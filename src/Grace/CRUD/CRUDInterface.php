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
 * CRUD access interface to DB (cache, master-slave, etc.)
 * Interface provides common access point for operations with entities
 * by unique id. You can combine few classes which implement CRUDInterface
 * to intercept method calls. Example:
 * $masterConnection = new InterfaceConnection(...master host...);
 * $master = new DBMasterDriver($masterConnection);
 * $slaveConnection = new InterfaceConnection(...slave host...);
 * $slave = new DBSlaveDriver($slaveConnection, $masterConnection);
 * $memcacheProxy = new MemcacheProxy(...memcache host..., $slaveConnection);
 * And after that you have following intercept chain:
 *                  $memcacheProxy       $slaveConnection     $masterConnection
 *  selectById      gets from cache      only when cache      ----
 *                  or calls slave       doesn't exist
 *                  and updates cache
 *  insertById      pass to slave        pass to master       insert into db
 *  updateById      clears cache and     pass to master       update db
 *                  pass to slave
 *  deleteById      deletes cache and    pass to master       delete from db
 *                  pass to slave
 * As you can see it is very flexible structure and you can create
 * so many levels as you need.
 * @author Mikhail Natrov <miknatr@gmail.com>
 */
interface CRUDInterface
{
    /**
     * Fetch entity by id
     * @abstract
     * @param string $table table name
     * @param string $id    entity id
     * @return array|false returns row associative array or false if
     */
    public function selectById($table, $id);
    /**
     * Delete entity by id
     * @abstract
     * @param string $table  table name
     * @param string $id     entity id
     * @param array  $values associative array of values
     * @return array|false returns row associative array or false if
     */
    public function insertById($table, $id, array $values);
    /**
     * Delete entity by id
     * @abstract
     * @param string $table  table name
     * @param string $id     entity id
     * @param array  $values associative array of values
     * @return array|false returns row associative array or false if
     */
    public function updateById($table, $id, array $values);
    /**
     * Delete entity by id
     * @abstract
     * @param string $table table name
     * @param string $id    entity id
     * @return array|false returns row associative array or false if
     */
    public function deleteById($table, $id);
}
