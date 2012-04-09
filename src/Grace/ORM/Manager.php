<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;

abstract class Manager implements ManagerInterface {
    private $sqlReadConnection;
    private $crudConnection;
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
        UnitOfWork $unitOfWork, InterfaceConnection $sqlReadConnection,
        InterfaceConnection $crudConnection) {

        $this->sqlReadConnection = $sqlReadConnection;
        $this->crudConnection = $crudConnection;
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = $identityMap;
        $this->unitOfWork = $unitOfWork;
        $this->modelsNamespace = $modelsNamespace;
    }
    protected function getFinder($className) {
        if (!isset($this->finders[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . '';
            $fullFinderClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Finder';
            $fullCollectionClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Collection';
            $this->finders[$className] = new $fullFinderClassName($this->eventDispatcher,
                    $this->unitOfWork, $this->identityMap,
                    $this->sqlReadConnection, $this->getMapper($className),
                    $className, $fullClassName, $fullCollectionClassName);
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
            $this->mappers[$className] = new $fullClassName;
        }
        return $this->mappers[$className];
    }
    public function commit() {
        foreach ($this->unitOfWork->getNewRecords() as $record) {
            $className = $this->trimFullClassName($record);
            $changes = $this->getMapper($className)
                ->convertRecordArrayToDbRow($record->asArray());
            $this->crudConnection->getSQLBuilder()
                ->insert($className)
                ->values($changes)
                ->execute();
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            $className = $this->trimFullClassName($record);
            $changes = $this->getMapper($className)
                ->getRecordChanges($record->asArray(),
                $record->getDefaultFields());
            if (count($changes) > 0) {
                $this->crudConnection->getSQLBuilder()
                    ->update($className)
                    ->values($changes)
                    //TODO 'id' - magic string
                    ->eq('id', $record->getId())
                    ->execute();
            }
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            $className = $this->trimFullClassName($record);
            $this->crudConnection->getSQLBuilder()
                ->delete($className)
                //TODO 'id' - magic string
                ->eq('id', $record->getId())
                ->execute();
        }
    }
    private function trimFullClassName(Record $record) {
        return substr(get_class($record), strlen($this->modelsNamespace . '\\'));
    }
}