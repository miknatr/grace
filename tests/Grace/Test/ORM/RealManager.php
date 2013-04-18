<?php

namespace Grace\Test\ORM;

use Grace\ORM\ORMManagerAbstract;
use Grace\ORM\Service\UnitOfWork;

class RealManager extends ORMManagerAbstract
{
    /**
     * @return OrderFinder
     */
    public function getOrderFinder()
    {
        return $this->getFinder('Order');
    }
}
