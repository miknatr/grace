<?php

namespace Grace\Bundle\CommonBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Grace\Generator\ModelsGenerator;

use Grace\DBAL\InterfaceConnection;

class InitDbCommand extends ContainerAwareCommand
{
    const DBTYPE_FIELD = 'mapping';

    protected function configure()
    {
        $this
            ->setName('grace:init_db')
            ->setDescription('Initialize DB structure from Grace models file')
            ->addArgument('table', InputArgument::OPTIONAL, 'Table name (create only one table)')
            ->addOption('db', 'd', InputOption::VALUE_OPTIONAL, 'DB name', 'intertos_test')
            ->addOption('force-drop', 'f', InputOption::VALUE_NONE, 'Use "DROP TABLE IF EXISTS"')
            ->addOption('insert-fakes', 'i', InputOption::VALUE_NONE, 'Insert sample data from config ("fakes")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $db InterfaceConnection */
        $db = $this
            ->getContainer()
            ->get('grace_db');

        $configFull = $this
            ->getContainer()
            ->get('grace_generator')
            ->getConfig();

        if (!isset($configFull['models'])) {
            throw new \LogicException('Models config must contain "models" section');
        }

        $config = $configFull['models'];
        $finders = $configFull['extra-finders'];

        $dbName = $input->getOption('db');
        $output->writeln("Using database '{$dbName}'.\n");
        $db->execute("USE `?e`", array($dbName));

        $singleTable = $input->getArgument('table');
        if($singleTable) {
            if(empty($config[$singleTable])) {
                $output->writeln("Table '{$singleTable}' definition not found in config.yml!");
            } else {
                $result = $this->createTable($db, $singleTable, $config[$singleTable], $input->getOption('force-drop'));
                $output->writeln($result);
            }
        } else {
            foreach ($config as $modelName => $modelContent) {
                $result = $this->createTable($db, $modelName, $modelContent, $input->getOption('force-drop'));
                $output->writeln($result);
            }
            foreach ($finders as $finder => $finderConfig) {
                if (isset($finderConfig['table'])) {
                    $result = $this->createTable($db, $finderConfig['table'], $config[$finderConfig['class']], $input->getOption('force-drop'));
                    $output->writeln($result);
                }
            }
        }

        $output->writeln("\nTables have been created");

        if($input->getOption('insert-fakes')) {
            $output->writeln("\nInserting fakes...");
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

        $output->writeln("\nAll tasks complete.");
    }

    private function getFieldsSQL(InterfaceConnection $db, array $fields, $indexes = array())
    {
        $sql = array();
        foreach($fields as $fieldName => $fieldProps) {
            $sql[] = $db->replacePlaceholders("`?e` ?p NOT NULL", array($fieldName, $fieldProps));
        }

        if(!empty($indexes)) {
            $indexes = array_unique($indexes);
            $sql[] = implode(",\n", $indexes);
        }

        return implode(",\n", $sql);
    }

    private function createTable(InterfaceConnection $db, $name, $structure, $forceDrop = false)
    {
        $result = "No fields given for table {$name}";
        $fields = array();
        $indexes = !empty($structure['indexes']) ? $structure['indexes'] : array();

        if(!empty($structure['properties'])) {
            foreach ($structure['properties'] as $propName => $propOptions) {
                //здесь надо вводить тип коннекта, и на осн. типа решать создавать или нет
                if (!empty($propOptions[self::DBTYPE_FIELD]) && $propOptions[self::DBTYPE_FIELD] !== true) {
                    if(is_array($propOptions[self::DBTYPE_FIELD])) {
                        $fields[$propName] = end($propOptions[self::DBTYPE_FIELD]);
                    } else {
                        $fields[$propName] = $propOptions[self::DBTYPE_FIELD];
                    }
                }
            }
        }

        if (!empty($fields)) {
            $result = "Creating table {$name}... ";

            $is_present = false;
            if($forceDrop) {
                $db->execute("DROP TABLE IF EXISTS `?e`", array($name));
            } else {
                try {
                    $db->execute("SELECT 1 FROM `?e`", array($name));
                    $is_present = true;
                } catch(\Grace\DBAL\ExceptionQuery $e) {}
            }

            if($is_present) {
                $result .= "exists!";
            } else {
                $fieldsSQL = $this->getFieldsSQL($db, $fields, $indexes);
                $db->execute("CREATE TABLE `?e` (\n?p\n) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci", array($name, $fieldsSQL));
                $result .= "ok.";
            }
        }

        return $result;
    }

    private function insertFakes(InterfaceConnection $db, $tableName, $fakeList)
    {
        foreach($fakeList as $fake) {
            $sets = array();
            foreach($fake as $col => $value) {
                $sets[] = $db->replacePlaceholders("`?e` = ?q", array($col, $value));
            }
            $db->execute("INSERT INTO {$tableName} SET ?p", array(implode(',', $sets)));
        }
    }
}