<?php

namespace Grace\Test\SQLBuilder;

class AbstractWhereBuilderTest extends \PHPUnit_Framework_TestCase {
    /** @var AbstractWhereBuilderChild */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp() {
        $this->plug = new ExecutablePlug;
        $this->builder = new AbstractWhereBuilderChild('TestTable', $this->plug);
    }
    protected function tearDown() {
        
    }
    public function testWithoutConditions() {
        $this->assertEquals('', $this->builder->getWhereSql());
        $this->assertEquals(array(), $this->builder->getQueryArguments());
    }
    public function testOneCondition() {
        $this->builder->eq('id', 123);
        $this->assertEquals(' WHERE id=?q', $this->builder->getWhereSql());
        $this->assertEquals(array(123), $this->builder->getQueryArguments());
    }
    public function testAllConditions() {
        $this->builder
            ->eq('id', 1)
            ->notEq('id', 2)
            ->gt('id', 3)
            ->gtEq('id', 4)
            ->lt('id', 5)
            ->ltEq('id', 6)
            ->like('name', 'Mike')
            ->notLike('name', 'John')
            ->likeInPart('lastname', 'Li')
            ->notLikeInPart('lastname', 'Fu')
            ->in('category', array(1, 2, 3, 4, 5))
            ->notIn('category', array(6, 7, 8, 9, 0))
            ->between('id', 7, 8)
            ->notBetween('id', 9, 10)
            ;
        $this->assertEquals(' WHERE id=?q AND id!=?q'
            . ' AND id>?q AND id>=?q AND id<?q AND id<=?q'
            . ' AND name LIKE ?q AND name NOT LIKE ?q'
            . ' AND lastname LIKE ?q AND lastname NOT LIKE ?q'
            . ' AND category IN (?q,?q,?q,?q,?q)'
            . ' AND category NOT IN (?q,?q,?q,?q,?q)'
            . ' AND id BETWEEN ?q AND ?q AND id NOT BETWEEN ?q AND ?q'
            , $this->builder->getWhereSql());
        $this->assertEquals(array(
                1, 2, 3, 4, 5, 6, 'Mike', 'John', '%Li%', '%Fu%',
                1, 2, 3, 4, 5, 6, 7, 8, 9, 0,
                7, 8, 9, 10,
            ), $this->builder->getQueryArguments());
    }
}
