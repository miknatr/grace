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
 * Record observer
 */
class RecordObserver
{
    /**
     * @var ServiceContainer;
     */
    private $container;
    /**
     * @param ServiceContainer $container
     */
    public function __construct(ServiceContainerInterface $container = null)
    {
        $this->container = $container;
    }
    /**
     * @return \Grace\ORM\ServiceContainer
     */
    public function getContainer()
    {
        return $this->container;
    }


    public function onInsert(Record $record) {}
    public function onChange(Record $record) {}
    public function onDelete(Record $record) {}
}