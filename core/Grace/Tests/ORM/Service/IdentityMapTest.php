<?php

namespace Grace\Tests\ORM\Service;

use Grace\ORM\Service\IdentityMap;

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
        $this->assertFalse($this->identityMap->issetModel('Foo', 123));
    }
    public function testGettingUndefinded()
    {
        $this->assertEquals(false, $this->identityMap->getModel('Foo', 123));
    }
    public function testSetting()
    {
        $model = new \stdClass;
        $this->identityMap->setModel('Foo', 123, $model);
        $this->assertTrue($this->identityMap->issetModel('Foo', 123));
        $this->assertEquals($model, $this->identityMap->getModel('Foo', 123));
    }
    public function testUnsetting()
    {
        $record = new \stdClass;
        $this->identityMap->setModel('Foo', 123, $record);
        $this->identityMap->unsetModel('Foo', 123, $record);
        $this->assertFalse($this->identityMap->issetModel('Foo', 123));
        $this->assertEquals(false, $this->identityMap->getModel('Foo', 123));
    }
    public function testClean()
    {
        $this->identityMap->setModel('Foo', 123, new \stdClass);
        $this->identityMap->setModel('Foo', 234, new \stdClass);
        $this->identityMap->clean();
        $this->assertFalse($this->identityMap->issetModel('Foo', 123));
        $this->assertFalse($this->identityMap->issetModel('Foo', 234));
    }
}
