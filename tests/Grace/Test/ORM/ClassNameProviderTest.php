<?php

namespace Grace\Test\ORM;

use Grace\ORM\ClassNameProvider;

class ClassNameProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClassNameProvider */
    protected $provider;

    public function testDefaultNames()
    {
        $provider = new ClassNameProvider();
        $this->assertEquals('Post', $provider->getBaseClass('\\Model\\Post'));
        $this->assertEquals('\\Model\\Post', $provider->getModelClass('Post'));
        $this->assertEquals('\\Finder\\PostFinder', $provider->getFinderClass('Post'));
        $this->assertEquals('\\Mapper\\PostMapper', $provider->getMapperClass('Post'));
        $this->assertEquals('\\Collection\\PostCollection', $provider->getCollectionClass('Post'));
    }
    public function testCommonNamespace()
    {
        $provider = new ClassNameProvider('Some\\AppBundle');
        $this->assertEquals('Post', $provider->getBaseClass('\\Some\\AppBundle\\Model\\Post'));
        $this->assertEquals('\\Some\\AppBundle\\Model\\Post', $provider->getModelClass('Post'));
        $this->assertEquals('\\Some\\AppBundle\\Finder\\PostFinder', $provider->getFinderClass('Post'));
        $this->assertEquals('\\Some\\AppBundle\\Mapper\\PostMapper', $provider->getMapperClass('Post'));
        $this->assertEquals('\\Some\\AppBundle\\Collection\\PostCollection', $provider->getCollectionClass('Post'));

        $this->assertEquals('\\Some\\AppBundle\\Finder\\Post\\ExtraPostFinder', $provider->getFinderClass('Post\\ExtraPost'));
    }
}
