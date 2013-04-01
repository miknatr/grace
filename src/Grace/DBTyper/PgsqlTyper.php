<?php

namespace Grace\DBTyper;

class PgsqlTyper extends AbstractTyper {
    public function getDbType($ormType)
    {
        list($type, $paramsString) = $this->parseType($ormType);

        switch ($type) {
            case 'bool':
                return 'boolean';
            case 'int':
                return 'integer';
            case 'bigint':
                return 'bigint';
            case 'float':
                return 'real';
            case 'double':
                return 'double precision';
            case 'numeric':
                $params = explode(',', $paramsString);
                if (count($params) != 2 or intval($params[0]) <= 0 or intval($params[1]) <= 0) {
                    throw new \InvalidArgumentException('Unsupported numeric parameters ' . $paramsString);
                }
                $numbers = intval($params[0]);
                $afterComma = intval($params[1]);
                return "numeric($numbers, $afterComma)";
            case 'string':
                $len = intval($paramsString);
                if ($len == 0) {
                    $len = 255;
                }
                if ($len > 255) {
                    throw new \InvalidArgumentException('Unsupported varchar length ' . $paramsString);
                }
                return "varchar($len)";
            case 'text':
                return 'text';
            case 'timestamp':
                return 'timestamp';
            case 'point':
                return 'point';
            case 'enum':
                $values = explode(',', $paramsString);
                array_walk($values, function($value) { return trim($value, "\"'"); });
                $values = implode("','", $values);
                return "enum('$values')";
        }

        throw new \InvalidArgumentException('Unsupported orm type ' . $ormType);
    }
}