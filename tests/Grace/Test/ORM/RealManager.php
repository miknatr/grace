<?php

namespace Grace\Test\ORM;

use Grace\ORM\Manager;

class RealManager extends Manager {
    
    /**
     * @return OrderFinder 
     */
    public function getOrderFinder() {
        return $this->getFinder('Order');
    }
}