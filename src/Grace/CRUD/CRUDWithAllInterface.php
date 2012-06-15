<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\CRUD;

/**
 * Extends CRUD interface gets all data
 * @author Mikhail Natrov <miknatr@gmail.com>
 */
interface CRUDWithAllInterface extends CRUDInterface
{
    /**
     * Fetches all entities
     * @abstract
     * @param string $table table name
     * @return array returns  of rows
     */
    public function selectAll($table);
}