<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;

abstract class Manager implements ManagerInterface {

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
    protected function getFinder($className) {
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
    /**
     *
     * @param string $className
     * @return MapperInterface
     */
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
        foreach ($this->unitOfWork->getNewRecords()  as $record) {
            $className = get_class($record);
            $changes = $this->getMapper($className)
                ->convertRecordArrayToDbRow($record->asArray());
            $this->dbWriteConnection->getSQLBuilder()
                ->insert($className)
                ->values($changes)
                ->execute();
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            $className = get_class($record);
            $changes = $this->getMapper($className)
                ->getRecordChanges($record->asArray(), $record->getDefaultFields());
            if (count($changes) > 0) {
                $this->dbWriteConnection->getSQLBuilder()
                    ->update($className)
                    ->values($changes)
                    //TODO 'id' - magic string
                    ->eq('id', $record->getId())
                    ->execute();
            }
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            $className = get_class($record);
            $this->dbWriteConnection->getSQLBuilder()
                ->delete($className)
                //TODO 'id' - magic string
                ->eq('id', $record->getId())
                ->execute();
        }
    }
}