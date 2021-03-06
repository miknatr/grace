<?php

namespace Grace\Tests\ORM;

use Grace\Cache\CacheInterface;
use Grace\DBAL\ConnectionAbstract\ConnectionInterface;
use Grace\ORM\Grace;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\Config\Loader;
use Grace\ORM\Service\ModelObserver;
use Grace\ORM\Service\TypeConverter;
use Grace\Tests\ORM\Plug\Model\TaxiPassenger;

class ModelAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var Grace */
    protected $orm;
    /** @var TaxiPassenger */
    protected $taxiPassenger;

    protected function setUp()
    {
        /** @var $db ConnectionInterface */
        $db = $this->getMock('\\Grace\\DBAL\\ConnectionAbstract\\ConnectionInterface');
        /** @var $cache CacheInterface */
        $cache = $this->getMock('\\Grace\\Cache\\CacheInterface');

        $typeConverter = new TypeConverter();
        $this->orm = new Grace(
            $db,
            new ClassNameProvider('Grace\\Tests\\ORM\\Plug'),
            new ModelObserver(),
            $typeConverter,
            (new Loader(__DIR__ . '/Resources/models', $typeConverter))->getConfig(),
            $cache
        );

        $this->taxiPassenger = new TaxiPassenger(
            null,
            array(
                'id'    => 123,
                'name'  => 'Mike',
                'phone' => '+79991234567',
            ),
            'TaxiPassenger',
            $this->orm
        );
    }
    public function testGettingIdAndProperties()
    {
        $this->assertEquals(123, $this->taxiPassenger->id);
        $this->assertEquals('Mike', $this->taxiPassenger->getName());
        $this->assertEquals('+79991234567', $this->taxiPassenger->getPhone());
    }
    public function testSettingProperty()
    {
        $this->taxiPassenger->setName('John')->setPhone('+1234546890');

        $this->assertEquals('John', $this->taxiPassenger->getName());
        $this->assertEquals('+1234546890', $this->taxiPassenger->getPhone());

        $this->assertEquals(array($this->taxiPassenger), array_values($this->orm->unitOfWork->getChangedModels()));

        $modelArray = $this->taxiPassenger->getProperties();
        $this->assertEquals('John', $modelArray['name']);
        $this->assertEquals('+1234546890', $modelArray['phone']);
    }
    public function testSettingPropertyWithReverting()
    {
        $this->taxiPassenger->setName('John')->setPhone('+1234546890');
        $this->taxiPassenger->revert();

        $this->assertEquals('Mike', $this->taxiPassenger->getName());
        $this->assertEquals('+79991234567', $this->taxiPassenger->getPhone());
        $this->assertEquals(array(), $this->orm->unitOfWork->getChangedModels());
    }
    public function testDeleting()
    {
        $this->taxiPassenger->delete();
        $this->assertEquals(array($this->taxiPassenger), array_values($this->orm->unitOfWork->getDeletedModels()));
    }
}
