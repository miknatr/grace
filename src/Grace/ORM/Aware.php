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
abstract class Aware
{
    private $orm;
    private $container;

    /**
     * @param ManagerAbstract $orm
     */
    public function setOrm(ManagerAbstract $orm)
    {
        $this->orm = $orm;
    }
    /**
     * Gets orm manager
     * @return ManagerAbstract
     */
    final protected function getOrm()
    {
        return $this->orm;
    }
    /**
     * @param ServiceContainerInterface $container
     */
    public function setContainer(ServiceContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * Gets service container
     * @return ServiceContainerInterface
     */
    final protected function getContainer()
    {
        return $this->container;
    }
}