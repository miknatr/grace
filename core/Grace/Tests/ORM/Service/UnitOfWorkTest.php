<?php

namespace Grace\Tests\ORM\Service;

use Grace\ORM\Service\UnitOfWork;
use Grace\Tests\ORM\Plug\Model\TaxiPassenger;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /** @var UnitOfWork */
    protected $unitOfWork;

    protected function setUp()
    {
        $this->unitOfWork = new UnitOfWork;
    }
    public function testEmptyReturn()
    {
        $this->assertEquals(array(), $this->unitOfWork->getNewModels());
        $this->assertEquals(array(), $this->unitOfWork->getChangedModels());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedModels());
    }
    public function testNewMarkers()
    {
        $model1 = new TaxiPassenger(array('id' => 1));
        $model2 = new TaxiPassenger(array('id' => 2));
        $model3 = $model2;

        $this->unitOfWork->markAsNew($model1);
        $this->unitOfWork->markAsNew($model2);
        $this->unitOfWork->markAsNew($model3);

        $this->assertEquals(array($model1, $model2), array_values($this->unitOfWork->getNewModels()));
        $this->assertEquals(array(), $this->unitOfWork->getChangedModels());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedModels());
    }
    public function testChangedMarkers()
    {
        $model1 = new TaxiPassenger(array('id' => 1));
        $model2 = new TaxiPassenger(array('id' => 2));
        $model3 = $model2;

        $this->unitOfWork->markAsChanged($model1);
        $this->unitOfWork->markAsChanged($model2);
        $this->unitOfWork->markAsChanged($model3);

        $this->assertEquals(array(), $this->unitOfWork->getNewModels());
        $this->assertEquals(array($model1, $model2), array_values($this->unitOfWork->getChangedModels()));
        $this->assertEquals(array(), $this->unitOfWork->getDeletedModels());
    }
    public function testDeleteMarkers()
    {
        $model1 = new TaxiPassenger(array('id' => 1));
        $model2 = new TaxiPassenger(array('id' => 2));
        $model3 = $model2;

        $this->unitOfWork->markAsDeleted($model1);
        $this->unitOfWork->markAsDeleted($model2);
        $this->unitOfWork->markAsDeleted($model3);

        $this->assertEquals(array(), $this->unitOfWork->getNewModels());
        $this->assertEquals(array(), $this->unitOfWork->getChangedModels());
        $this->assertEquals(array($model1, $model2), array_values($this->unitOfWork->getDeletedModels()));
    }
    public function testRevert()
    {
        $model1 = new TaxiPassenger(array('id' => 1));
        $model2 = new TaxiPassenger(array('id' => 2));
        $model3 = $model2;

        $this->unitOfWork->markAsNew($model1);
        $this->unitOfWork->markAsNew($model2);
        $this->unitOfWork->markAsNew($model3);

        $this->unitOfWork->revert($model1);
        $this->unitOfWork->revert($model2);
        $this->unitOfWork->revert($model3);

        $this->assertEquals(array(), $this->unitOfWork->getNewModels());
        $this->assertEquals(array(), $this->unitOfWork->getChangedModels());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedModels());
    }
}
