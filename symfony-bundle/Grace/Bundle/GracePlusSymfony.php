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
        parent::__construct($db, $classNameProvider, $modelObserver, $typeConverter, $config, $cache);

        $this->eventDispatcher = $eventDispatcher;
        $this->logger          = $logger;
        $this->roleHierarchy   = $roleHierarchy;
        $this->validator       = $validator;
    }
}
