<?php

namespace Grace\Test\ORM;

use Grace\ORM\ManagerAbstract;

class RealManager extends ManagerAbstract {
    
    /**
     * @return OrderFinder 
     */
    public function getOrderFinder() {
        return $this->getFinder('Order');
    }
}