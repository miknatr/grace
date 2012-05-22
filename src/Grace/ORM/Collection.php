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
 * Collection of record objects
 * Iterator
 */
abstract class Collection extends \ArrayObject
{
    /**
     * Clears all changes in collection records
     * @return Collection
     */
    public function revert()
    {
        foreach ($this as $record) {
            $record->revert();
        }
        return $this;
    }
    /**
     * Edits all records in collection
     * @param array $fields
     * @return Collection
     */
    public function edit(array $fields)
    {
        foreach ($this as $record) {
            $record->edit($fields);
        }
        return $this;
    }
    /**
     * Marks as delete all records in collection
     * @return Collection
     */
    public function delete()
    {
        foreach ($this as $record) {
            $record->delete();
        }
        return $this;
    }
}