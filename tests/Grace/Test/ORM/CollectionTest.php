<?php

namespace Grace\Test\ORM;

use Grace\ORM\UnitOfWork;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $dispatcher;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var OrderCollection */
    protected $collection;

    protected function setUp()
    {
        $this->dispatcher = new \stdClass();
        $this->unitOfWork = new UnitOfWork;

        $fields = array(
            'name'  => 'Mike',
            'phone' => '+79991234567',
        );
        $r1     = new Order($this->dispatcher, $this->unitOfWork, 1, $fields, false);

        $fields = array(
            'name'  => 'John',
            'phone' => '+79991234567',
        );
        $r2     = new Order($this->dispatcher, $this->unitOfWork, 2, $fields, false);

        $this->collection = new OrderCollection(array($r1, $r2));
    }
    public function testCollectionAsArray()
    {
        foreach ($this->collection as $record) {
            $this->assertTrue($record instanceof Order);
        }
        $this->assertEquals(2, count($this->collection));
    }
    public function testSettingFieldWithoutSaving()
    {
        $this->collection
            ->setName('Anonymous')
            ->setPhone('nophone');

        $interator = $this->collection->getIterator();

        $r = $interator->current();
        $this->assertEquals('Anonymous', $r->getName());
        $this->assertEquals('nophone', $r->getPhone());

        $interator->next();

        $r = $interator->current();
        $this->assertEquals('Anonymous', $r->getName());
        $this->assertEquals('nophone', $r->getPhone());

        $this->assertEquals(array(), $this->unitOfWork->getChangedRecords());
    }
    public function testDeleting()
    {
        $this->collection->delete();
        $this->assertEquals(2, count($this->unitOfWork->getDeletedRecords()));
    }
    protected function checkAssertsAfterSetters()
    {
        $this->collection
            ->setName('Anonymous')
            ->setPhone('nophone');

        $interator = $this->collection->getIterator();

        $r = $interator->current();
        $this->assertEquals('Anonymous', $r->getName());
        $this->assertEquals('nophone', $r->getPhone());

        $interator->next();

        $r = $interator->current();
        $this->assertEquals('Anonymous', $r->getName());
        $this->assertEquals('nophone', $r->getPhone());

        $this->assertEquals(2, count($this->unitOfWork->getChangedRecords()));
    }
    public function testSettingFieldWithSaving()
    {
        $this->collection
            ->setName('Anonymous')
            ->setPhone('nophone')
            ->save();
        $this->checkAssertsAfterSetters();
    }
    public function testSettingFieldWithSavingViaEdit()
    {
        $this->collection
            ->edit(array(
                        'name'  => 'Anonymous',
                        'phone' => 'nophone',
                   ))
            ->save();
        $this->checkAssertsAfterSetters();
    }
}
