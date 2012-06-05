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

/**
 * Orm manager
 * Gets finders and manages db connections
 */
abstract class ManagerAbstract
{
    const DEFAULT_CONNECTION_NAME = 'default';
    protected $connectionNames = array();
    private $sqlReadOnlyConnections = array();
    private $crudConnections = array();
    private $container;
    private $nameProvider;
    private $identityMap;
    protected $unitOfWork;
    private $mappers = array();
    private $finders = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        //TODO static
        StaticAware::setOrm($this);
        $this->identityMap = new IdentityMap;
        $this->unitOfWork  = new UnitOfWork;
        //TODO static
        RecordAware::setUnitOfWork($this->unitOfWork);
    }
    /**
     * Sets class name provider
     * @param ClassNameProviderInterface $nameProvider
     * @return ManagerAbstract
     */
    public function setClassNameProvider(ClassNameProviderInterface $nameProvider)
    {
        $this->nameProvider = $nameProvider;
        return $this;
    }
    /**
     * Gets class name provider
     * Make new instance of ClassNameProvider if provider is not set
     * @return ClassNameProviderInterface
     */
    protected function getClassNameProvider()
    {
        if (empty($this->nameProvider)) {
            $this->nameProvider = new ClassNameProvider;
        }
        return $this->nameProvider;
    }
    /**
     * Sets event dispatcher
     * Any object is allowed, you can set any specific dispatcher which you need
     * @param $eventDispatcher
     * @return ManagerAbstract
     */
    public function setContainer(ServiceContainerInterface $container)
    {
        $this->container = $container;
        //TODO static
        StaticAware::setServiceContainer($this->container);
        return $this;
    }
    /**
     * @return mixed
     */
    protected function getContainer()
    {
        if (empty($this->container)) {
            $this->container = new ServiceContainer();
        }
        return $this->container;
    }
    /**
     * Sets crud connection
     * If $name is not provided, sets default connection
     * @param \Grace\CRUD\CRUDInterface $crud
     * @param string                    $name
     * @return ManagerAbstract
     */
    public function setCrudConnection(CRUDInterface $crud, $name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        $this->crudConnections[$name] = $crud;
        return $this;
    }
    /**
     * Gets crud connection by name
     * If $name is not provided, gets default connection
     * If connection for this $name is not defined tries to create new CRUD\DBMasterDriver
     * from sql connection which associated with this name
     * @param string $name
     * @return CRUDInterface
     */
    public function getCrudConnection($name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        if (!isset($this->crudConnections[$name]) and isset($this->sqlReadOnlyConnections[$name])) {
            $this->crudConnections[$name] = new DBMasterDriver($this->sqlReadOnlyConnections[$name]);
        }
        if (!isset($this->crudConnections[$name])) {
            return null;
        }
        return $this->crudConnections[$name];
    }
    /**
     * Checks if crud connection with this name is set
     * @param string $name
     * @return bool
     */
    protected function hasCrudConnection($name)
    {
        return isset($this->crudConnections[$name]);
    }
    /**
     * Sets sql connection by name
     * If $name is not provided, sets default connection
     * @param \Grace\DBAL\InterfaceConnection $sqlReadOnly
     * @param string                          $name
     * @return ManagerAbstract
     */
    public function setSqlReadOnlyConnection(InterfaceConnection $sqlReadOnly, $name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        $this->sqlReadOnlyConnections[$name] = $sqlReadOnly;
        return $this;
    }
    /**
     * Gets sql connection by name
     * If $name is not provided, gets default connection
     * @param string $name
     * @return InterfaceConnection
     */
    public function getSqlReadOnlyConnection($name = '')
    {
        if ($name == '') {
            $name = self::DEFAULT_CONNECTION_NAME;
        }
        if (!isset($this->sqlReadOnlyConnections[$name])) {
            return null;
        }
        return $this->sqlReadOnlyConnections[$name];
    }
    /**
     * Checks if sql connection with this name is set
     * @param string $name
     * @return bool
     */
    protected function hasSqlReadOnlyConnection($name)
    {
        return isset($this->sqlReadOnlyConnections[$name]);
    }
    /**
     * Gets connection name which associated with model $className
     * @param string $className
     * @return string
     */
    protected function getConnectionNameByClass($className)
    {
        if (isset($this->connectionNames[$className])) {
            return $this->connectionNames[$className];
        }
        return self::DEFAULT_CONNECTION_NAME;
    }
    /**
     * Gets finder which associated with model $className
     * @param $className
     * @param $finderClassName for extra-finders
     * @return FinderSql
     */
    protected function getFinder($className, $finderClassName = '', $tableName = '')
    {
        if ($finderClassName == '') {
            $finderClassName = $className;
        }
        if ($tableName == '') {
            $tableName = $className;
        }
        if (!isset($this->finders[$finderClassName])) {
            $connectionName = $this->getConnectionNameByClass($className);

            $nameProvider        = $this->getClassNameProvider();
            $fullFinderClassName = $nameProvider->getFinderClass($finderClassName);

            $finder =
                new $fullFinderClassName($this->identityMap, $this->getMapper($className), $tableName, $nameProvider->getModelClass($className), $nameProvider->getCollectionClass($className));

            if ($finder instanceof Aware) {
                $finder->setOrm($this);
                $finder->setContainer($this->getContainer());
            }
            if ($finder instanceof FinderCrud) {
                $finder->setCrud($this->getCrudConnection($connectionName));
            }
            if ($finder instanceof FinderSql) {
                $finder->setSqlReadOnly($this->getSqlReadOnlyConnection($connectionName));
            }

            $this->finders[$finderClassName] = $finder;
        }

        return $this->finders[$finderClassName];
    }
    /**
     * Gets mapper which associated with model $className
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
    /**
     * Commit all changer from unit of work into database
     */
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

        $this->unitOfWork->flush();
    }
}