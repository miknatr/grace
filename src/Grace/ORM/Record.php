<?php

namespace Grace\ORM;

abstract class Record implements RecordInterface, MapperRecordInterface {
    private $eventDispatcher;
    private $unitOfWork;
    private $id;
    private $defaultFields = array();
    protected $fields = array();

    final public function __construct(EventDispatcher $eventDispatcher,
        UnitOfWork $unitOfWork, $id, array $fields, $isNew = false) {

        $this->eventDispatcher = $eventDispatcher;
        $this->unitOfWork = $unitOfWork;

        $this->id = $id;
        $this->defaultFields = $fields;
        $this->fields = $fields;

        if ($isNew) { //if it is a new object
            $this->getUnitOfWork()->markAsNew($this);
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
        return $this->id;
    }
    final protected function getEventDispatcher() {
        return $this->eventDispatcher;
    }
    final protected function getUnitOfWork() {
        return $this->unitOfWork;
    }
}