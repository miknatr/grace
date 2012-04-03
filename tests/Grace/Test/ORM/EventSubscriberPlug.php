<?php

namespace Grace\Test\ORM;

use Grace\ORM\EventSubscriberInterface;

class EventSubscriberPlug implements EventSubscriberInterface {
    public $wasFirstEvent = false;
    public $wasSecondEvent = false;
    public function firstEvent($context) {
        $this->wasFirstEvent = true;
    }
    public function secondEvent($context) {
        $this->wasSecondEvent = true;
    }
    public function doubleFilter($value, $context) {
        return $value . $value;
    }
}