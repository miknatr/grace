<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;

class Manager implements ManagerInterface {

    private $dbReadConnection;
    private $dbWriteConnection;
    //TODO memcache, redis etc
    private $cacheConnection;
    private $eventDispatcher;
    private $identityMap;
    private $unitOfWork;
    private $modelsNamespace;
    private $mappers = array();
    private $finders = array();

    public function __construct($modelsNamespace,
        EventDispatcher $eventDispatcher, IdentityMap $identityMap,
        UnitOfWork $unitOfWork, InterfaceConnection $dbReadConnection,
        InterfaceConnection $dbWriteConnection) {

        $this->dbReadConnection = $dbReadConnection;
        $this->dbWriteConnection = $dbWriteConnection;
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = $identityMap;
        $this->unitOfWork = $unitOfWork;
        $this->modelsNamespace = $modelsNamespace;
    }
    public function getFinder($className) {
        if (!isset($this->finders[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Finder';
            $fullCollectionClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Collection';
            if (!class_exists($fullClassName)) {
                $fullClassName = 'Finder';
            }
            $this->finders[$className] = new $fullClassName($this->identityMap,
                    $this->dbReadConnection, $this->getMapper($className),
                    $className, $fullCollectionClassName);
        }
        return $this->finders[$className];
    }
    private function getMapper($className) {
        if (!isset($this->mappers[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Mapper';
            if (!class_exists($fullClassName)) {
                $fullClassName = 'Mapper';
            }
            $this->mappers[$className] = new $fullClassName;
        }
        return $this->mappers[$className];
    }
    public function commit() {
        //TODO
        foreach ($this->unitOfWork->getNewRecords() as $record) {
            
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            
        }
    }
}