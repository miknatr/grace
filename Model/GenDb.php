<?php

class Orm_GenDb
{
    protected $_db = null;

    public function __construct()
    {
        $this->_db = db();
    }

    public function deleteAndCreateDatabase($database)
    {
        $this->_db->execute('CREATE DATABASE IF NOT EXISTS `' . $database . '`'
                          . 'DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $tables = $this->_db->execute('SHOW TABLES')->fetchColumn();
        foreach ($tables as $table) {
            $this->_db->execute('DROP TABLE `' . $table . '`');
        }
    }
    public function genSqlFromCsv($csv)
    {
        foreach ($csv as $table => $files) {
            foreach ($files as $file) {
                $handle = fopen($file, "r");
                while (!feof($handle)) {
                    $string = fgets($handle, 4096);
                    $string = trim($string, " \t\r\n");
                    if (trim($string) != '') {
                        $this->_db->execute("INSERT INTO `$table` VALUES($string)");
                    }
                }
                fclose($handle);
            }
        }
    }
    public function genSqlFromYaml($yaml)
    {
        $schema = $yaml;

        foreach ($schema['schema'] as $tableName => $tableParams) {
            if (isset($tableParams['_extends']))
                $tableParams = $schema['schema'][$tableParams['_extends']];

            if (!isset($tableParams['_id'])) {
                $tableParams['_id'] = 'id';
            }

            $fieldsStrings = array();
            foreach ($tableParams as $fieldName => $fieldParams) {
                if (substr($fieldName, 0, 1) != '_') {
                    $fieldsStrings[] = '`' . $fieldName . '` '
                                     . $this->_convertTypeYamlToSql($fieldParams['type'], $fieldParams) . ' '
                                     . 'NOT NULL' . ' '
                                     . ((isset($tableParams['_id']) and $tableParams['_id'] == $fieldName)
                                            ? 'AUTO_INCREMENT' : '');
                }
            }

            if (isset($tableParams['_id'])) {
                $fieldsStrings[] = 'PRIMARY KEY (' . $tableParams['_id'] . ')';
            }

            $this->_db->execute("DROP TABLE IF EXISTS `" . $tableName . "`");
            $this->_db->execute($this->_genCreateSql($tableName, $fieldsStrings));
        }

        if(isset($schema['data'])) {
	        foreach ($schema['data'] as $tableName => $rows) {
	            foreach ($rows as $row) {
	                if (is_array($row)) {
	                    $fields = array();
	                    $values = array();
	                    foreach ($row as $field => $value) {
	                        $fields[] = $field;
	                        $values[] = "'" . addslashes($value) . "'";
	                    }
	                    $this->_db->execute('INSERT INTO `' . $tableName . '`'
	                               . '(' . implode(',', $fields) . ')'
	                               . 'VALUES (' . implode(',', $values) . ')');
	                } else {
	                    $this->_db->execute('INSERT INTO `' . $tableName . '` VALUES (' . $row . ')');
	                }
	            }
	        }
        }
    }

    private function _convertTypeYamlToSql($type, $params)
    {
        //http://dev.mysql.com/doc/refman/5.0/en/numeric-types.html
        if (stripos($type, 'bool') === 0) {
            return 'tinyint(1) unsigned';
        } elseif (stripos($type, 'uint') === 0) {
            $len = substr($type, 4);
            if ($len == '') {
                return 'int(10) unsigned';
            } elseif ($len <= 2) {
                return 'tinyint(' . $len . ') unsigned';
            } elseif ($len <= 4) {
                return 'smallint(' . $len . ') unsigned';
            } elseif ($len <= 6) {
                return 'mediumint(' . $len . ') unsigned';
            } elseif ($len <= 9) {
                return 'int(' . $len . ') unsigned';
            } elseif ($len <= 19) {
                return 'bigint(' . $len . ') unsigned';
            } else {
                return 'bigint(' . $len . ') unsigned';
            }
        } elseif (stripos($type, 'int') === 0) {
            $len = substr($type, 3);
            if ($len == '') {
                return 'int(10)';
            } elseif ($len <= 2) {
                return 'tinyint(' . $len . ')';
            } elseif ($len <= 4) {
                return 'smallint(' . $len . ')';
            } elseif ($len <= 6) {
                return 'mediumint(' . $len . ')';
            } elseif ($len <= 9) {
                return 'int(' . $len . ')';
            } elseif ($len <= 18) {
                return 'bigint(' . $len . ')';
            } else {
                return 'bigint(' . $len . ')';
            }
        } elseif (stripos($type, 'decimal') === 0) {
            $len = substr($type, 7);
            return 'decimal(' . str_replace('.', ',', $len) . ')';
        } elseif (stripos($type, 'char') === 0) {
            $len = substr($type, 4);
            if ($len == '') {
                $len = '255';
            }
            return 'char(' . $len . ')';
        } elseif (stripos($type, 'varchar') === 0) {
            $len = substr($type, 7);
            if ($len == '') {
                $len = '255';
            }
            return 'varchar(' . $len . ')';
        } elseif (stripos($type, 'float') === 0) {
            return 'float';
        } elseif (stripos($type, 'text') === 0) {
            return 'text';
        } elseif (stripos($type, 'datetime') === 0) {
            return 'datetime';
        } elseif (stripos($type, 'date') === 0) {
            return 'date';
        } elseif (stripos($type, 'enum') === 0) {
            return $type;
        } elseif (stripos($type, 'set') === 0) {
            return $type;
        } else {
            trigger_error('Unknown yaml type: ' . $type . ' ' . print_r($params, true));
        }
    }

    private function _genCreateSql($table, $fieldsStrings)
    {
        return "CREATE TABLE `$table` (\n"
             . implode(",\n", $fieldsStrings) . "\n"
             . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
    }
}
