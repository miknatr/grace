<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;
use Grace\CRUD\CRUDInterface;
use Grace\CRUD\DBMasterDriver;

abstract class ManagerAbstract implements ManagerInterface
{
    private $sqlReadOnly;
    private $crud;
    private $eventDispatcher;
    private $nameProvider;
    private $identityMap;
    private $unitOfWork;
    private $mappers = array();
    private $finders = array();

    public function __construct(InterfaceConnection $sqlReadOnly, CRUDInterface $crud = null, $eventDispatcher = null,
                                ClassNameProviderInterface $nameProvider = null)
    {
        $this->sqlReadOnly = $sqlReadOnly;
        if ($crud == null) {
            $this->crud = new DBMasterDriver($sqlReadOnly);
        } else {
            $this->crud = $crud;
        }
        $this->eventDispatcher = $eventDispatcher;
        if ($nameProvider == null) {
            $this->nameProvider = new ClassNameProvider;
        } else {
            $this->nameProvider = $nameProvider;
        }
        $this->identityMap = new IdentityMap;
        $this->unitOfWork  = new UnitOfWork;
    }
    protected function getFinder($className)
    {
        if (!isset($this->finders[$className])) {
            $fullFinderClassName       = $this->nameProvider->getFinderClass($className);
            $this->finders[$className] =
                new $fullFinderClassName($this->eventDispatcher, $this->unitOfWork, $this->identityMap, $this->sqlReadOnly, $this->crud, $this->getMapper($className), $className, $this->nameProvider->getModelClass($className), $this->nameProvider->getCollectionClass($className));
        }
        return $this->finders[$className];
    }
    /**
     * @param string $className
     * @return MapperInterface
     */
    private function getMapper($className)
    {
        if (!isset($this->mappers[$className])) {
            $fullClassName             = $this->nameProvider->getMapperClass($className);
            $this->mappers[$className] = new $fullClassName;
        }
        return $this->mappers[$className];
    }
    public function commit()
    {
        foreach ($this->unitOfWork->getNewRecords() as $record) {
            $className = $this->nameProvider->getBaseClass(get_class($record));
            $changes   = $this
                ->getMapper($className)
                ->convertRecordArrayToDbRow($record->asArray());
            $this->crud->insertById($className, $record->getId(), $changes);
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            $className = $this->nameProvider->getBaseClass(get_class($record));
            $changes   = $this
                ->getMapper($className)
                ->getRecordChanges($record->asArray(), $record->getDefaultFields());
            if (count($changes) > 0) {
                $this->crud->updateById($className, $record->getId(), $changes);
            }
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            $className = $this->nameProvider->getBaseClass(get_class($record));
            $this->crud->deleteById($className, $record->getId());
        }
    }
}