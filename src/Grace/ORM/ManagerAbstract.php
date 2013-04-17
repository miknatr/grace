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
use Grace\SQLBuilder\Factory;
use Grace\CRUD\CRUDInterface;
use Grace\CRUD\CRUDCommitableInterface;
use Grace\CRUD\DBMasterDriver;
use Grace\TypeConverter\Converter;

/**
 * Orm manager
 * Gets finders and manages db connections
 */
abstract class ManagerAbstract
{

    static private $instances;
    static private $currentHash;
    /**
     * @return ManagerAbstract
     */
    static final public function getCurrent()
    {
        return self::$instances[self::$currentHash];
    }
    final public function __construct()
    {
        self::$instances[spl_object_hash($this)] = $this;
        $this->touch();
    }
    final public function touch()
    {
        self::$currentHash = spl_object_hash($this);
    }


    /**
     * Commit all changer from unit of work into database
     */
    public function commit()
    {
        foreach ($this->sqlReadOnlyConnections as $connection) {
            $connection->start();
        }

        try {
            foreach ($this->getUnitOfWork()->getNewRecords() as $record) {
                $className = $this->getClassNameProvider()->getBaseClass(get_class($record));
                $crud      = $this->getSqlReadOnlyConnection($this->getConnectionNameByClass($className));=
                $crud->insertById($className, $record->getId(), $record->getAsDbRow());
            }


            foreach ($this->getUnitOfWork()->getChangedRecords() as $record) {
                $className = $this->getClassNameProvider()->getBaseClass(get_class($record));
                $crud      = $this->getCrudConnection($this->getConnectionNameByClass($className));
                $changes = $record->getAsDbRowChangesOnlyAndCleanDefaultFields();

                if (count($changes) > 0) {
                    $crud->updateById($className, $record->getId(), $changes);
                }
            }


            foreach ($this->getUnitOfWork()->getDeletedRecords() as $record) {
                $className = $this->getClassNameProvider()->getBaseClass(get_class($record));
                $crud      = $this->getCrudConnection($this->getConnectionNameByClass($className));
                $crud->deleteById($className, $record->getId());
            }


            foreach ($this->getUnitOfWork()->getNewRecords() as $record) {
                $record->onCommitInsert();
                $this->getRecordObserver()->onInsert($record);
                $record->clearIsNewMarker();
            }
            foreach ($this->getUnitOfWork()->getChangedRecords() as $record) {
                $record->onCommitChange();
                $this->getRecordObserver()->onChange($record);
            }
            foreach ($this->getUnitOfWork()->getDeletedRecords() as $record) {
                $record->onCommitDelete();
                $this->getRecordObserver()->onDelete($record);
            }
        } catch (\Exception $e) {
            foreach ($this->sqlReadOnlyConnections as $connection) {
                $connection->rollback();
            }
            throw $e;
        }

        foreach ($this->sqlReadOnlyConnections as $connection) {
            $connection->commit();
        }

        $this->clean();
    }
    /**
     * Clean all object caches
     */
    public function clean()
    {
        $this->getUnitOfWork()->clean();
        $this->getIdentityMap()->clean();
    }



    //MAPPERS AND FINDERS

    private $finders = array();
    /**
     * Gets finder which associated with model $className
     * @param $className
     * @return FinderSql
     */
    public function getFinder($className)
    {
        if (!isset($this->finders[$className])) {
            $connectionName = $this->getConnectionNameByClass($className);
            $fullFinderClassName = $this->getClassNameProvider()->getFinderClass($className);

            $finder = new $fullFinderClassName($className);

            if ($finder instanceof FinderSql) {
                $finder->setSqlReadOnly($this->getSqlReadOnlyConnection($connectionName));
            }

            $this->finders[$className] = $finder;
        }

        return $this->finders[$className];
    }



    //DB CONNECTIONS

    const DEFAULT_CONNECTION_NAME = 'default';
    protected $connectionNames = array();
    /**
     * Gets connection name which associated with model $className
     * @param string $className
     * @return string
     */
    public function getConnectionNameByClass($className)
    {
        if (isset($this->connectionNames[$className])) {
            return $this->connectionNames[$className];
        }
        return self::DEFAULT_CONNECTION_NAME;
    }

    /**
     * @var InterfaceConnection[]
     */
    private $sqlReadOnlyConnections = array();
    /**
     * Sets sql connection by name
     * If $name is not provided, sets default connection
     * @param InterfaceConnection $sqlReadOnly
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

    public function setSqlBuilderPrefix($prefix)
    {
        Factory::setNamespacePrefix($prefix);
        return $this;
    }


    //ORM STORAGES AND SERVICES

    private $container;
    /**
     * Sets service container
     * @static
     * @param ServiceContainerInterface $container
     * @return ManagerAbstract
     */
    final public function setContainer(ServiceContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final public function getContainer()
    {
        return $this->container;
    }


    private $unitOfWork;
    /**
     * Gets service container
     * @return UnitOfWork
     */
    final public function getUnitOfWork()
    {
        if (empty($this->unitOfWork)) {
            $this->unitOfWork = new UnitOfWork;
        }
        return $this->unitOfWork;
    }


    private $identityMap;
    /**
     * Gets IdentityMap
     * @return IdentityMap
     */
    final public function getIdentityMap()
    {
        if (empty($this->identityMap)) {
            $this->identityMap = new IdentityMap;
        }
        return $this->identityMap;
    }


    private $nameProvider;
    /**
     * Sets class name provider
     * @param ClassNameProviderInterface $nameProvider
     * @return ManagerAbstract
     */
    final public function setClassNameProvider(ClassNameProviderInterface $nameProvider)
    {
        $this->nameProvider = $nameProvider;
        return $this;
    }
    /**
     * Gets class name provider
     * Make new instance of ClassNameProvider if provider is not set
     * @return ClassNameProviderInterface
     */
    final public function getClassNameProvider()
    {
        if (empty($this->nameProvider)) {
            $this->nameProvider = new ClassNameProvider;
        }
        return $this->nameProvider;
    }


    private $recordObserver;
    /**
     * Sets class RecordObserver
     * @param RecordObserver $recordObserver
     * @return ManagerAbstract
     */
    final public function setRecordObserver(RecordObserver $recordObserver)
    {
        $this->recordObserver = $recordObserver;
        return $this;
    }
    /**
     * Gets class RecordObserver
     * Make new instance of RecordObserver if provider is not set
     * @return RecordObserver
     */
    final public function getRecordObserver()
    {
        if (empty($this->recordObserver)) {
            $this->recordObserver = new RecordObserver;
        }
        return $this->recordObserver;
    }


    private $typeConverter;
    /**
     * Gets service container
     * @return Converter
     */
    final public function getTypeConverter()
    {
        if (empty($this->typeConverter)) {
            $this->typeConverter = new Converter();
        }
        return $this->typeConverter;
    }


    static protected $modelsConfig = array(); //generator overrides this
    /**
     * Gets config
     * @return array
     */
    final public function getModelsConfig()
    {
        return static::$modelsConfig;
    }
}
