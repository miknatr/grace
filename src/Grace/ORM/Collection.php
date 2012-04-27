<?php

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