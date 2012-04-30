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

abstract class Collection extends \ArrayObject implements RecordInterface
{
    public function save()
    {
        foreach ($this as $record) {
            $record->save();
        }
        return $this;
    }
    public function edit(array $fields)
    {
        foreach ($this as $record) {
            $record->edit($fields);
        }
        return $this;
    }
    public function delete()
    {
        foreach ($this as $record) {
            $record->delete();
        }
        return $this;
    }
}