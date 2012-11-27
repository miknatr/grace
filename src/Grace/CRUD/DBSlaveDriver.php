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

use Grace\DBAL\InterfaceConnection;

/**
 * Slave crud db connection
 * Intercepts selectById call
 * Other calls passes to master crud connection
 */
class DBSlaveDriver implements CRUDInterface
{
    //TODO Do and test all methods below
    private $connection;
    private $master;

    /**
     * @param \Grace\DBAL\InterfaceConnection $slaveConnection
     * @param DBMasterDriver                  $master
     */
    public function __construct(InterfaceConnection $slaveConnection, DBMasterDriver $master)
    {
        $this->connection = $slaveConnection;
        $this->master     = $master;
    }
    /**
     * @inheritdoc
     */
    public function selectById($table, $id)
    {
        try {
            return $this->connection
                ->getSQLBuilder()
                ->select($table)
                ->eq('id', $id)
                ->fetchOne();
        } catch (ExceptionNoResultDB $e) {
            throw new ExceptionNoResult($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function insertById($table, $id, array $values)
    {
        $values['id'] = $id;
        return $this->connection
            ->getSQLBuilder()
            ->insert($table)
            ->values($values)
            ->eq('id', $id)
            ->execute();
    }
    /**
     * @inheritdoc
     */
    public function updateById($table, $id, array $values)
    {
        return $this->connection
            ->getSQLBuilder()
            ->update($table)
            ->values($values)
            ->eq('id', $id)
            ->execute();
    }
    /**
     * @inheritdoc
     */
    public function deleteById($table, $id)
    {
        return $this->connection
            ->getSQLBuilder()
            ->delete($table)
            ->eq('id', $id)
            ->execute();
    }
}

