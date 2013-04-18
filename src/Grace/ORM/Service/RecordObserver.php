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

use Grace\ORM\ORMManagerAbstract;
use Grace\ORM\RecordAbstract;

class RecordObserver
{
    public function onBeforeInsert(RecordAbstract $record) {}
    public function onBeforeChange(RecordAbstract $record) {}
    public function onBeforeDelete(RecordAbstract $record) {}

    public function onAfterInsert(RecordAbstract $record) {}
    public function onAfterChange(RecordAbstract $record) {}
    public function onAfterDelete(RecordAbstract $record) {}
}
