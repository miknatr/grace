<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\SelectBuilder;

class SelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SelectBuilder */
    protected $builder;
    /** @var ExecutableAndResultPlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutableAndResultPlug;
        $this->builder = new SelectBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }

    public function testSelectWithoutParams()
    {
        $this->builder->execute();
        $this->assertEquals('SELECT * FROM ?f', $this->plug->query);
        $this->assertEquals(array('TestTable'), $this->plug->arguments);
    }
    public function testCountSelectWithoutParams()
    {
        $this->builder
            ->count()
            ->execute();
        $this->assertEquals('SELECT COUNT(?f) AS ?f FROM ?f', $this->plug->query);
        $this->assertEquals(array('id', 'counter', 'TestTable'), $this->plug->arguments);
    }
    public function testSelectAllParams()
    {
        $this->builder
            ->fields(array('id', 'name'))
            ->groupByField('region')
            ->orderByField('id', 'DESC')
            ->limit(5, 15)
            ->eq('isPublished', 1) //test with AbstractWhereBuilder
            ->between('category', 10, 20) //test with AbstractWhereBuilder
            ->_or()
            ->between('category', 40, 50) //test with AbstractWhereBuilder
            ->_open()
                ->eq('isPublished', 1)
                ->eq('isPublished', 1)
                ->_or()
                ->eq('isPublished', 1)
            ->_close()
            ->_not()
            ->_open()
                ->_not()
                ->eq('isPublished', 1)
                ->_not()
                ->eq('isPublished', 1)
                ->_or()
                ->_not()
                ->eq('isPublished', 1)
            ->_close()
            ->execute();
        $this->assertEquals(
            'SELECT ?f, ?f FROM ?f' .
                ' WHERE ?f=?q AND ?f BETWEEN ?q AND ?q OR ?f BETWEEN ?q AND ?q' .
                ' AND ( ?f=?q AND ?f=?q OR ?f=?q )' .
                ' AND NOT ( NOT ?f=?q AND NOT ?f=?q OR NOT ?f=?q )' .
                ' GROUP BY ?f' .
                ' ORDER BY ?f DESC' .
                ' LIMIT 5,15', $this->plug->query);

        $this->assertEquals(
            array(
                'id',
                'name',
                'TestTable',
                'isPublished',
                1,
                'category',
                10,
                20,
                'category',
                40,
                50,
                'isPublished',
                1,
                'isPublished',
                1,
                'isPublished',
                1,
                'isPublished',
                1,
                'isPublished',
                1,
                'isPublished',
                1,
                'region',
                'id',
            ),
            $this->plug->arguments
        );
    }
    public function testFetchAll()
    {
        $this->assertEquals('all', $this->builder->fetchAll());
    }
    public function testFetchResult()
    {
        $this->assertEquals('result', $this->builder->fetchResult());
    }
    public function testFetchColumn()
    {
        $this->assertEquals('column', $this->builder->fetchColumn());
    }
    public function testFetchOne()
    {
        $this->assertEquals('one', $this->builder->fetchOne());
    }
}
