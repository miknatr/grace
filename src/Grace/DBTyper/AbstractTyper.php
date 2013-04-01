<?php

namespace Grace\DBTyper;

abstract class AbstractTyper {
    abstract public function getDbType($ormType);
    protected function parseType($typeString)
    {
        $matches = array();
        preg_match('/^(?P<type>([a-z])*)(\((?P<params>.*)\)){0,1}$/', $typeString, $matches);

        if (!isset($matches['type'])) {
            throw new \InvalidArgumentException('Invalid type definition ' . $typeString);
        }

        return array($matches['type'], isset($matches['params']) ? $matches['params'] : '');
    }
}