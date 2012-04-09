<?php

namespace Grace\ORM;

interface MapperInterface {
    public function convertDbRowToRecordArray(array $row);
    public function convertRecordArrayToDbRow(array $recordArray);
    public function getRecordChanges(array $recordArray, array $defaults);
}
