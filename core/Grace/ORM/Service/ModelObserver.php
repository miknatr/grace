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

use Grace\ORM\ModelAbstract;

class ModelObserver
{
    public function onBeforeInsert(ModelAbstract $model) {}
    public function onBeforeChange(ModelAbstract $model) {}
    public function onBeforeDelete(ModelAbstract $model) {}

    public function onAfterInsert(ModelAbstract $model) {}
    public function onAfterChange(ModelAbstract $model) {}
    public function onAfterDelete(ModelAbstract $model) {}
}
