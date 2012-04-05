<?php

namespace Grace\ORM;

class UnitOfWork {

    private $newRecords = array();
    private $changedRecords = array();
    private $deletedRecords = array();

    public function markAsNew(Record $record) {
        $this->newRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsChanged(Record $record) {
        $this->changedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsDeleted(Record $record) {
        $this->deletedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function getNewRecords() {
        return $this->newRecords;
    }
    public function getChandedRecords() {
        return $this->changedRecords;
    }
    public function getDeletedRecords() {
        return $this->deletedRecords;
    }
}
