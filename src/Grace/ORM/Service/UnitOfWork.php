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

use Grace\ORM\RecordAbstract;

class UnitOfWork
{

    private $newRecords = array();
    private $changedRecords = array();
    private $deletedRecords = array();


    public function markAsNew(RecordAbstract $record)
    {
        if (!isset($this->deletedRecords[spl_object_hash($record)])) {
            $this->newRecords[spl_object_hash($record)] = $record;
        }
    }
    public function markAsChanged(RecordAbstract $record)
    {
        if (!isset($this->newRecords[spl_object_hash($record)]) and !isset($this->deletedRecords[spl_object_hash($record)])) {
            $this->changedRecords[spl_object_hash($record)] = $record;
        }
    }
    public function markAsDeleted(RecordAbstract $record)
    {
        if (isset($this->newRecords[spl_object_hash($record)])) {
            unset($this->newRecords[spl_object_hash($record)]);
        }
        if (isset($this->changedRecords[spl_object_hash($record)])) {
            unset($this->changedRecords[spl_object_hash($record)]);
        }
        $this->deletedRecords[spl_object_hash($record)] = $record;
    }
    public function revert(RecordAbstract $record)
    {
        unset($this->newRecords[spl_object_hash($record)]);
        unset($this->changedRecords[spl_object_hash($record)]);
        unset($this->deletedRecords[spl_object_hash($record)]);
    }


    /** @return RecordAbstract[] */
    public function getNewRecords()
    {
        return $this->newRecords;
    }
    /** @return RecordAbstract[] */
    public function getChangedRecords()
    {
        return $this->changedRecords;
    }
    /** @return RecordAbstract[] */
    public function getDeletedRecords()
    {
        return $this->deletedRecords;
    }
    public function clean()
    {
        $this->newRecords     = array();
        $this->changedRecords = array();
        $this->deletedRecords = array();
    }
}
