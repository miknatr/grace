<?php

namespace Grace\Test\ORM;

use Grace\ORM\UnitOfWork;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase {
    /** @var UnitOfWork */
    protected $unitOfWork;

    protected function setUp() {
        $this->unitOfWork = new UnitOfWork;
    }
    public function testEmptyReturn() {
        $this->assertEquals(array(), $this->unitOfWork->getNewRecords());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecords());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecords());
    }
    public function testNewMarkers() {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsNew($record1)
            ->markAsNew($record2)
            ->markAsNew($record3);
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getNewRecords()));
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecords());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecords());
    }
    public function testChangedMarkers() {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsChanged($record1)
            ->markAsChanged($record2)
            ->markAsChanged($record3);
        $this->assertEquals(array(), $this->unitOfWork->getNewRecords());
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getChangedRecords()));
        $this->assertEquals(array(), $this->unitOfWork->getDeletedRecords());
    }
    public function testDeleteMarkers() {
        $record1 = new \stdClass;
        $record2 = new \stdClass;
        $record3 = $record2;
        $this->unitOfWork
            ->markAsDeleted($record1)
            ->markAsDeleted($record2)
            ->markAsDeleted($record3);
        $this->assertEquals(array(), $this->unitOfWork->getNewRecords());
        $this->assertEquals(array(), $this->unitOfWork->getChangedRecords());
        $this->assertEquals(array($record1, $record2), array_values($this->unitOfWork->getDeletedRecords()));
    }
}