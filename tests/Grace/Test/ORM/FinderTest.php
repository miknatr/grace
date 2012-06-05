<?php

namespace Grace\Test\ORM;

use Grace\ORM\UnitOfWork;
use Grace\ORM\IdentityMap;
use Grace\ORM\ServiceContainer;
use Grace\DBAL\MysqliConnection;
use Grace\CRUD\DBMasterDriver;

class FinderTest extends \PHPUnit_Framework_TestCase
{
    /** @var RealManager */
    protected $orm;
    /** @var OrderFinder */
    protected $finder;
    /** @var ServiceContainer */
    protected $container;
    /** @var IdentityMap */
    protected $identityMap;
    /** @var OrderMapper */
    protected $mapper;
    /** @var MysqliConnection */
    protected $connection;
    /** @var DBMasterDriver */
    protected $crud;

    protected function setUp()
    {
        $this->orm = new RealManager();
        $this->container = new ServiceContainer();
        $this->orm->setContainer($this->container);
        $this->connection  =
            new MysqliConnection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);
        $this->crud        = new DBMasterDriver($this->connection);
        $this->identityMap = new IdentityMap;
        $this->mapper      = new OrderMapper;

        $this->finder      =
            new OrderFinder($this->identityMap, $this->mapper, 'Order', 'Grace\Test\ORM\Order', 'Grace\Test\ORM\OrderCollection');
        $this->finder->setCrud($this->crud);
        $this->finder->setSqlReadOnly($this->connection);


        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        $this->connection->execute('CREATE TABLE `Order` (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), phone VARCHAR(255))');
        $this->connection->execute('INSERT INTO `Order` VALUES (1, "Mike", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (2, "John", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (3, "Bill", "1234567")');
    }
    protected function tearDown()
    {
        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        unset($this->connection);
    }
    public function testGetContainer()
    {
        $this->assertEquals($this->container, $this->finder->getContainerPublic());
    }
    public function testCreate()
    {
        $r = $this->finder->create();
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(4, $r->getId());

        $r = $this->finder->create();
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(5, $r->getId());
    }
    public function testGetById()
    {
        $r = $this->finder->getById(3);
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(3, $r->getId());
        $this->assertEquals('Bill', $r->getName());

        //Testing Identity Map work
        $r2 = $this->finder->getById(3);
        $this->assertEquals(spl_object_hash($r), spl_object_hash($r2));
    }
    public function testGetByIdWithBadId()
    {
        $this->setExpectedException('Grace\ORM\ExceptionNoResult');
        $r = $this->finder->getById(123123);
    }
    public function testFetchColumn()
    {
        $r = $this->finder->getNameColumn();
        $this->assertEquals(array('Mike', 'John', 'Bill'), $r);
    }
    public function testFetchAll()
    {
        $r = $this->finder->getAllRecords();
        $this->assertEquals(3, count($r));
        $this->assertEquals('Mike', $r
            ->getIterator()
            ->current()
            ->getName());
    }
}
