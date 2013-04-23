<?php

namespace Grace\Tests\ORM\Service;

use Grace\ORM\Service\ClassNameProvider;

class ClassNameProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClassNameProvider */
    protected $provider;

    public function testCommonNamespace()
    {
        $provider = new ClassNameProvider('Grace\\Tests\\ORM\\Plug');

        $this->assertEquals('TaxiPassenger', $provider->getBaseClass('TaxiPassenger'));
        $this->assertEquals('TaxiPassenger', $provider->getBaseClass('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('TaxiPassenger', $provider->getBaseClass('Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('TaxiPassenger', $provider->getBaseClass('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));
        $this->assertEquals('TaxiPassenger', $provider->getBaseClass('Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));

        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger', $provider->getModelClass('TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger', $provider->getModelClass('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger', $provider->getModelClass('Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger', $provider->getModelClass('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger', $provider->getModelClass('Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));

        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder', $provider->getFinderClass('TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder', $provider->getFinderClass('\\Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder', $provider->getFinderClass('Grace\\Tests\\ORM\\Plug\\Model\\TaxiPassenger'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder', $provider->getFinderClass('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));
        $this->assertEquals('\\Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder', $provider->getFinderClass('Grace\\Tests\\ORM\\Plug\\Finder\\TaxiPassengerFinder'));
    }
}
