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
 * Converter from db row to record array and the other way around
 */
interface MapperInterface
{
    /**
     * Converts from db row to record array
     * @abstract
     * @param array $row
     * @return mixed
     */
    public function convertDbRowToRecordArray(array $row);
    /**
     * Converts from record array to db row
     * @abstract
     * @param array $recordArray
     * @return mixed
     */
    public function convertRecordArrayToDbRow(array $recordArray);
    /**
     * Gets differs between record and defaults
     * @abstract
     * @param array $recordArray
     * @param array $defaults
     * @return mixed
     */
    public function getRecordChanges(array $recordArray, array $defaults);
}
