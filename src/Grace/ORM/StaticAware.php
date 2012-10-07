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

/**
 * Gets access to orm manager and container
 */
abstract class StaticAware
{
    static private $orm;
    /**
     * @static
     * @param ManagerAbstract $orm
     */
    final static public function setOrm(ManagerAbstract $orm)
    {
        self::$orm = $orm;
    }
    /**
     * Gets orm manager
     * @return ManagerAbstract
     */
    final public function getOrm()
    {
        return self::$orm;
    }


    static private $container;
    /**
     * @static
     * @param ServiceContainerInterface $container
     */
    final static public function setServiceContainer(ServiceContainerInterface $container)
    {
        self::$container = $container;
    }
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final public function getContainer()
    {
        return self::$container;
    }


    static private $unitOfWork;
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final public function getUnitOfWork()
    {
        if (empty(self::$unitOfWork)) {
            self::$unitOfWork = new UnitOfWork();
        }
        return self::$unitOfWork;
    }


    static private $identityMap;
    /**
     * Gets IdentityMap
     * @return IdentityMap
     */
    final public function getIdentityMap()
    {
        if (empty(self::$identityMap)) {
            self::$identityMap = new IdentityMap;
        }
        return self::$identityMap;
    }
}