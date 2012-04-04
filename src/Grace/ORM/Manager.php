<?php

namespace Grace\ORM;

class Manager implements ManagerInterface {

    private $dbConnections = array();
    private $eventDispatcher;
    private $identityMap;
    private $modelsNamespace;
    private $mappers = array();
    private $finders = array();

    public function __construct($modelsNamespace, array $dbConnections,
        EventDispatcher $eventDispatcher, IdentityMap $identityMap) {

        $this->dbConnections = $dbConnections;
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = $identityMap;
        $this->modelsNamespace = $modelsNamespace;
    }
    public function getFinder($className) {
        if (!isset($this->finders[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Finder';
            if (class_exists($fullClassName)) {
                $this->finders[$className] = new $fullClassName;                 
            } else {
                $this->finders[$className] = new Finder();
            }
        }
        return $this->finders[$className];
    }
    private function getMapper($className) {
        if (!isset($this->mappers[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Mapper';
            if (class_exists($fullClassName)) {
                $this->mappers[$className] = new $fullClassName;                 
            } else {
                $this->mappers[$className] = new Mapper;
            }
        }
        return $this->mappers[$className];
    }
    public function commit() {
        
    }
}