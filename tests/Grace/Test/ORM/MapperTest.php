<?php

namespace Grace\Test\ORM;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderMapper */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new OrderMapper;
    }
    public function testConvertDbRowToRecordArray()
    {
        $row = array(
            'id'               => '123',
            'name'             => 'Mike',
            'someDbOnlyField'  => 'some value',
            'someDbOnlyField2' => 'some value',
        );
        $r   = $this->mapper->convertDbRowToRecordArray($row);
        $this->assertEquals(array(
                                 'id'    => '123',
                                 'name'  => 'Mike',
                                 'phone' => null,
                            ), $r);
    }
    public function testConvertRecordArrayToDbRow()
    {
        $record = array(
            'id'                   => '123',
            'name'                 => 'Mike',
            'someObjectOnlyField'  => 'some value',
            'someObjectOnlyField2' => 'some value',
        );
        $r      = $this->mapper->convertRecordArrayToDbRow($record);
        $this->assertEquals(array(
                                 'id'    => '123',
                                 'name'  => 'Mike',
                                 'phone' => null,
                            ), $r);
    }
    public function testGetRecordChanges()
    {
        $defaults = array(
            'id'                   => '123',
            'name'                 => 'Mike',
            'phone'                => '1234567',
            'someObjectOnlyField2' => 'some value',
        );
        $record   = array(
            'id'                   => '123',
            'name'                 => 'John',
            'phone'                => '1234567',
            'someObjectOnlyField2' => 'some other value',
        );
        $r        = $this->mapper->getRecordChanges($record, $defaults);
        $this->assertEquals(array(
                                 'name' => 'John',
                            ), $r);
    }
}
