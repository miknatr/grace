<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

use Grace\DBAL\InterfaceConnection;
use Grace\CRUD\CRUDInterface;
use Grace\CRUD\DBMasterDriver;

abstract class ManagerAbstract
{
    const DEFAULT_CONNECTION_NAME = 'default';
    private $connectionNames = array();
    private $sqlReadOnlyConnections = array();
    private $crudConnections = array();
    private $eventDispatcher;
    private $nameProvider;
    private $identityMap;
    private $unitOfWork;
    private $mappers = array();
    private $finders = array();

    public function __construct()
    {
        $this->identityMap = new IdentityMap;
        $this->unitOfWork  = new UnitOfWork;
    }
    public function setClassNameProvider(ClassNameProviderInterface $nameProvider)
    {
        $this->nameProvider = $nameProvider;
        return $this;
    }
    protected function getClassNameProvider()
    {
        if (empty($this->nameProvider)) {
            $this->nameProvider = new ClassNameProvider;
        }
        return $this->nameProvider;
    }
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
    protected function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
    public function setCrudConnection(CRUDInterface $crud, $name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        $this->crudConnections[$name] = $crud;
        return $this;
    }
    protected function getCrudConnection($name)
    {
        if (!isset($this->crudConnections[$name]) and isset($this->sqlReadOnlyConnections[$name])) {
            $this->crudConnections[$name] = new DBMasterDriver($this->sqlReadOnlyConnections[$name]);
        }
        if (!isset($this->crudConnections[$name])) {
            return null;
        }
        return $this->crudConnections[$name];
    }
    protected function hasCrudConnection($name)
    {
        return isset($this->crudConnections[$name]);
    }
    public function setSqlReadOnlyConnection(InterfaceConnection $sqlReadOnly, $name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        $this->sqlReadOnlyConnections[$name] = $sqlReadOnly;
        return $this;
    }
    protected function getSqlReadOnlyConnection($name)
    {
        if (!isset($this->sqlReadOnlyConnections[$name])) {
            return null;
        }
        return $this->sqlReadOnlyConnections[$name];
    }
    protected function hasSqlReadOnlyConnection($name)
    {
        return isset($this->sqlReadOnlyConnections[$name]);
    }
    protected function getConnectionNameByClass($className)
    {
        if (isset($this->connectionNames[$className])) {
            return $this->connectionNames[$className];
        }
        return self::DEFAULT_CONNECTION_NAME;
    }
    protected function getFinder($className)
    {
        if (!isset($this->finders[$className])) {
            $connectionName = $this->getConnectionNameByClass($className);

            $nameProvider              = $this->getClassNameProvider();
            $fullFinderClassName       = $nameProvider->getFinderClass($className);
            $this->finders[$className] =
                new $fullFinderClassName($this->eventDispatcher, $this->unitOfWork, $this->identityMap, $this->getMapper($className), $className, $nameProvider->getModelClass($className), $nameProvider->getCollectionClass($className), $this->getSqlReadOnlyConnection($connectionName), $this->getCrudConnection($connectionName));
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
            $fullClassName             = $this
                ->getClassNameProvider()
                ->getMapperClass($className);
            $this->mappers[$className] = new $fullClassName;
        }
        return $this->mappers[$className];
    }
    public function commit()
    {
        foreach ($this->unitOfWork->getNewRecords() as $record) {
            $className = $this
                ->getClassNameProvider()
                ->getBaseClass(get_class($record));
            $crud      = $this->getCrudConnection($this->getConnectionNameByClass($className));
            $changes   = $this
                ->getMapper($className)
                ->convertRecordArrayToDbRow($record->asArray());
            $crud->insertById($className, $record->getId(), $changes);
        }
        foreach ($this->unitOfWork->getChangedRecords() as $record) {
            $className = $this
                ->getClassNameProvider()
                ->getBaseClass(get_class($record));
            $crud      = $this->getCrudConnection($this->getConnectionNameByClass($className));
            $changes   = $this
                ->getMapper($className)
                ->getRecordChanges($record->asArray(), $record->getDefaultFields());
            if (count($changes) > 0) {
                $crud->updateById($className, $record->getId(), $changes);
            }
        }
        foreach ($this->unitOfWork->getDeletedRecords() as $record) {
            $className = $this
                ->getClassNameProvider()
                ->getBaseClass(get_class($record));
            $crud      = $this->getCrudConnection($this->getConnectionNameByClass($className));
            $crud->deleteById($className, $record->getId());
        }
    }
}