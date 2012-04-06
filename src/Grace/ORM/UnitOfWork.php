<?php

namespace Grace\ORM;

class UnitOfWork {

    private $newRecords = array();
    private $changedRecords = array();
    private $deletedRecords = array();

    public function markAsNew($record) {
        $this->newRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsChanged($record) {
        $this->changedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsDeleted($record) {
        $this->deletedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    /*
     * @return ManagerRecordInterface[]
     */
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
