<?php

namespace Grace\ORM;

abstract class Record implements RecordInterface, MapperRecordInterface {

    private $eventDispatcher;
    private $unitOfWork;
    private $defaultFields = array();
    private $id;

    final public function __construct(EventDispatcher $eventDispatcher,
        UnitOfWork $unitOfWork, array $fields = array()) {
        
        $this->defaultFields = $fields;

        $this->eventDispatcher = $eventDispatcher;
        $this->unitOfWork = $unitOfWork;

        if (count($fields) == 0) { //if it is a new object
            $this->getUnitOfWork()->markAsNew($this);
        } else { //if it is a exists object
            if (isset($fields['id'])) {
                $this->id = $fields['id'];
            }
            //mapper gets prepared properties which must start with 'field'
            foreach ($fields as $k => $v) {
                if (property_exists($this, $k)) {// and substr($k, 0, 5) == 'field') {
                    $this->$k = $v;
                }
            }
        }
    }
    final public function asArray() {
        return get_object_vars($this);
    }
    final public function getDefaultFields() {
        return $this->defaultFields;
    }
    final public function delete() {
        $this->getUnitOfWork()->markAsDeleted($this);
        return $this;
    }
    final public function edit(array $fields) {
        foreach ($fields as $k => $v) {
            $method = 'set' . ucfirst($k);
            if (method_exists($this, $method)) {
                $this->$method($v);
            }
        }
        return $this;
    }
    final public function save() {
        $this->getUnitOfWork()->markAsChanged($this);
        return $this;
    }
    final public function getId() {
        if ($this->id == null) {
            $this->id = $this->generateNewId();
        }
        return $this->id;
    }
    protected function generateNewId() {
        //return substr(md5(microtime()), rand(0, 32-8), 8);
    }
    final protected function getEventDispatcher() {
        return $this->eventDispatcher;
    }
    final protected function getUnitOfWork() {
        return $this->unitOfWork;
    }
}