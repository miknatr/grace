<?php

namespace Grace\CRUD;

use Grace\DBAL\InterfaceConnection;

class DBMasterDriver implements CRUDInterface {
    private $connection;

    public function __construct(InterfaceConnection $connection) {
        $this->connection = $connection;
    }
    public function selectById($table, $id) {
        return $this->connection->getSQLBuilder()
                ->select($table)->eq('id', $id)->fetchOne();
    }
    public function insertById($table, $id, array $values) {
        $values['id'] = $id;
        return $this->connection->getSQLBuilder()
                ->insert($table)->values($values)->execute();
    }
    public function updateById($table, $id, array $values) {
        return $this->connection->getSQLBuilder()
                ->update($table)->values($values)->eq('id', $id)->execute();
    }
    public function deleteById($table, $id) {
        return $this->connection->getSQLBuilder()
                ->delete($table)->eq('id', $id)->execute();
    }
}

