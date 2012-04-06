<?php

namespace Grace\ORM;

interface MapperInterface {
    public function __construct($fullRecordClassName);
    public function convertDbRowToRecordArray(array $row);
    public function convertRecordToDbRow(MapperRecordInterface $record);
    public function getRecordChanges(MapperRecordInterface $record);
}
