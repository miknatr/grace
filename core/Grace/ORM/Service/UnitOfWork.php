<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM\Service;

use Grace\ORM\ModelAbstract;

class UnitOfWork
{
    private $newModels     = array();
    private $changedModels = array();
    private $deletedModels = array();


    public function markAsNew(ModelAbstract $model)
    {
        //TODO на эти вещи (когди одновременно удаляется/добавляется/изменяется) нужен тест, модель должна попадать только в один раздел, причем нью всегда перекрывает ченжед и тд
        if (isset($this->changedModels[spl_object_hash($model)])) {
            unset($this->changedModels[spl_object_hash($model)]);
        }
        if (!isset($this->deletedModels[spl_object_hash($model)])) {
            $this->newModels[spl_object_hash($model)] = $model;
        }
    }
    public function markAsChanged(ModelAbstract $model)
    {
        if (!isset($this->newModels[spl_object_hash($model)]) and !isset($this->deletedModels[spl_object_hash($model)])) {
            $this->changedModels[spl_object_hash($model)] = $model;
        }
    }
    public function markAsDeleted(ModelAbstract $model)
    {
        if (isset($this->newModels[spl_object_hash($model)])) {
            unset($this->newModels[spl_object_hash($model)]);
        }
        if (isset($this->changedModels[spl_object_hash($model)])) {
            unset($this->changedModels[spl_object_hash($model)]);
        }
        $this->deletedModels[spl_object_hash($model)] = $model;
    }
    public function revert(ModelAbstract $model)
    {
        unset($this->newModels[spl_object_hash($model)]);
        unset($this->changedModels[spl_object_hash($model)]);
        unset($this->deletedModels[spl_object_hash($model)]);
    }


    /** @return ModelAbstract[] */
    public function getNewModels()
    {
        return $this->newModels;
    }
    /** @return ModelAbstract[] */
    public function getChangedModels()
    {
        return $this->changedModels;
    }
    /** @return ModelAbstract[] */
    public function getDeletedModels()
    {
        return $this->deletedModels;
    }
    public function clean()
    {
        $this->newModels     = array();
        $this->changedModels = array();
        $this->deletedModels = array();
    }
    public function needCommit()
    {
        return (count($this->newModels) > 0 or count($this->changedModels) > 0 or count($this->deletedModels) > 0);
    }

    protected $committedProps = array();
    public function saveCommittedProps(ModelAbstract $model)
    {
        $this->committedProps[] = array(
            'model' => $model,
            'props' => $model->getProperties(),
        );
    }

    public function flushCommittedPropsInModels()
    {
        foreach ($this->committedProps as $row) {
            /** @var ModelAbstract $model */
            $model = $row['model'];
            $model->setOriginalProperties($row['props']);
        }
    }
}
