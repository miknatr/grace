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
    static private $container;

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
    final protected function getOrm()
    {
        return self::$orm;
    }
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
    final protected function getContainer()
    {
        return self::$container;
    }
}