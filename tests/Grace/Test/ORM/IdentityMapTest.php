<?php

namespace Grace\Test\ORM;

use Grace\ORM\IdentityMap;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var IdentityMap */
    protected $identityMap;

    protected function setUp()
    {
        $this->identityMap = new IdentityMap;
    }
    public function testIssetingUndefinded()
    {
        $this->assertFalse($this->identityMap->issetRecord('Foo', 123));
    }
    public function testGettingUndefinded()
    {
        $this->assertEquals(false, $this->identityMap->getRecord('Foo', 123));
    }
    public function testSetting()
    {
        $record = new \stdClass;
        $this->identityMap->setRecord('Foo', 123, $record);
        $this->assertTrue($this->identityMap->issetRecord('Foo', 123));
        $this->assertEquals($record, $this->identityMap->getRecord('Foo', 123));
    }
    public function testUnsetting()
    {
        $record = new \stdClass;
        $this->identityMap->setRecord('Foo', 123, $record);
        $this->identityMap->unsetRecord('Foo', 123, $record);
        $this->assertFalse($this->identityMap->issetRecord('Foo', 123));
        $this->assertEquals(false, $this->identityMap->getRecord('Foo', 123));
    }
}