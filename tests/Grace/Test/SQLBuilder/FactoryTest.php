<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\Factory;
use Grace\SQLBuilder\SelectBuilder;
use Grace\SQLBuilder\CreateBuilder;
use Grace\SQLBuilder\AlterBuilder;
use Grace\SQLBuilder\InsertBuilder;
use Grace\SQLBuilder\UpdateBuilder;
use Grace\SQLBuilder\DeleteBuilder;

class FactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var Factory */
    protected $builder;

    protected function setUp() {
        $this->builder = new Factory(new ExecutablePlug);
    }
    protected function tearDown() {
        
    }
    public function testExecuteExcepcion() {
        $this->setExpectedException('\BadMethodCallException');
        $this->builder->execute();
    }
    public function testCreateFactory() {
        $r = $this->builder->create('Test');
        $this->assertTrue($r instanceof CreateBuilder);
    }
    public function testAlterFactory() {
        $r = $this->builder->alter('Test');
        $this->assertTrue($r instanceof AlterBuilder);
    }
    public function testSelectFactory() {
        $r = $this->builder->select('Test');
        $this->assertTrue($r instanceof SelectBuilder);
    }
    public function testInsertFactory() {
        $r = $this->builder->insert('Test');
        $this->assertTrue($r instanceof InsertBuilder);
    }
    public function testUpdateFactory() {
        $r = $this->builder->update('Test');
        $this->assertTrue($r instanceof UpdateBuilder);
    }
    public function testDeleteFactory() {
        $r = $this->builder->delete('Test');
        $this->assertTrue($r instanceof DeleteBuilder);
    }
}
