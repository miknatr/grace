<?php

namespace Grace\Test\ORM;

use Grace\ORM\UnitOfWork;
use Grace\ORM\ServiceContainer;

class RecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var RealManager */
    protected $orm;
    /** @var ServiceContainer */
    protected $container;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var Order */
    protected $order;

    protected function setUp()
    {
        $this->orm = new RealManager();
        $this->container = new ServiceContainer();
        $this->unitOfWork = new UnitOfWork;
        $fields           = array(
            'name'  => 'Mike',
            'phone' => '+79991234567',
        );
        $this->order      = new Order($this->orm, $this->container, $this->unitOfWork, 123, $fields, false);
    }
    public function testGettingContainer()
    {
        $this->assertEquals($this->container, $this->order->getContainerPublic());
    }
    public function testGettingIdAndFields()
    {
        $this->assertEquals(123, $this->order->getId());
        $this->assertEquals('Mike', $this->order->getName());
        $this->assertEquals('+79991234567', $this->order->getPhone());
    }
    public function testSettingFieldWithReverting()
    {
        $this->order
            ->setName('John')
            ->setPhone('+1234546890');
        $this->order->revert();
        $this->assertEquals('John', $this->order->getName());
        $this->assertEquals('+1234546890', $this->order->getPhone());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecords());
    }
    public function testDeleting()
    {
        $this->order->delete();
        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getDeletedRecords()));
    }
    protected function checkAssertsAfterSetters()
    {
        $this->assertEquals('John', $this->order->getName());
        $this->assertEquals('+1234546890', $this->order->getPhone());

        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getChangedRecords()));

        $defaults = $this->order->getDefaultFields();
        $this->assertEquals('Mike', $defaults['name']);
        $this->assertEquals('+79991234567', $defaults['phone']);

        $recordArray = $this->order->asArray();
        $this->assertEquals('John', $recordArray['name']);
        $this->assertEquals('+1234546890', $recordArray['phone']);
    }
    public function testSettingFieldWithSaving()
    {
        $this->order
            ->setName('John')
            ->setPhone('+1234546890');
        $this->checkAssertsAfterSetters();
    }
    public function testSettingFieldWithSavingViaEdit()
    {
        $this->order
            ->edit(array(
                        'name'  => 'John',
                        'phone' => '+1234546890',
                   ));
        $this->checkAssertsAfterSetters();
    }
    public function testCreateNewRecord()
    {
        $fields      = array(
            'name'  => 'Mike',
            'phone' => '+79991234567',
        );
        $this->order = new Order($this->orm, $this->container, $this->unitOfWork, 123, $fields, true);
        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getNewRecords()));
    }
}
