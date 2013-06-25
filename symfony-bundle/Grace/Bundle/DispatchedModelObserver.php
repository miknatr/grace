<?php

namespace Grace\Bundle;

use Grace\Bundle\Event\RecordChangeEvent;
use Grace\Bundle\ModelAbstractPlusSymfony;
use Grace\ORM\ModelAbstract;
use Grace\ORM\Service\ModelObserver;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DispatchedModelObserver extends ModelObserver
{
    const BEFORE_INSERT = 'before_insert';
    const BEFORE_CHANGE = 'before_change';
    const BEFORE_DELETE = 'before_delete';
    const AFTER_INSERT  = 'after_insert';
    const AFTER_CHANGE  = 'after_change';
    const AFTER_DELETE  = 'after_delete';

    /** @var EventDispatcher */
    protected $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onBeforeInsert(ModelAbstract $model)
    {
        if ($model instanceof ModelAbstractPlusSymfony) {
            $model->ensureValid();
        }
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_INSERT));
    }

    public function onBeforeChange(ModelAbstract $model)
    {
        if ($model instanceof ModelAbstractPlusSymfony) {
            $model->ensureValid();
        }
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_CHANGE));
    }

    public function onBeforeDelete(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::BEFORE_DELETE));
    }

    public function onAfterInsert(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_INSERT));
    }

    public function onAfterChange(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_CHANGE));
    }

    public function onAfterDelete(ModelAbstract $model)
    {
        $this->eventDispatcher->dispatch('recordChange', new RecordChangeEvent($model, self::AFTER_DELETE));
    }
}
