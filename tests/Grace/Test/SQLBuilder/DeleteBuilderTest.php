<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\DeleteBuilder;

class DeleteBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteBuilder */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutablePlug;
        $this->plug->setSqlEscapeSymbol('`');
        $this->builder = new DeleteBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }
    public function testSelectWithoutParams()
    {
        $this->builder->execute();
        $this->assertEquals('DELETE FROM `TestTable`', $this->plug->query);
        $this->assertEquals(array(), $this->plug->arguments);
    }
    public function testSelectAllParams()
    {
        $this->builder
            ->eq('isPublished', 1) //test with AbstractWhereBuilder
            ->between('category', 10, 20) //test with AbstractWhereBuilder
            ->execute();

        $this->assertEquals(
            'DELETE FROM `TestTable`' . ' WHERE isPublished=?q AND category BETWEEN ?q AND ?q', $this->plug->query);
        $this->assertEquals(array(1, 10, 20), $this->plug->arguments);
    }
}
