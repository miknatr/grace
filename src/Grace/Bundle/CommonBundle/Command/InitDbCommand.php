<?php

namespace Grace\Bundle\CommonBundle\Command;

use Grace\ORM\FinderSql;
use Grace\ORM\ManagerAbstract;
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
    const DB_NAME_FROM_ENVIRONMENT = 'db_name_from_enviroment';

    protected function configure()
    {
        $this
            ->setName('grace:init_db')
            ->setDescription('Initialize DB structure from Grace models file')
            ->addOption('create-db', 'c', InputOption::VALUE_NONE, 'Create database if need')
            ->addOption('force-drop', 'f', InputOption::VALUE_NONE, 'Use "DROP TABLE IF EXISTS"')
            ->addOption('insert-fakes', 'i', InputOption::VALUE_NONE, 'Insert sample data from config ("fakes")')
            ->addArgument('db', InputArgument::OPTIONAL, 'DB name', self::DB_NAME_FROM_ENVIRONMENT)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $orm ManagerAbstract */
        $orm = $this->getContainer()->get('grace_orm');
        /** @var $db InterfaceConnection */
        $db = $this->getContainer()->get('grace_db');


        $createDb = $input->getOption('create-db');
        $forceDrop = $input->getOption('force-drop');
        $dbName = $input->getArgument('db') == self::DB_NAME_FROM_ENVIRONMENT ? $this->getContainer()->getParameter('database_name') : $input->getArgument('db');

        if ($createDb) {
            $this->createDb($db, $output, $dbName);
        }


        $configFull = $this->getContainer()->get('grace_generator')->getConfig();

        if (!isset($configFull['models'])) {
            throw new \LogicException('Models config must contain "models" section');
        }

        $config = $configFull['models'];

        $output->writeln("Using database '{$dbName}'.\n");
        $db->execute("USE `?e`", array($dbName));


        foreach ($config as $modelName => $modelContent) {
            $result = $this->createTable($db, $modelName, $modelContent, $forceDrop);
            /** @var $finderClass FinderSql */
            $finderClass = $orm->getClassNameProvider()->getFinderClass($modelName);
            $output->writeln($result);
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

    private function createDb(InterfaceConnection $db, OutputInterface $output, $database)
    {
        //TODO mysql-specific code in orm
        $mysqli = new \mysqli(
            $this->getContainer()->getParameter('database_host'),
            $this->getContainer()->getParameter('database_user'),
            $this->getContainer()->getParameter('database_password'),
            null,
            $this->getContainer()->getParameter('database_port')
        );

        if ($mysqli->connect_error) {
            if ($output) {
                $output->write('Mysql connection error (' . $mysqli->connect_errno . '): ' . $mysqli->connect_error);
            }
            exit(1);
        }

        $result = $mysqli->query("CREATE DATABASE IF NOT EXISTS `$database`");

        if (!$result) {
            if ($output) {
                $output->write('Mysql query error (' . $mysqli->errno . '): ' . $mysqli->error);
            }
            exit(1);
        }

        $mysqli->close();
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
