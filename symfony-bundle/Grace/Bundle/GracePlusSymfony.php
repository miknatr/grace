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
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Monolog\Logger;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class GracePlusSymfony extends Grace
{
    public $eventDispatcher;
    public $logger;
    public $roleHierarchy;

    public function __construct(
        ConnectionInterface $db,
        ClassNameProvider $classNameProvider,
        ModelObserver $modelObserver,
        TypeConverter $typeConverter,
        Config $config,
        CacheInterface $cache,
        EventDispatcher $eventDispatcher,
        Logger $logger,
        RoleHierarchyInterface $roleHierarchy
    ) {
        parent::__construct($db, $classNameProvider, $modelObserver, $typeConverter, $config, $cache);

        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
        $this->roleHierarchy   = $roleHierarchy;
    }
}
