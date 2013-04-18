<?php

namespace Grace\Bundle\CommonBundle;

use Grace\DBAL\AbstractConnection\InterfaceConnection;
use Grace\ORM\ORMManagerAbstract;
use Grace\Cache\CacheInterface;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Config\ModelsConfig;
use Grace\ORM\Service\RecordObserver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Monolog\Logger;

class ORMManager extends ORMManagerAbstract
{
    public $cache;
    public $eventDispatcher;
    public $logger;
    public $roleHierarchy;

    public function __construct(InterfaceConnection $db,
                                ClassNameProvider $classNameProvider,
                                RecordObserver $recordObserver,
                                ModelsConfig $modelsConfig,
                                CacheInterface $cache,
                                EventDispatcher $eventDispatcher,
                                Logger $logger,
                                RoleHierarchy $roleHierarchy
    )
    {
        parent::__construct($db, $classNameProvider, $recordObserver, $modelsConfig, $cache);

        $this->cache           = $cache;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
        $this->roleHierarchy   = $roleHierarchy;
    }
}
