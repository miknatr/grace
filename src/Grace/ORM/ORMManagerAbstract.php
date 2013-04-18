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

use Grace\DBAL\AbstractConnection\InterfaceConnection;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\IdentityMap;
use Grace\ORM\Service\RecordObserver;
use Grace\ORM\Service\UnitOfWork;
use Grace\SQLBuilder\Factory;
use Grace\ORM\Service\TypeConverter;
use Grace\ORM\Config\ModelsConfig;

abstract class ORMManagerAbstract
{
    //STOPPER это теперь выпиливается и переопределяется в файндерах
    public function setSqlBuilderPrefix($prefix)
    {
        Factory::setNamespacePrefix($prefix);
        return $this;
    }

    //public access for services is optimization, by convention you must not change them
    public $db;
    public $classNameProvider;
    public $recordObserver;
    public $typeConverter;
    public $identityMap;
    public $unitOfWork;
    public $modelsConfig;

    public function __construct(InterfaceConnection $db,
                                ClassNameProvider $classNameProvider,
                                RecordObserver $recordObserver,
                                ModelsConfig $modelsConfig
    )
    {
        $this->db                = $db;
        $this->classNameProvider = $classNameProvider;
        $this->recordObserver    = $recordObserver;


        $this->typeConverter = new TypeConverter();
        $this->identityMap   = new IdentityMap;
        $this->unitOfWork    = new UnitOfWork;

        $this->modelsConfig = $modelsConfig;
    }


    public function commit()
    {
        $db             = $this->db;
        $unitOfWork     = $this->unitOfWork;
        $recordObserver = $this->recordObserver;


        $db->start();

        try {
            foreach ($unitOfWork->getNewRecords() as $record) {
                $recordObserver->onBeforeInsert($record);
            }
            foreach ($unitOfWork->getChangedRecords() as $record) {
                $recordObserver->onBeforeChange($record);
            }
            foreach ($unitOfWork->getDeletedRecords() as $record) {
                $recordObserver->onBeforeDelete($record);
            }


            foreach ($unitOfWork->getNewRecords() as $record) {
                $this->getFinder($record->getBaseClass())->insertRecordOnCommit($record);
            }

            foreach ($unitOfWork->getChangedRecords() as $record) {
                $this->getFinder($record->getBaseClass())->updateRecordOnCommit($record);
            }

            foreach ($unitOfWork->getDeletedRecords() as $record) {
                $this->getFinder($record->getBaseClass())->deleteRecordOnCommit($record);
            }


            foreach ($unitOfWork->getNewRecords() as $record) {
                $recordObserver->onAfterInsert($record);
            }
            foreach ($unitOfWork->getChangedRecords() as $record) {
                $recordObserver->onAfterChange($record);
            }
            foreach ($unitOfWork->getDeletedRecords() as $record) {
                $recordObserver->onAfterDelete($record);
            }


            foreach ($unitOfWork->getNewRecords() as $record) {
                $record->flushDefaults();
            }
            foreach ($unitOfWork->getChangedRecords() as $record) {
                $record->flushDefaults();
            }
            foreach ($unitOfWork->getDeletedRecords() as $record) {
                $record->flushDefaults();
            }

        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }

        $db->commit();

        $this->clean();
    }
    public function clean()
    {
        $this->unitOfWork->clean();
        $this->identityMap->clean();
    }


    private $finders = array();
    public function getFinder($baseClass)
    {
        if (!isset($this->finders[$baseClass])) {
            $fullFinderClassName = $this->classNameProvider->getFinderClass($baseClass);
            $finder = new $fullFinderClassName($baseClass, $this);
            $this->finders[$baseClass] = $finder;
        }

        return $this->finders[$baseClass];
    }
}
