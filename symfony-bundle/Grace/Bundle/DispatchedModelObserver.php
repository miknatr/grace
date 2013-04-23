<?php

namespace Grace\Bundle;

use Grace\ORM\Service\ModelObserver;
use Grace\ORM\ModelAbstract;
use Grace\Bundle\Event\RecordChangeEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DispatchedModelObserver extends ModelObserver
{
    const BEFORE_INSERT = 'before_insert';
    const BEFORE_CHANGE = 'before_change';
    const BEFORE_DELETE = 'before_delete';
    const AFTER_INSERT = 'after_insert';
    const AFTER_CHANGE = 'after_change';
    const AFTER_DELETE = 'after_delete';

    protected $eventDispatcher;
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onAfterInsert(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_INSERT));
    }
    public function onAfterChange(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_CHANGE));
    }
    public function onAfterDelete(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_DELETE));
    }
    public function onBeforeInsert(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_INSERT));
    }
    public function onBeforeChange(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_CHANGE));
    }
    public function onBeforeDelete(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_DELETE));
    }
}
