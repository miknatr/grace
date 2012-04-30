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

interface MapperInterface
{
    public function convertDbRowToRecordArray(array $row);
    public function convertRecordArrayToDbRow(array $recordArray);
    public function getRecordChanges(array $recordArray, array $defaults);
}
