<?php

namespace Grace\CRUD;

interface CRUDInterface
{
    public function selectById($table, $id);
    public function insertById($table, $id, array $values);
    public function updateById($table, $id, array $values);
    public function deleteById($table, $id);
}