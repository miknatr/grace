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
 * CRUD interface for connections which supports transactions
 * @author Mikhail Natrov <miknatr@gmail.com>
 */
interface CRUDCommitableInterface
{
    /**
     * Starts transactions
     * @abstract
     */
    public function start();
    /**
     * Commits transactions
     * @abstract
     */
    public function commit();
    /**
     * Rollbacks transactions
     * @abstract
     */
    public function rollback();
}