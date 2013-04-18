<?php

namespace Grace\Test\ORM;

use Grace\ORM\Service\UnitOfWork;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Grace\ORM\Service\UnitOfWork */
    protected $unitOfWork;

    protected function setUp()
    {
        $this->unitOfWork = new \Grace\ORM\Service\UnitOfWork;
    }
    public function testEmptyReturn()
    {
        $this->assertEquals(array(), $this->unitOfWork->getNewRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecordIds());
    }
    public function testNewMarkers()
    {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsNew($record1)
            ->markAsNew($record2)
            ->markAsNew($record3);
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getNewRecordIds()));
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecordIds());
    }
    public function testChangedMarkers()
    {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsChanged($record1)
            ->markAsChanged($record2)
            ->markAsChanged($record3);
        $this->assertEquals(array(), $this->unitOfWork->getNewRecordIds());
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getChangedRecordIds()));
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecordIds());
    }
    public function testDeleteMarkers()
    {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsDeleted($record1)
            ->markAsDeleted($record2)
            ->markAsDeleted($record3);
        $this->assertEquals(array(), $this->unitOfWork->getNewRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecordIds());
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getDeletedRecordIds()));
    }
    public function testRevert()
    {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = new \stdClass;
        $this->unitOfWork
            ->markAsNew($record1)
            ->markAsChanged($record2)
            ->markAsDeleted($record3);
        $this->unitOfWork
            ->revert($record1)
            ->revert($record2)
            ->revert($record3);
        $this->assertEquals(array(), $this->unitOfWork->getNewRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecordIds());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecordIds());
    }
}