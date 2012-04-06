<?php

namespace Grace\Test\ORM;

use Grace\ORM\EventDispatcher;
use Grace\ORM\UnitOfWork;

class RecordTest extends \PHPUnit_Framework_TestCase {
    /** @var EventDispatcher */
    protected $dispatcher;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var Order */
    protected $order;

    protected function setUp() {
        $this->dispatcher = new EventDispatcher;
        $this->unitOfWork = new UnitOfWork;
        $fields = array(
            'name' => 'Mike',
            'phone' => '+79991234567',
        );
        $this->order = new Order($this->dispatcher, $this->unitOfWork, 123, $fields);
    }
    public function testGettingEventDispatcher() {
        $this->assertEquals($this->dispatcher, $this->order->getEventDispatcherPublic());
    }
    public function testGettingIdAndFields() {
        $this->assertEquals(123, $this->order->getId());
        $this->assertEquals('Mike', $this->order->getName());
        $this->assertEquals('+79991234567', $this->order->getPhone());
    }
    public function testSettingFieldWithoutSaving() {
        $this->order
            ->setName('John')
            ->setPhone('+1234546890');
        $this->assertEquals('John', $this->order->getName());
        $this->assertEquals('+1234546890', $this->order->getPhone());
        $this->assertEquals(array(), $this->unitOfWork->getChandedRecords());
    }
    public function testDeleting() {
        $this->order->delete();
        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getDeletedRecords()));
    }
    protected function checkAssertsAfterSetters() {
        $this->assertEquals('John', $this->order->getName());
        $this->assertEquals('+1234546890', $this->order->getPhone());
        
        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getChandedRecords()));
        
        $defaults = $this->order->getDefaultFields();
        $this->assertEquals('Mike', $defaults['name']);
        $this->assertEquals('+79991234567', $defaults['phone']);
        
        print_r($this->order->asArray());
    }
    public function testSettingFieldWithSaving() {
        $this->order
            ->setName('John')
            ->setPhone('+1234546890')
            ->save();
        $this->checkAssertsAfterSetters();
    }
    public function testSettingFieldWithSavingViaEdit() {
        $this->order
            ->edit(array(
                'name' => 'John',
                'phone' => '+1234546890',
            ))
            ->save();
        $this->checkAssertsAfterSetters();
    }
    public function testCreateNewRecord() {
        $fields = array(
            'name' => 'Mike',
            'phone' => '+79991234567',
        );
        $this->order = new Order($this->dispatcher, $this->unitOfWork, 123, $fields, true);
        $this->assertEquals(array($this->order), array_values($this->unitOfWork->getNewRecords()));
    }
}
