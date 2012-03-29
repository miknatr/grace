<?php
namespace Grace\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;
use Grace\DBAL\InterfaceResult;

class Factory {
    private $executable;
    public function __construct(InterfaceExecutable $executable) {
        $this->executable = $executable;
    }
    public function execute() {
        throw new \BadMethodCallException('It is factory class, please use select/insert/update/delete/create methods to get concrete sql builders');
    }
    public function select($table) {
        return new SelectBuilder($table, $this->executable);
    }
    public function insert($table) {
        return new InsertBuilder($table, $this->executable);
    }
    public function update($table) {
        return new UpdateBuilder($table, $this->executable);
    }
    public function delete($table) {
        return new DeleteBuilder($table, $this->executable);
    }
    public function create($table) {
        return new CreateBuilder($table, $this->executable);
    }
    public function alter($table) {
        return new AlterBuilder($table, $this->executable);
    }
}

