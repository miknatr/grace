<?php

namespace Grace\Bundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Grace\ORM\ModelAbstract;

class RecordChangeEvent extends Event
{
    public $record;
    public $changeType;
    public function __construct(ModelAbstract $record, $changeType)
    {
        $this->record = $record;
        $this->changeType = $changeType;
    }
}
