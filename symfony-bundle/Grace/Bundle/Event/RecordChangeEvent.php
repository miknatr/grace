<?php

namespace Grace\Bundle\Event;

use Grace\ORM\ModelAbstract;
use Symfony\Component\EventDispatcher\Event;

class RecordChangeEvent extends Event
{
    /** @var ModelAbstract */
    public $record;
    public $changeType;

    public function __construct(ModelAbstract $record, $changeType)
    {
        $this->record     = $record;
        $this->changeType = $changeType;
    }
}
