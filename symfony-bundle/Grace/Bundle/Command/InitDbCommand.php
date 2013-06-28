<?php

namespace Grace\Bundle\Command;

use Doctrine\Tests\DBAL\Functional\TypeConversionTest;
use Grace\Bundle\GracePlusSymfony;
use Grace\DBAL\Exception\QueryException;
use Grace\ORM\Service\Config\Element\ModelElement;
use Grace\ORM\Service\TypeConverter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Grace\ORM\Service\Generator;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Symfony\Component\Yaml\Yaml;

class InitDbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('grace:init_db')
            ->setDescription('Initialize DB structure from Grace models file')
            ->addOption('create-db', 'c', InputOption::VALUE_NONE, 'Create database if need')
            ->addOption('force-drop', 'f', InputOption::VALUE_NONE, 'Use "DROP TABLE IF EXISTS"')
            ->addOption('insert-fakes', 'i', InputOption::VALUE_NONE, 'Insert sample data from config ("fakes")');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fakesFile = $this->getContainer()->getParameter('grace.model_config_fakes');

        /** @var $orm GracePlusSymfony */
        $orm = $this->getContainer()->get('grace_orm');
        $typeConverter = $orm->typeConverter;
        /** @var $db ConnectionInterface */
        $db = $this->getContainer()->get('grace_db');

        $createDb  = $input->getOption('create-db');
        $forceDrop = $input->getOption('force-drop');
        $dbName    = $this->getContainer()->getParameter('grace_db');
        $dbName    = $dbName['database'];
        $output->writeln("Database '{$dbName}'");

        if ($createDb) {
            $output->writeln("Creating database '{$dbName}'");
            $db->createDatabaseIfNotExist();
        }

        $config = $orm->config->models;

        foreach ($config as $modelName => $modelConfig) {
            $output->writeln($this->createTable($db, $typeConverter, $modelName, $modelConfig, $forceDrop));
        }
        $output->writeln("Tables have been created");

        if ($input->getOption('insert-fakes')) {
            $output->writeln("Inserting fakes");
            $fakes = $this->getFakes($fakesFile);
            if (!empty($fakes)) {
                foreach ($fakes as $tableName => $rows) {
                    $this->insertFakes($orm, $tableName, $rows);
                    $output->writeln(" > {$tableName}");
                }
                $output->writeln("ok");
            } else {
                $output->writeln("No fakes found!");
            }
        }

        $output->writeln("All tasks complete.");
    }

    private function createTable(ConnectionInterface $db, TypeConverter $typeConverter, $name, ModelElement $config, $forceDrop = false)
    {
        $fieldsSQL = $this->getFieldsSQL($db, $typeConverter, $config);

        if ($fieldsSQL == '') {
            return "Model {$name} is not sql";
        }

        $result = "Creating table {$name}... ";

        $isPresent = false;
        if ($forceDrop) {
            $db->execute("DROP TABLE IF EXISTS ?f CASCADE", array($name));
        } else {
            try {
                $db->execute("SELECT 1 FROM ?f", array($name));
                $isPresent = true;
            } catch(QueryException $e) {
            }
        }

        if ($isPresent) {
            $result .= 'exists!';
        } else {
            $db->execute("CREATE TABLE ?f (\n$fieldsSQL\n, PRIMARY KEY (\"id\"))", array($name));
            //ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            $result .= 'ok.';
        }

        return $result;
    }

    private function getFieldsSQL(ConnectionInterface $db, TypeConverter $typeConverter, ModelElement $config)
    {
        $sql = array();

        foreach ($config->properties as $propName => $propConfig) {
            if ($propConfig->mapping->localPropertyType) {
                $type = $typeConverter->getDbType($propConfig->mapping->localPropertyType);
                $null = 'NOT NULL';
            } elseif ($propConfig->mapping->foreignKeyTable) {
                //STOPPER внешний ключ и нул
                $type = 'ХУЙ';
                $null = 'ХУЙ';
            } else {
                //STOPPER нормалёк исключения ёпта
                throw new \Exception;
            }

            $sql[] = $db->replacePlaceholders("?f ?p $null", array($propName, $type));
        }

        return implode(",\n", $sql);
    }

    private function getFakes($fakesFile)
    {
        return Yaml::parse($fakesFile);
    }

    private function insertFakes(GracePlusSymfony $orm, $modelName, $fakeList)
    {
        foreach ($fakeList as $fake) {
            $orm->getFinder($modelName)->create($fake);
        }
        $orm->commit();
    }
}
