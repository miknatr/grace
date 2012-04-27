<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\InsertBuilder;

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
                     ))
            ->execute();

        $this->assertEquals(
            'INSERT INTO `TestTable`' . ' (`id`, `name`, `phone`)' . ' VALUES (?q, ?q, ?q)', $this->plug->query);
        $this->assertEquals(array(123, 'Mike', '123-123'), $this->plug->arguments);
    }
}