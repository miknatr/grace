<?php

namespace Grace\SQLBuilder;

class InsertBuilder extends AbstractBuilder {
    private $fieldsSql = '';
    private $valuesSql = '';
    private $fieldValues = array();
    
    public function values(array $values) {
        $this->fieldsSql = '`' . implode('`, `', array_keys($values)) . '`';
        $this->valuesSql = substr(str_repeat('?q, ', count($values)), 0, -2);
        $this->fieldValues = array_values($values);
        return $this;
    }
    protected function getQueryString() {
        if (count($this->fieldValues) == 0) {
            throw new ExceptionCallOrder('Set values for insert before execute');
        }
        return 'INSERT INTO `' . $this->from . '` (' . $this->fieldsSql . ')'
            . ' VALUES (' . $this->valuesSql . ')';
    }
    protected function getQueryArguments() {
        return $this->fieldValues;
    }
}