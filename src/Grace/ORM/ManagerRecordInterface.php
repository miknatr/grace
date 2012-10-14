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
 * Record events interface for commit in manager
 */
interface ManagerRecordInterface
{
    /**
     * @abstract
     */
    public function onCommitInsert();
    /**
     * @abstract
     */
    public function onCommitChange();
    /**
     * @abstract
     */
    public function onCommitDelete();
}
