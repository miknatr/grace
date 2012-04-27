<?php

namespace Grace\Test\EventDispatcher;

use Grace\EventDispatcher\SubscriberInterface;

class SubscriberPlug implements SubscriberInterface
{
    public $wasFirstEvent = false;
    public $wasSecondEvent = false;
    public function firstEvent($context)
    {
        $this->wasFirstEvent = true;
    }
    public function secondEvent($context)
    {
        $this->wasSecondEvent = true;
    }
    public function doubleFilter($value, $context)
    {
        return $value . $value;
    }
}