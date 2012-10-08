<?php

namespace Grace\Test\ORM;

use Grace\ORM\UnitOfWork;
use Grace\ORM\IdentityMap;
use Grace\DBAL\MysqliConnection;
use Grace\CRUD\DBMasterDriver;
use Grace\ORM\ExceptionNoResult;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var RealManager */
    protected $manager;
    protected $dispatcher;
    /** @var MysqliConnection */
    protected $connection;
    /** @var DBMasterDriver */
    protected $crud;

    protected function setUp()
    {
        $this->establishConnection();

        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        $this->connection->execute('CREATE TABLE `Order` (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255), phone VARCHAR(255))');
        $this->connection->execute('INSERT INTO `Order` VALUES (1, "Mike", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (2, "John", "1234567")');
        $this->connection->execute('INSERT INTO `Order` VALUES (3, "Bill", "1234567")');
    }
    protected function establishConnection()
    {
        $this->dispatcher = new \stdClass();
        $this->connection =
            new MysqliConnection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);
        $this->crud       = new DBMasterDriver($this->connection);

        $this->manager = new RealManager();
        $this->manager
            ->setClassNameProvider(new RealClassNameProvider())
            ->setSqlReadOnlyConnection($this->connection)
            ->setCrudConnection($this->crud);
        $this->manager->clean();
    }
    protected function tearDown()
    {
        $this->connection->execute('DROP TABLE IF EXISTS `Order`');
        unset($this->connection);
    }
    public function testFinder()
    {
        $finder = $this->manager->getOrderFinder();
        $this->assertTrue($finder instanceof OrderFinder);

        $finder2 = $this->manager->getOrderFinder();
        $this->assertEquals(spl_object_hash($finder), spl_object_hash($finder2));
    }
    public function testCommit()
    {
        //test insert
        $inserted = $this->manager->getOrderFinder()->create();
        $inserted->setName('Arnold')->setPhone('+1-123-123');

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
            $this->manager
                ->getOrderFinder()
                ->getById(3)
                ->getName();
            $this->fail('Row was deleted');
        } catch (ExceptionNoResult $e) {
            $this->assertTrue(true);
        }
    }
}