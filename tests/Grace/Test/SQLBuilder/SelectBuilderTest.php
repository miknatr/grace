<?php

namespace Grace\Test\SQLBuilder;

use Grace\SQLBuilder\Factory;
use Grace\SQLBuilder\SelectBuilder;

class SelectBuilderTest extends \PHPUnit_Framework_TestCase {
    /** @var SelectBuilder */
    protected $builder;
    /** @var ExecutablePlug */
    protected $plug;

    protected function setUp() {
        $this->plug = new ExecutablePlug;
        $this->builder = new SelectBuilder('TestTable', $this->plug);
    }
    protected function tearDown() {
        
    }

    public function testSelectWithoutParams() {
        $this->builder->execute();
        print_r($this->plug);
        $this->assertEquals('SELECT * FROM `TestTable`', $this->plug->query);
        //$this->assertEquals(array(), $this->plug->arguments);
    }
}
