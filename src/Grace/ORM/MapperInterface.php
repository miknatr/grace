<?php

namespace Grace\ORM;

interface MapperInterface {
    public function convertDbRowToRecord(array $row);
    public function convertRecordToDbRow(MapperRecordInterface $record);
}
