<?php

namespace Grace\ORM;

class IdentityMap {

    private $records = array();
    private $newRecordIds = array();
    private $changedRecordIds = array();
    private $deletedRecordIds = array();

    public function getRecord($class, $id) {
        if (!isset($this->records[$class][$id])) {
            return false;
        }
        return $this->records[$class][$id];
    }
    public function setRecord($class, $id, $object) {
        $this->records[$class][$id] = $object;
        return $this;
    }
    public function issetRecord($class, $id) {
        return isset($this->records[$class][$id]);
    }
    public function unsetRecord($class, $id) {
        unset($this->records[$class][$id]);
    }
    public function markRecordAsNew($class, $id) {
        $this->newRecord[$class][$id] = true;
        return $this;
    }
    public function markRecordAsChanged($class, $id) {
        $this->changedRecordIds[$class][$id] = true;
        return $this;
    }
    public function markRecordAsDeleted($class, $id) {
        $this->deletedRecordIds[$class][$id] = true;
        return $this;
    }
    public function commit() {
        foreach ($this->newRecordIds as $class => $classRecords) {
            foreach ($classRecords as $id => $value) {
                
            }
        }
        foreach ($this->changedRecordIds as $class => $classRecords) {
            foreach ($classRecords as $id => $value) {
                
            }
        }
        foreach ($this->deletedRecordIds as $class => $classRecords) {
            foreach ($classRecords as $id => $value) {
                
            }
        }
    }

}
