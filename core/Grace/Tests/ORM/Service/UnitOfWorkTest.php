<?php

namespace Grace\Tests\ORM\Service;

use Grace\Cache\CacheInterface;
use Grace\DBAL\Mysqli\Connection;
use Grace\ORM\Grace;
use Grace\ORM\Service\ClassNameProvider;
use Grace\ORM\Service\ModelObserver;
use Grace\ORM\Service\TypeConverter;
use Grace\ORM\Service\UnitOfWork;
use Grace\Tests\ORM\Plug\Model\TaxiPassenger;
use Grace\Tests\ORM\Plug\GraceConfigHelper;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var Grace */
    protected $orm;

    protected function setUp()
    {
        /** @var $cache CacheInterface */
        $cache = $this->getMock('\\Grace\\Cache\\CacheInterface');
        $connection = new Connection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);

        $this->orm = new Grace(
            $connection,
            new ClassNameProvider('Grace\\Tests\\ORM\\Plug'),
            new ModelObserver(),
            new TypeConverter(),
            GraceConfigHelper::create(),
            $cache
        );

        $this->unitOfWork = $this->orm->unitOfWork;
    }
    public function testEmptyReturn()
    {
        $this->assertEquals(array(), $this->unitOfWork->getNewModels());
        $this->assertEquals(array(), $this->unitOfWork->getChangedModels());
        $this->assertEquals(array(), $this->unitOfWork->getDeletedModels());
    }
    public function testNewMarkers()
    {
        $model1 = new TaxiPassenger(null, array('id' => 1, 'name' => 'Arnold Schwarzenegger', 'phone' => '555-12-12'), $this->orm);
        $model2 = new TaxiPassenger(null, array('id' => 2, 'name' => 'Sylvester Stallone', 'phone' => '555-12-12'), $this->orm);
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
        $model1 = new TaxiPassenger(null, array('id' => 1, 'name' => 'Arnold Schwarzenegger', 'phone' => '555-12-12'), $this->orm);
        $model2 = new TaxiPassenger(null, array('id' => 2, 'name' => 'Sylvester Stallone', 'phone' => '555-12-12'), $this->orm);
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
        $model1 = new TaxiPassenger(null, array('id' => 1, 'name' => 'Arnold Schwarzenegger', 'phone' => '555-12-12'), $this->orm);
        $model2 = new TaxiPassenger(null, array('id' => 2, 'name' => 'Sylvester Stallone', 'phone' => '555-12-12'), $this->orm);
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
        $model1 = new TaxiPassenger(null, array('id' => 1, 'name' => 'Arnold Schwarzenegger', 'phone' => '555-12-12'), $this->orm);
        $model2 = new TaxiPassenger(null, array('id' => 2, 'name' => 'Sylvester Stallone', 'phone' => '555-12-12'), $this->orm);
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
