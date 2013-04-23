<?php

namespace Grace\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Grace\ORM\Service\Generator;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;

class CheckDbCommand extends ContainerAwareCommand
{
    const DBTYPE_FIELD = 'mapping';
    protected function configure()
    {
        $this
            ->setName('grace:check_db')
            ->setDescription('Compare DB structure with models defined in file')
            ->addOption('db', 'd', InputOption::VALUE_OPTIONAL, 'DB name', 'intertos_test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $db \Grace\DBAL\ConnectionAbstract\ConnectionInterface */
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

        $dbName = $input->getOption('db');
        $output->writeln("Checking database '{$dbName}'...");
        $db->execute("USE `?e`", array($dbName));

        $tablesExpected = array();
        $diff           = array();

        foreach ($config as $modelName => $modelContent) {
            foreach ($modelContent['properties'] as $propName => $propOptions) {
                if (!empty($propOptions[self::DBTYPE_FIELD]) && $propOptions[self::DBTYPE_FIELD] !== true) {
                    $tablesExpected[$modelName]['fields'][$propName] =
                        $this->getFieldAttributes($propOptions[self::DBTYPE_FIELD]);
                }
            }

            if (!empty($modelContent['indexes'])) {
                $tablesExpected[$modelName]['indexes'] = $modelContent['indexes'];
            }
        }

        foreach ($tablesExpected as $tableName => $tableInfo) {
            if (!empty($tablesExpected[$tableName]['fields'])) {
                $fieldsExpected  = !empty($tableInfo['fields']) ? $tableInfo['fields'] : array();
                $indexesExpected = !empty($tableInfo['indexes']) ? $tableInfo['indexes'] : array();
                sort($indexesExpected);

                $schemeDef  = $db
                    ->execute("SHOW CREATE TABLE `?e`", array($tableName))
                    ->fetchOneOrFalse();
                $schemeText = $schemeDef['Create Table'];

                $fieldsExisting  = array_fill_keys(array_keys($fieldsExpected), array());
                $indexesExisting = array();

                $elements = array();
                preg_match_all('
                ~
                    ^
                        (
                            (\s* (PRIMARY|UNIQUE|) \s* KEY \s+ (.*?))(,?) #index definition
                            |
                            \s* `(.+)` \s+ (.*?) (,?) #field definition
                        )
                    $
                ~mx', $schemeText, $elements, PREG_SET_ORDER);

                foreach ($elements as $elem) {
                    if (count($elem) == 6) { //index definition
                        $indexesExisting[] = trim($elem[2]);
                    } else {
                        $fieldsExisting[$elem[6]] = $this->getFieldAttributes($elem[7]);
                    }
                }

                sort($indexesExisting);

                if ($fieldsExpected != $fieldsExisting) {
                    foreach ($fieldsExpected as $fieldName => $fieldAttrsExp) {
                        if ($fieldAttrsExp != $fieldsExisting[$fieldName]) {
                            $diff[$tableName]['fields'][$fieldName] = array(
                                'config' => $fieldAttrsExp, 'db' => $fieldsExisting[$fieldName],
                            );
                        }
                    }
                }

                if ($indexesExpected != $indexesExisting) {
                    $diff[$tableName]['indexes'] = array(
                        'config' => $indexesExpected, 'db' => $indexesExisting,
                    );
                }
            }
        }

        if (empty($diff)) {
            $output->writeln("No changes from config.");
        } else {
            foreach ($diff as $tableName => $tableDiffs) {
                $output->writeln("\nTable `{$tableName}` :");

                if (!empty($tableDiffs['fields'])) {
                    foreach ($tableDiffs['fields'] as $fieldName => $fieldDiff) {
                        $output->writeln("   --- `{$fieldName}`");
                        $output->writeln("      config-> " . implode(' ', $fieldDiff['config']));
                        $output->writeln("          db-> " . implode(' ', $fieldDiff['db']));
                    }
                }
                if (!empty($tableDiffs['indexes'])) {
                    $output->writeln("   === different indexes:");
                    $output->writeln("      config:");
                    foreach ($tableDiffs['indexes']['config'] as $index) {
                        $output->writeln("              ~ {$index}");
                    }
                    $output->writeln("          db:");
                    foreach ($tableDiffs['indexes']['db'] as $index) {
                        $output->writeln("              ~ {$index}");
                    }
                }
            }
            $output->writeln("\nYou can use 'app/console grace:init_db [table] -f' to generate table from config.");
        }
    }

    private function getFieldAttributes($dbtype)
    {
        $dbtype = trim(preg_replace('/NOT NULL|COLLATE (\S+)/', '', $dbtype));
        $attrs  = array_filter(explode(' ', $dbtype));
        sort($attrs);
        return $attrs;
    }
}
