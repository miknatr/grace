<?php

namespace Grace\ORM;

class IdentityMap {

    private $records = array();

    public function getRecord($class, $id) {
        if (!isset($this->records[$class][$id])) {
            return false;
        }
        return $this->records[$class][$id];
    }
    public function setRecord($class, $id, $record) {
        $this->records[$class][$id] = $record;
        return $this;
    }
    public function issetRecord($class, $id) {
        return isset($this->records[$class][$id]);
    }
    public function unsetRecord($class, $id) {
        unset($this->records[$class][$id]);
        return $this;
    }
}