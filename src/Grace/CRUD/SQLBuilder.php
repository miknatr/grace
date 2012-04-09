<?php
namespace Grace\CRUD;

use Grace\SQLBuilder\Factory;

class SQLBuilder implements CRUDInterface {
    private $sqlBuilder;
    public function __construct(Factory $sqlBuilder) {
        $this->sqlBuilder = $sqlBuilder;
    }
    public function selectById($table, $id) {
        return $this->sqlBuilder->select($table)->eq('id', $id)->fetchOne();
    }
    public function insertById($table, $id, array $values) {
        $values['id'] = $id;
        return $this->sqlBuilder->insert($table)->values($values)->eq('id', $id)->execute();        
    }
    public function updateById($table, $id, array $values) {
        return $this->sqlBuilder->update($table)->values($values)->eq('id', $id)->execute();        
    }
    public function deleteById($table, $id) {
        return $this->sqlBuilder->delete($table)->eq('id', $id)->execute();        
    }
}

