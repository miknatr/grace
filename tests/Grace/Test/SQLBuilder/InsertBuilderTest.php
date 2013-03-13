<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\InsertBuilder;
use Grace\SQLBuilder\SqlValue;

class InsertBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var InsertBuilder */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutablePlug;
        $this->builder = new InsertBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }
    public function testInsertWithoutParams()
    {
        $this->setExpectedException('Grace\SQLBuilder\ExceptionCallOrder');
        $this->builder->execute();
    }
    public function testInsert()
    {
        $this->builder
            ->values(array(
                          'id'    => 123,
                          'name'  => 'Mike',
                          'phone' => '123-123',
                          'point' => new SqlValue('POINT(?q, ?q)', array(1, 2)),
                     ))
            ->execute();

        $this->assertEquals(
            'INSERT INTO ?f (?i)' . ' VALUES (?q, ?q, ?q, POINT(?q, ?q))', $this->plug->query);
        $this->assertEquals(array('TestTable', array('id', 'name', 'phone', 'point'), 123, 'Mike', '123-123', 1, 2), $this->plug->arguments);
    }
}