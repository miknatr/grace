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
 * @inheritdoc
 */
abstract class Mapper implements MapperInterface
{
    protected $fields = array();
    protected $noDbFields = array();

    /**
     * @inheritdoc
     */
    public function convertDbRowToRecordArray(array $row)
    {
        $recordArray = array();
        foreach ($this->fields as $field) {
            if (isset($row[$field])) {
                $recordArray[$field] = $row[$field];
            } else {
                $recordArray[$field] = null;
            }
        }
        foreach ($this->noDbFields as $field) {
            $recordArray[$field] = null;
        }
        return $recordArray;
    }
    /**
     * @inheritdoc
     */
    public function convertRecordArrayToDbRow(array $recordArray)
    {
        $row = array();
        foreach ($this->fields as $field) {
            if (isset($recordArray[$field])) {
                $row[$field] = $recordArray[$field];
            } else {
                $row[$field] = null;
            }
        }
        return $row;
    }
    /**
     * @inheritdoc
     */
    public function getRecordChanges(array $recordArray, array $defaults)
    {
        $changes = array();
        foreach ($this->fields as $field) {
            if (isset($recordArray[$field]) and isset($defaults[$field]) and $recordArray[$field] != $defaults[$field]) {
                $changes[$field] = $recordArray[$field];
            }
        }
        return $changes;
    }
}