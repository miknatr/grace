<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\SqlValue;
use Grace\SQLBuilder\UpdateBuilder;

class UpdateBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var UpdateBuilder */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutablePlug;
        $this->builder = new UpdateBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }
    public function testUpdateWithoutParams()
    {
        $this->setExpectedException('Grace\SQLBuilder\ExceptionCallOrder');
        $this->builder->execute();
    }
    public function testUpdateWithoutWhereStatement()
    {
        $this->builder
            ->values(array(
                          'id'    => 123,
                          'name'  => 'Mike',
                          'phone' => '123-123',
                          'point' => new SqlValue('POINT(?q, ?q)', array(1, 2)),
                     ))
            ->execute();

        $this->assertEquals('UPDATE `TestTable` SET' . ' `id`=?q, `name`=?q, `phone`=?q, `point`=POINT(?q, ?q)', $this->plug->query);
        $this->assertEquals(array(123, 'Mike', '123-123', 1, 2), $this->plug->arguments);
    }
    public function testUpdateWithWhereStatement()
    {
        $this->builder
            ->values(array(
                          'id'    => 123,
                          'name'  => 'Mike',
                          'phone' => '123-123',
                     ))
            ->eq('isPublished', 1) //test with AbstractWhereBuilder
            ->between('category', 10, 20) //test with AbstractWhereBuilder
            ->execute();

        $this->assertEquals('UPDATE `TestTable` SET' . ' `id`=?q, `name`=?q, `phone`=?q' .
                                ' WHERE isPublished=?q AND category BETWEEN ?q AND ?q', $this->plug->query);
        $this->assertEquals(array(123, 'Mike', '123-123', 1, 10, 20), $this->plug->arguments);
    }
}