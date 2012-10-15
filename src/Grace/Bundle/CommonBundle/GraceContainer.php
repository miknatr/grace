<?php

namespace Grace\Bundle\CommonBundle;

use Grace\ORM\ServiceContainerInterface;
use Grace\Bundle\CommonBundle\Cache\Cache;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Role\RoleHierarchy;
use Monolog\Logger;

class GraceContainer implements ServiceContainerInterface
{
    /**
     * @var RoleHierarchy
     */
    private $roleHierarchy;
    public function setRoleHierarchy($roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }
    public function getRoleHierarchy()
    {
        return $this->roleHierarchy;
    }

    /**
     * @var Cache
     */
    private $cache;
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
    }
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;
    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @var Logger
     */
    private $logger;
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }
    public function getLogger()
    {
        return $this->logger;
    }
}
