<?php

namespace Grace\Test\ORM;

use Grace\ORM\EventDispatcher;
use Grace\ORM\UnitOfWork;
use Grace\ORM\IdentityMap;
use Grace\DBAL\MysqliConnection;
use Grace\ORM\ExceptionNotFoundById;

class ManagerTest extends \PHPUnit_Framework_TestCase {
    /** @var RealManager */
    protected $manager;
    /** @var EventDispatcher */
    protected $dispatcher;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var IdentityMap */
    protected $identityMap;
    /** @var MysqliConnection */
    protected $connection;

    protected function setUp() {
        $this->establishConnection();

        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        $this->connection->execute('CREATE TABLE `Order` (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), phone VARCHAR(255))');
        $this->connection->execute('INSERT INTO `Order` VALUES (1, "Mike", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (2, "John", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (3, "Bill", "1234567")');
    }
    protected function establishConnection() {
        $this->connection = new MysqliConnection(array(
                'host' => TEST_MYSQLI_HOST,
                'port' => TEST_MYSQLI_PORT,
                'user' => TEST_MYSQLI_NAME,
                'password' => TEST_MYSQLI_PASSWORD,
                'database' => TEST_MYSQLI_DATABASE,
            ));
        $this->dispatcher = new EventDispatcher;
        $this->unitOfWork = new UnitOfWork;
        $this->identityMap = new IdentityMap;

        $this->manager = new RealManager('Grace\Test\ORM', $this->dispatcher,
                $this->identityMap, $this->unitOfWork, $this->connection, $this->connection);
    }
    protected function tearDown() {
        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        unset($this->connection);
    }
    public function testFinder() {
        $finder = $this->manager->getOrderFinder();
        $this->assertTrue($finder instanceof OrderFinder);

        $finder2 = $this->manager->getOrderFinder();
        $this->assertEquals(spl_object_hash($finder), spl_object_hash($finder2));
    }
    public function testCommit() {
        //test insert
        $inserted = $this->manager->getOrderFinder()->create();
        $inserted
            ->setName('Arnold')
            ->setPhone('+1-123-123');
        
        //test delete
        $this->manager->getOrderFinder()->getById(3)->delete();
        
        //test update
        $this->manager->getOrderFinder()->getById(2)->setName('Jack');
        
        $this->manager->commit();
        
        
        //clean objects and see changes
        $this->establishConnection();
        $this->assertEquals('Mike', $this->manager->getOrderFinder()->getById(1)->getName());
        $this->assertEquals('Jack', $this->manager->getOrderFinder()->getById(2)->getName());
        $this->assertEquals('Arnold', $this->manager->getOrderFinder()->getById(4)->getName());
        
        try {
            $this->manager->getOrderFinder()->getById(3)->getName();
            $this->fail('Row was deleted');
        } catch (ExceptionNotFoundById $e) {
            $this->assertTrue(true);
        }
    }
}