<?php

namespace Grace\CRUD;

use Grace\DBAL\InterfaceConnection;

class DBSlaveDriver implements CRUDInterface
{
    private $connection;
    private $master;

    public function __construct(InterfaceConnection $slaveConnection, DBMasterDriver $master)
    {
        $this->connection = $slaveConnection;
        $this->master     = $master;
    }
    //TODO Do and test all methods below
    public function selectById($table, $id)
    {
        return $this->connection
            ->getSQLBuilder()
            ->select($table)
            ->eq('id', $id)
            ->fetchOne();
    }
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
    public function updateById($table, $id, array $values)
    {
        return $this->connection
            ->getSQLBuilder()
            ->update($table)
            ->values($values)
            ->eq('id', $id)
            ->execute();
    }
    public function deleteById($table, $id)
    {
        return $this->connection
            ->getSQLBuilder()
            ->delete($table)
            ->eq('id', $id)
            ->execute();
    }
}

