<?php

namespace Grace\ORM;

abstract class Mapper implements MapperInterface
{
    protected $fields = array();

    public function convertDbRowToRecordArray(array $row)
    {
        $recordArray = array();
        foreach ($this->fields as $field) {
            if (isset($row[$field])) {
                $recordArray[$field] = $row[$field];
            } else {
                $recordArray[$field] = null;
            }
        }
        return $recordArray;
    }
    public function convertRecordArrayToDbRow(array $recordArray)
    {
        $row = array();
        foreach ($this->fields as $field) {
            if (isset($recordArray[$field])) {
                $row[$field] = $recordArray[$field];
            } else {
                $row[$field] = null;
            }
        }
        return $row;
    }
    public function getRecordChanges(array $recordArray, array $defaults)
    {
        $changes = array();
        foreach ($this->fields as $field) {
            if (isset($recordArray[$field])
                and isset($defaults[$field])
                    and $recordArray[$field] != $defaults[$field]
            ) {

                $changes[$field] = $recordArray[$field];
            }
        }
        return $changes;
    }
}