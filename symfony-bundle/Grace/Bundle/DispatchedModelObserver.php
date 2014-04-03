<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\Bundle;

use Grace\ORM\ModelAbstract;
use Grace\ORM\Service\ModelObserver;

class DispatchedModelObserver extends ModelObserver
{
    /** @var callable */
    private $onBeforeInsert;
    /** @var callable */
    private $onBeforeChange;
    /** @var callable */
    private $onBeforeDelete;
    /** @var callable */
    private $onCommitDone;

    /**
     * STOPPER выпилить null и вернуть callable тайпхинты потом когда симфони уйдёт
     * @param callable $onBeforeInsert
     * @param callable $onBeforeChange
     * @param callable $onBeforeDelete
     * @param callable $onCommitDone
     */
    public function __construct($onBeforeInsert = null, $onBeforeChange = null, $onBeforeDelete = null, $onCommitDone = null)
    {
        $this->onBeforeInsert = $onBeforeInsert;
        $this->onBeforeChange = $onBeforeChange;
        $this->onBeforeDelete = $onBeforeDelete;
        $this->onCommitDone   = $onCommitDone;
    }

    public function onBeforeInsert(ModelAbstract $model)
    {
        parent::onBeforeInsert($model);

        if ($model instanceof ModelAbstractPlusSymfony) {
            $model->ensureValid();
        }

        call_user_func($this->onBeforeInsert, $model);
    }

    public function onBeforeChange(ModelAbstract $model)
    {
        parent::onBeforeChange($model);

        if ($model instanceof ModelAbstractPlusSymfony) {
            $model->ensureValid();
        }

        call_user_func($this->onBeforeChange, $model);
    }

    public function onBeforeDelete(ModelAbstract $model)
    {
        parent::onBeforeDelete($model);

        if ($model instanceof ModelAbstractPlusSymfony) {
            $model->ensureValid();
        }

        call_user_func($this->onBeforeDelete, $model);
    }

    public function onCommitDone()
    {
        parent::onCommitDone();

        call_user_func($this->onCommitDone);

        foreach ($this->onCommitDoneCallbacks as $callback) {
            call_user_func($callback);
        }
    }

    /** @var callable[] */
    protected $onCommitDoneCallbacks = array();

    public function doOnCommitDone(callable $callback)
    {
        $this->onCommitDoneCallbacks[] = $callback;
    }
}
