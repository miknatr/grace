<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\ORM;

class UnitOfWork
{

    private $newRecords = array();
    private $changedRecords = array();
    private $deletedRecords = array();

    public function markAsNew($record)
    {
        $this->newRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsChanged($record)
    {
        $this->changedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function markAsDeleted($record)
    {
        $this->deletedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    public function getNewRecords()
    {
        return $this->newRecords;
    }
    public function getChangedRecords()
    {
        return $this->changedRecords;
    }
    public function getDeletedRecords()
    {
        return $this->deletedRecords;
    }
}
