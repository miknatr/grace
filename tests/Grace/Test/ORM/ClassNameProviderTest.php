<?php

namespace Grace\Test\ORM;

use Grace\ORM\ClassNameProvider;

class ClassNameProviderTest extends \PHPUnit_Framework_TestCase {
    /** @var ClassNameProvider */
    protected $provider;

    protected function setUp() {
        $this->provider = new ClassNameProvider;
    }
    public function testDefaultNames() {
        $this->assertEquals('Post', $this->provider->getBaseClass('\\Model\\Post'));
        $this->assertEquals('\\Model\\Post', $this->provider->getModelClass('Post'));
        $this->assertEquals('\\Finder\\PostFinder', $this->provider->getFinderClass('Post'));
        $this->assertEquals('\\Mapper\\PostMapper', $this->provider->getMapperClass('Post'));
        $this->assertEquals('\\Collection\\PostCollection', $this->provider->getCollectionClass('Post'));
    }
}
