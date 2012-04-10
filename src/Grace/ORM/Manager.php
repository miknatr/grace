<?php

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;
use Grace\CRUD\CRUDInterface;

abstract class Manager implements ManagerInterface {
    private $modelsNamespace;
    private $sqlReadOnly;
    private $crud;
    private $eventDispatcher;
    private $identityMap;
    private $unitOfWork;
    private $mappers = array();
    private $finders = array();

    public function __construct(EventDispatcher $eventDispatcher,
        $modelsNamespace, InterfaceConnection $sqlReadOnly, CRUDInterface $crud) {

        $this->sqlReadOnly = $sqlReadOnly;
        $this->crud = $crud;
        $this->modelsNamespace = $modelsNamespace;
        $this->eventDispatcher = $eventDispatcher;
        $this->identityMap = new IdentityMap;
        $this->unitOfWork = new UnitOfWork;
    }
    protected function getFinder($className) {
        if (!isset($this->finders[$className])) {
            $fullClassName = '\\' . $this->modelsNamespace . '\\' . $className . '';
            $fullFinderClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Finder';
            $fullCollectionClassName = '\\' . $this->modelsNamespace . '\\' . $className . 'Collection';
            $this->finders[$className] = new $fullFinderClassName($this->eventDispatcher,
                    $this->unitOfWork, $this->identityMap,
                    $this->sqlReadOnly, $this->crud, $this->getMapper($className),
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
            $this->crud->insertById($className, $record->getId(), $changes);
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            $className = $this->trimFullClassName($record);
            $changes = $this->getMapper($className)
                ->getRecordChanges($record->asArray(),
                $record->getDefaultFields());
            if (count($changes) > 0) {
                $this->crud->updateById($className, $record->getId(), $changes);
            }
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            $className = $this->trimFullClassName($record);
            $this->crud->deleteById($className, $record->getId());
        }
    }
    private function trimFullClassName(Record $record) {
        return substr(get_class($record), strlen($this->modelsNamespace . '\\'));
    }
}