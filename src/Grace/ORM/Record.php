<?php

namespace Grace\ORM;

abstract class Record implements RecordInterface, MapperRecordInterface {
    private $eventDispatcher;
    private $unitOfWork;
    private $id;
    private $defaultFields = array();
    protected $fields = array();

    final public function __construct(EventDispatcher $eventDispatcher,
        UnitOfWork $unitOfWork, $id, array $fields, $isNew) {

        $this->eventDispatcher = $eventDispatcher;
        $this->unitOfWork = $unitOfWork;

        $this->id = $id;
        $this->defaultFields = $fields;
        $this->fields = $fields;

        if ($isNew) { //if it is a new object
            $this->unitOfWork->markAsNew($this);
        }
    }
    final public function asArray() {
        return $this->fields;
    }
    final public function getDefaultFields() {
        return $this->defaultFields;
    }
    final public function delete() {
        $this->unitOfWork->markAsDeleted($this);
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
        $this->unitOfWork->markAsChanged($this);
        return $this;
    }
    final public function getId() {
        return $this->id;
    }
    final protected function getEventDispatcher() {
        return $this->eventDispatcher;
    }
}