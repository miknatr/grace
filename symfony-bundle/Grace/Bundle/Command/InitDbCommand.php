<?php

namespace Grace\Bundle\Command;

use Doctrine\Tests\DBAL\Functional\TypeConversionTest;
use Grace\DBAL\Exception\QueryException;
use Grace\ORM\FinderSql;
use Grace\ORM\Grace;
use Grace\ORM\Service\TypeConverter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Grace\ORM\Service\Generator;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class InitDbCommand extends ContainerAwareCommand
{
    const DBTYPE_FIELD = 'mapping';

    protected function configure()
    {
        $this
            ->setName('grace:init_db')
            ->setDescription('Initialize DB structure from Grace models file')
            ->addOption('create-db', 'c', InputOption::VALUE_NONE, 'Create database if need')
            ->addOption('force-drop', 'f', InputOption::VALUE_NONE, 'Use "DROP TABLE IF EXISTS"')
            ->addOption('insert-fakes', 'i', InputOption::VALUE_NONE, 'Insert sample data from config ("fakes")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $orm Grace */
        $orm = $this->getContainer()->get('grace_orm');
        $typeConverter = $orm->getTypeConverter();
        /** @var $db \Grace\DBAL\ConnectionAbstract\ConnectionInterface */
        $db = $this->getContainer()->get('grace_db');


        $createDb = $input->getOption('create-db');
        $forceDrop = $input->getOption('force-drop');
        $dbName = $this->getContainer()->getParameter('grace_db');
        $dbName = $dbName['database'];
        $output->writeln("Database '{$dbName}'");

        if ($createDb) {
            $output->writeln("Creating database '{$dbName}'");
            $db->createDatabaseIfNotExist();
        }


        $configFull = $this->getContainer()->get('grace_generator')->getConfig();

        if (!isset($configFull['models'])) {
            throw new \LogicException('Models config must contain "models" section');
        }

        $config = $configFull['models'];



        foreach ($config as $modelName => $modelContent) {
            $result = $this->createTable($db, $typeConverter, $modelName, $modelContent, $forceDrop);
            /** @var $finderClass FinderSql */
            $finderClass = $orm->getClassNameProvider()->getFinderClass($modelName);
            $output->writeln($result);
        }
        $output->writeln("Tables have been created");


        if($input->getOption('insert-fakes')) {
            $output->writeln("Inserting fakes");
            if(!empty($configFull['fakes'])) {
                foreach($configFull['fakes'] as $tableName => $fakeList) {
                    $this->insertFakes($db, $tableName, $fakeList);
                    $output->writeln(" > {$tableName}");
                }
                $output->writeln("ok");
            } else {
                $output->writeln("No fakes found!");
            }
        }


        $output->writeln("All tasks complete.");
    }

    private function getFieldsSQL(ConnectionInterface $db, TypeConverter $typeConverter, $structure)
    {
        $fields = array();

        foreach ($structure['properties'] as $propName => $propOptions) {
            if (!empty($propOptions[self::DBTYPE_FIELD])) {
                $fields[$propName]['type'] = $typeConverter->getDbType($propOptions[self::DBTYPE_FIELD]);
            }
        }
        $sql = array();
        foreach($fields as $fieldName => $fieldProps) {
            $sql[] = $db->replacePlaceholders("?f ?p NULL", array($fieldName, $fieldProps['type']));
        }

        return implode(",\n", $sql);
    }

    private function createTable(ConnectionInterface $db, TypeConverter $typeConverter, $name, $structure, $forceDrop = false)
    {
        $fieldsSQL = $this->getFieldsSQL($db, $typeConverter, $structure);

        if ($fieldsSQL == '') {
            return "Model {$name} is not sql";
        }

        $result = "Creating table {$name}... ";

        $is_present = false;
        if ($forceDrop) {
            $db->execute("DROP TABLE IF EXISTS ?f CASCADE", array($name));
        } else {
            try {
                $db->execute("SELECT 1 FROM ?f", array($name));
                $is_present = true;
            } catch (QueryException $e) {}
        }

        if ($is_present) {
            $result .= "exists!";
        } else {
            $db->execute("CREATE TABLE ?f (\n$fieldsSQL\n, PRIMARY KEY (\"id\"))", array($name));
            //ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            $result .= "ok.";
        }

        return $result;
    }

    private function insertFakes(ConnectionInterface $db, $tableName, $fakeList)
    {
        foreach($fakeList as $fake) {
            $db->getSQLBuilder()->insert($tableName)->values($fake)->execute();
        }
    }
}