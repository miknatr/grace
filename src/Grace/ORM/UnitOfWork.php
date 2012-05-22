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

/**
 * Container of changing markers
 */
class UnitOfWork
{

    private $newRecords = array();
    private $changedRecords = array();
    private $deletedRecords = array();

    /**
     * Mark record as new
     * @param Record $record
     * @return UnitOfWork
     */
    public function markAsNew($record)
    {
        $this->newRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    /**
     * Mark record as changed
     * @param Record $record
     * @return UnitOfWork
     */
    public function markAsChanged($record)
    {
        $this->changedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    /**
     * Mark record as deleted
     * @param Record $record
     * @return UnitOfWork
     */
    public function markAsDeleted($record)
    {
        $this->deletedRecords[spl_object_hash($record)] = $record;
        return $this;
    }
    /**
     * Delete all changes about this record
     * @param Record $record
     * @return UnitOfWork
     */
    public function revert($record)
    {
        unset($this->newRecords[spl_object_hash($record)]);
        unset($this->changedRecords[spl_object_hash($record)]);
        unset($this->deletedRecords[spl_object_hash($record)]);
        return $this;
    }
    /**
     * All new records
     * @return Record[]
     */
    public function getNewRecords()
    {
        return $this->newRecords;
    }
    /**
     * All changed records
     * @return Record[]
     */
    public function getChangedRecords()
    {
        return $this->changedRecords;
    }
    /**
     * All deleted records
     * @return Record[]
     */
    public function getDeletedRecords()
    {
        return $this->deletedRecords;
    }
}
