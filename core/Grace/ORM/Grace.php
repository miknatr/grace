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

use Grace\Cache\CacheInterface;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\IdentityMap;
use Grace\ORM\Service\ModelObserver;
use Grace\ORM\Service\UnitOfWork;
use Grace\ORM\Service\TypeConverter;
use Grace\ORM\Service\Config\Config;

class Grace
{
    //public access for services is optimization, by convention you must not change them
    public $db;
    public $classNameProvider;
    public $modelObserver;
    public $typeConverter;
    public $identityMap;
    public $unitOfWork;
    public $config;
    public $cache;

    public function __construct(
        ConnectionInterface $db,
        ClassNameProvider $classNameProvider,
        ModelObserver $modelObserver,
        TypeConverter $typeConverter,
        Config $config,
        CacheInterface $cache
    )
    {
        $this->db                = $db;
        $this->classNameProvider = $classNameProvider;
        $this->modelObserver     = $modelObserver;
        $this->typeConverter     = $typeConverter;
        $this->cache             = $cache;


        $this->identityMap   = new IdentityMap;
        $this->unitOfWork    = new UnitOfWork;

        $this->config = $config;
    }


    public function commit()
    {
        $db            = $this->db;
        $unitOfWork    = $this->unitOfWork;
        $modelObserver = $this->modelObserver;


        if ($unitOfWork->needCommit()) {
            $db->start();

            $i = 0;
            try {
                while ($unitOfWork->needCommit()) {
                    if ($i++ > 50) {
                        throw new \OutOfRangeException('Max reached');
                    }

                    $this->unitOfWork = new UnitOfWork();

                    foreach ($unitOfWork->getNewModels() as $model) {
                        $modelObserver->onBeforeInsert($model);
                    }
                    foreach ($unitOfWork->getChangedModels() as $model) {
                        $modelObserver->onBeforeChange($model);
                    }
                    foreach ($unitOfWork->getDeletedModels() as $model) {
                        $modelObserver->onBeforeDelete($model);
                    }


                    foreach ($unitOfWork->getNewModels() as $model) {
                        $this->getFinder($model->baseClass)->insertModelOnCommit($model);
                        $unitOfWork->saveCommittedProps($model);
                    }

                    foreach ($unitOfWork->getChangedModels() as $model) {
                        $this->getFinder($model->baseClass)->updateModelOnCommit($model);
                        $unitOfWork->saveCommittedProps($model);
                    }

                    foreach ($unitOfWork->getDeletedModels() as $model) {
                        $this->getFinder($model->baseClass)->deleteModelOnCommit($model);
                        $unitOfWork->saveCommittedProps($model);
                    }


                    foreach ($unitOfWork->getNewModels() as $model) {
                        $modelObserver->onAfterInsert($model);
                    }
                    foreach ($unitOfWork->getChangedModels() as $model) {
                        $modelObserver->onAfterChange($model);
                    }
                    foreach ($unitOfWork->getDeletedModels() as $model) {
                        $modelObserver->onAfterDelete($model);
                    }


                    $unitOfWork->flushCommittedPropsInModels();


                    foreach ($unitOfWork->getDeletedModels() as $model) {
                        $this->identityMap->unsetModel($model->baseClass, $model->id);
                    }

                    $unitOfWork->clean();
                    $unitOfWork = $this->unitOfWork;
                }
            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }

            $db->commit();
        }

        $this->clean();
    }
    public function clean()
    {
        $this->unitOfWork->clean();
        //$this->identityMap->clean();
    }


    private $finders = array();

    /**
     * @param $baseOrModelOrFinderClass
     * @return FinderAbstract
     */
    public function getFinder($baseOrModelOrFinderClass)
    {
        $baseClass = $this->classNameProvider->getBaseClass($baseOrModelOrFinderClass);

        if (!$baseClass) {
            return null;
        }

        if (!isset($this->finders[$baseClass])) {
            $fullFinderClassName = $this->classNameProvider->getFinderClass($baseClass);

            if (!class_exists($fullFinderClassName)) {
                return null;
            }

            $finder = new $fullFinderClassName($baseClass, $this);
            $this->finders[$baseClass] = $finder;
        }

        return $this->finders[$baseClass];
    }

    public function __get($name)
    {
        $finderSuffixPos = strpos($name, 'Finder');
        if ($finderSuffixPos !== false) {
            $modelName = ucfirst(substr($name, 0, $finderSuffixPos));
            $finder = $this->getFinder($modelName);
            if ($finder) {
                return $finder;
            }
        }

        throw new PropertyNotFoundException("No such property: {$name}");
    }
}
