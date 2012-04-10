<?php

namespace Grace\Test\ORM;

use Grace\EventDispatcher\Dispatcher;
use Grace\ORM\UnitOfWork;
use Grace\ORM\IdentityMap;
use Grace\DBAL\MysqliConnection;
use Grace\CRUD\DBMasterDriver;

class FinderTest extends \PHPUnit_Framework_TestCase {
    /** @var OrderFinder */
    protected $finder;
    /** @var Dispatcher */
    protected $dispatcher;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var IdentityMap */
    protected $identityMap;
    /** @var OrderMapper */
    protected $mapper;
    /** @var MysqliConnection */
    protected $connection;
    /** @var DBMasterDriver */
    protected $crud;

    protected function setUp() {
        $this->dispatcher = new Dispatcher;
        $this->connection = new MysqliConnection(array(
                'host' => TEST_MYSQLI_HOST,
                'port' => TEST_MYSQLI_PORT,
                'user' => TEST_MYSQLI_NAME,
                'password' => TEST_MYSQLI_PASSWORD,
                'database' => TEST_MYSQLI_DATABASE,
            ), $this->dispatcher);
        $this->crud = new DBMasterDriver($this->connection);
        $this->unitOfWork = new UnitOfWork;
        $this->identityMap = new IdentityMap;
        $this->mapper = new OrderMapper;
        $this->finder = new OrderFinder($this->dispatcher, $this->unitOfWork,
            $this->identityMap, $this->connection, $this->crud, $this->mapper,
            'Order', 'Grace\Test\ORM\Order', 'Grace\Test\ORM\OrderCollection');
        
        
        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        $this->connection->execute('CREATE TABLE `Order` (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), phone VARCHAR(255))');
        $this->connection->execute('INSERT INTO `Order` VALUES (1, "Mike", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (2, "John", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (3, "Bill", "1234567")');
    }
    protected function tearDown() {
        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        unset($this->connection);
    }
    public function testGetEventDispatcher() {
        $this->assertEquals($this->dispatcher, $this->finder->getEventDispatcherPublic());
    }
    public function testCreate() {
        $r = $this->finder->create();
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(4, $r->getId());
        
        $r = $this->finder->create();
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(5, $r->getId());
    }
    public function testGetById() {
        $r = $this->finder->getById(3);
        $this->assertTrue($r instanceof Order);
        $this->assertEquals(3, $r->getId());
        $this->assertEquals('Bill', $r->getName());
        
        //Testing Identity Map work
        $r2 = $this->finder->getById(3);
        $this->assertEquals(spl_object_hash($r), spl_object_hash($r2));
    }
    public function testGetByIdWithBadId() {
        $this->setExpectedException('Grace\ORM\ExceptionNotFoundById');
        $r = $this->finder->getById(123123);
    }
    public function testFetchColumn() {
        $r = $this->finder->getNameColumn();
        $this->assertEquals(array('Mike', 'John', 'Bill'), $r);
    }
    public function testFetchAll() {
        $r = $this->finder->getAllRecords();
        $this->assertEquals(3, count($r));
        $this->assertEquals('Mike', $r->getIterator()->current()->getName());
    }
}
