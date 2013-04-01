<?php

namespace Grace\Test\ORM;

use Grace\ORM\FinderSql;

class OrderFinder extends FinderSql
{
    public function getContainerPublic()
    {
        return $this->getContainer();
    }
    public function getNameColumn()
    {
        return $this
            ->getSelectBuilder()
            ->field('name')
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
