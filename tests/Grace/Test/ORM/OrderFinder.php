<?php

namespace Grace\Test\ORM;

use Grace\ORM\Finder;

class OrderFinder extends Finder {
    public function getEventDispatcherPublic() {
        return $this->getEventDispatcher();
    }
}