<?php
namespace Grace\CRUD;

class MemcacheProxy implements CRUDInterface {
    private $memcache;
    private $subject;
    public function __construct(array $config, CRUDInterface $subject) {
        //TODO DODODO
        $this->memcache = '';
        $this->subject = $subject;
    }
    public function selectById($table, $id) {
    }
    public function insertById($table, $id, array $values) {
        $values['id'] = $id;
    }
    public function updateById($table, $id, array $values) {
    }
    public function deleteById($table, $id) {
    }
}

