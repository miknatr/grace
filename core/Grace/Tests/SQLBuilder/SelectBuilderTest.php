<?php

namespace Grace\Tests\SQLBuilder;

use Grace\DBAL\Mysqli\SqlDialect;
use Grace\SQLBuilder\SelectBuilder;
use Grace\Tests\SQLBuilder\Plug\ExecutableAndResultPlug;

class SelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var SelectBuilder */
    protected $builder;
    /** @var ExecutableAndResultPlug */
    protected $plug;

    protected function setUp()
    {
        $this->plug    = new ExecutableAndResultPlug(new SqlDialect);
        $this->builder = new SelectBuilder('TestTable', $this->plug);
    }
    protected function tearDown()
    {
    }

//    public function testSelectWithoutParams()
//    {
//        $this->builder->execute();
//        $this->assertEquals('SELECT * FROM ?f AS ?f', $this->plug->query);
//        $this->assertEquals(array('TestTable', 'TestTable'), $this->plug->arguments);
//    }
//    public function testCountSelectWithoutParams()
//    {
//        $this->builder->count()->execute();
//        $this->assertEquals('SELECT COUNT(?f) AS ?f FROM ?f AS ?f', $this->plug->query);
//        $this->assertEquals(array('id', 'counter', 'TestTable', 'TestTable'), $this->plug->arguments);
//    }
    public function testSelectAllParams()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder
            ->fields(array('id', 'name'))
            ->group('region')
            ->orderDesc('id')
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
            'SELECT ?f:alias:.?f, ?f:alias:.?f'.
            ' FROM ?f AS ?f' .
            ' WHERE ?f:alias:.?f=?q AND ?f:alias:.?f BETWEEN ?q AND ?q OR ?f:alias:.?f BETWEEN ?q AND ?q' .
            ' AND ( ?f:alias:.?f=?q AND ?f:alias:.?f=?q OR ?f:alias:.?f=?q )' .
            ' AND NOT ( NOT ?f:alias:.?f=?q AND NOT ?f:alias:.?f=?q OR NOT ?f:alias:.?f=?q )' .
            ' GROUP BY ?f:alias:.?f' .
            ' ORDER BY ?f:alias:.?f DESC' .
            ' LIMIT 15 OFFSET 5',
            $this->plug->query
        );

        $this->assertEquals(
            array(
                'id',
                'name',
                'TestTable',
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
                'alias' => 'TestTable'
            ),
            $this->plug->arguments
        );
    }
//    public function testFetchAll()
//    {
//        $this->assertEquals('all', $this->builder->fetchAll());
//    }
//    public function testFetchResult()
//    {
//        $this->assertEquals('result', $this->builder->fetchResult());
//    }
//    public function testFetchColumn()
//    {
//        $this->assertEquals('column', $this->builder->fetchColumn());
//    }
//    public function testFetchOneOrFalse()
//    {
//        $this->assertEquals('one or false', $this->builder->fetchOneOrFalse());
//    }
}
