<?php

namespace Grace\ORM;

class Mapper implements MapperInterface {
    protected $fields = array();
    public function __construct($fullRecordClassName) {
        $rClass = new \ReflectionClass($fullRecordClassName);
        $properties = $rClass->getProperties($filter);
        foreach ($properties as $property) {
            //TODO magic string
            if (substr($property, 0, 5) == 'field') {
                $this->fields[] = lcfirst(substr($property, 5));
            }
        }
    }
    public function convertDbRowToRecordArray(array $row) {
        $recordArray = array();
        foreach ($row as $k => $v) {
            if (in_array($k, $this->fields)) {
                //TODO magic string
                $recordArray['field' . ucfirst($k)] = $v;
            }            
        }
        return $recordArray;
    }
    public function convertRecordToDbRow(MapperRecordInterface $record) {
        $row = array();
        $recordArray = $record->asArray();
        foreach ($recordArray as $k => $v) {
            $kWithoutField = lcfirst(substr($property, 5));
            if (in_array($kWithoutField, $this->fields)) {
                //TODO magic string
                $recordArray[$kWithoutField] = $v;
            }        
        }
        return $row;
    }
    public function getRecordChanges(MapperRecordInterface $record) {
        $changes = array();
        $defaultFields = $record->getDefaultFields();
        foreach ($record->asArray() as $k => $v) {
            if ($defaultFields[$k] != $v) {
                //$k has a prefix 'field'
                $changes[ucfirst(substr($k, 5))] = $v;
            }
        }
        return $changes;
    }
}