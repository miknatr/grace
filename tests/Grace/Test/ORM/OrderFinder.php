<?php

namespace Grace\Test\ORM;

use Grace\ORM\Finder;

class OrderFinder extends Finder
{
    public function getContainerPublic()
    {
        return $this->getContainer();
    }
    public function getNameColumn()
    {
        return $this
            ->getSelectBuilder()
            ->fields('name')
            ->fetchColumn();
    }
    /**
     * @return OrderCollection
     */
    public function getAllRecords()
    {
        return $this
            ->getSelectBuilder()
            ->fetchAll();
    }
}