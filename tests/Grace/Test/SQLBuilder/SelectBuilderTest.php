<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\SelectBuilder;

class SelectBuilderTest extends \PHPUnit_Framework_TestCase {
    /** @var SelectBuilder */
    protected $builder;
    /** @var ExecutableAndResultPlug */
    protected $plug;

    protected function setUp() {
        $this->plug = new ExecutableAndResultPlug;
        $this->builder = new SelectBuilder('TestTable', $this->plug);
    }
    protected function tearDown() {
    }

    public function testSelectWithoutParams() {
        $this->builder->execute();
        $this->assertEquals('SELECT * FROM `TestTable`', $this->plug->query);
        $this->assertEquals(array(), $this->plug->arguments);
    }
    public function testCountSelectWithoutParams() {
        $this->builder->count()->execute();
        $this->assertEquals('SELECT COUNT(id) FROM `TestTable`', $this->plug->query);
        $this->assertEquals(array(), $this->plug->arguments);
    }
    public function testSelectAllParams() {
        $this->builder
            ->fields('id, name')
            ->join('Test2Table', 'test2Id', 'id')
            ->group('region')
            ->having('region > 123')
            ->order('id DESC')
            ->limit(5, 15)
            ->eq('isPublished', 1)            //test with AbstractWhereBuilder
            ->between('category', 10, 20)     //test with AbstractWhereBuilder
            ->execute();
        $this->assertEquals(
            'SELECT id, name FROM `TestTable`'
            . ' JOIN `Test2Table` ON `TestTable`.`test2Id`=`Test2Table`.`id`'
            . ' WHERE isPublished=?q AND category BETWEEN ?q AND ?q'
            . ' GROUP BY region'
            . ' HAVING region > 123'
            . ' ORDER BY id DESC'
            . ' LIMIT 5,15', $this->plug->query);
        $this->assertEquals(array(1, 10, 20), $this->plug->arguments);
    }
    public function testFetchAll() {
        $this->assertEquals('all', $this->builder->fetchAll());
    }
    public function testFetchResult() {
        $this->assertEquals('result', $this->builder->fetchResult());
    }
    public function testFetchColumn() {
        $this->assertEquals('column', $this->builder->fetchColumn());
    }
    public function testFetchOne() {
        $this->assertEquals('one', $this->builder->fetchOne());
    }
    
}
