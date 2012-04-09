<?php

namespace Grace\Test\ORM;

use Grace\ORM\EventDispatcher;
use Grace\ORM\UnitOfWork;
use Grace\ORM\IdentityMap;
use Grace\DBAL\MysqliConnection;

class FinderTest extends \PHPUnit_Framework_TestCase {
    /** @var OrderFinder */
    protected $finder;
    /** @var EventDispatcher */
    protected $dispatcher;
    /** @var UnitOfWork */
    protected $unitOfWork;
    /** @var IdentityMap */
    protected $identityMap;
    /** @var OrderMapper */
    protected $mapper;
    /** @var MysqliConnection */
    protected $connection;

    protected function setUp() {
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
        $this->mapper = new OrderMapper;
        $this->finder = new OrderFinder($this->dispatcher, $this->unitOfWork,
            $this->identityMap, $this->connection, $this->mapper, 'Order',
            'Grace\Test\ORM\Order', 'Grace\Test\ORM\OrderCollection');
        
        
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
}
