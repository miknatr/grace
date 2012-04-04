<?php

namespace Grace\ORM;

abstract class Record implements RecordInterface, MapperRecordInterface {

    private $eventDispatcher;
    private $identityMap;
    private $id;

    final public function __construct(EventDispatcher $eventDispatcher,
        IdentityMap $identityMap, array $fields = array()) {

        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = $identityMap;

        if (count($fields) == 0) { //if it is a new object
            $this->getIdentityMap()->markRecordAsNew(get_class($this),
                $this->getId());
        } else { //if it is a exists object            
            //mapper properties mast start with 'field'
            if (isset($fields['id'])) {
                $this->id = $fields['id'];
            }
            foreach ($fields as $k => $v) {
                if (property_exists($this, $k) and substr($k, 0, 5) == 'field') {
                    $this->$k = $v;
                }
            }
        }
    }
    final public function asArray() {
        return get_object_vars($this);
    }
    final public function delete() {
        $this->getIdentityMap()->markRecordAsDeleted(get_class($this),
            $this->getId());
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
    final protected function getIdentityMap() {
        return $this->identityMap;
    }
}