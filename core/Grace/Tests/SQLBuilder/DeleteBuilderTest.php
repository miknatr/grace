<?php

namespace Grace\Tests\SQLBuilder;

use Grace\DBAL\Mysqli\SqlDialect;
use Grace\SQLBuilder\DeleteBuilder;
use Grace\Tests\SQLBuilder\Plug\ExecutablePlug;

class DeleteBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var DeleteBuilder */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutablePlug(new SqlDialect);
        $this->builder = new DeleteBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }
    public function testSelectWithoutParams()
    {
        $this->builder->execute();
        $this->assertEquals('DELETE FROM ?f', $this->plug->query);
        $this->assertEquals(array('TestTable', 'alias' => 'TestTable'), $this->plug->arguments);
    }
    public function testSelectAllParams()
    {
        $this->builder
            ->eq('isPublished', 1) //test with AbstractWhereBuilder
            ->between('category', 10, 20) //test with AbstractWhereBuilder
            ->execute();

        $this->assertEquals('DELETE FROM ?f WHERE ?f:alias:.?f=?q AND ?f:alias:.?f BETWEEN ?q AND ?q', $this->plug->query);
        $this->assertEquals(array('TestTable', 'isPublished', 1, 'category', 10, 20, 'alias' => 'TestTable'), $this->plug->arguments);
    }
}
