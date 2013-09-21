<?php

namespace Grace\Bundle;

use Grace\Bundle\Event\CommitDoneEvent;
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

    public function onCommitDone()
    {
        $this->eventDispatcher->dispatch('commitDone', new CommitDoneEvent());
    }
}
