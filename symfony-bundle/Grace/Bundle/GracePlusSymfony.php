<?php

namespace Grace\Bundle;

use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\ORM\Grace;
use Grace\Cache\CacheInterface;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Config\Config;
use Grace\ORM\Service\ModelObserver;
use Grace\ORM\Service\TypeConverter;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Monolog\Logger;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Validator\Validator;

class GracePlusSymfony extends Grace
{
    /** @var EventDispatcher */
    public $eventDispatcher;
    /** @var Logger */
    public $logger;
    /** @var RoleHierarchyInterface */
    public $roleHierarchy;
    /** @var Validator */
    public $validator;

    /** @var DispatchedModelObserver */ // если не перекрывать из базового (что не нужно, т.к. там уже определен), то шторм не хочет понимать, что это именно DispatchedModelObserver, даже если у класса @property-аннотацию написать, поэтому так
    public $modelObserver;

    public function __construct(
        ConnectionInterface $db,
        ClassNameProvider $classNameProvider,
        ModelObserver $modelObserver,
        TypeConverter $typeConverter,
        Config $config,
        CacheInterface $cache,
        EventDispatcher $eventDispatcher,
        Logger $logger,
        RoleHierarchyInterface $roleHierarchy,
        Validator $validator
    ) {
        if (!($modelObserver instanceof DispatchedModelObserver)) {
            throw new \LogicException('Model observer must be instance of DispatchedModelObserver for properly work of GracePlusSymfony extension');
        }

        parent::__construct($db, $classNameProvider, $modelObserver, $typeConverter, $config, $cache);

        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
        $this->roleHierarchy   = $roleHierarchy;
        $this->validator       = $validator;
    }

    public function doOnCommitDone(callable $callback)
    {
        $this->modelObserver->doOnCommitDone($callback);
    }
}
