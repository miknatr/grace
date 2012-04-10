<?php

namespace Grace\Test\DBAL;

use Grace\EventDispatcher\Dispatcher;
use Grace\DBAL\MysqliConnection;
use Grace\DBAL\ExceptionConnection;
use Grace\DBAL\ExceptionQuery;

class MysqliConnectionTest extends AbstractConnectionTest {
    /** @var MysqliConnection */
    protected $connection;

    protected function setUp() {
        $this->connection = new MysqliConnection(array(
                'host' => TEST_MYSQLI_HOST,
                'port' => TEST_MYSQLI_PORT,
                'user' => TEST_MYSQLI_NAME,
                'password' => TEST_MYSQLI_PASSWORD,
                'database' => TEST_MYSQLI_DATABASE,
            ), new Dispatcher);
    }
    protected function tearDown() {
        unset($this->connection);
    }
    public function testBadConnectionConfig() {
        unset($this->connection);
        $this->setExpectedException('Grace\DBAL\ExceptionConnection');
        $this->connection = new MysqliConnection(array(
                'host' => 'locahost', //real server - immediately bad response
                'port' => 3306,       //if non-exists server - we'll wait timeout
                'user' => 'not exists',
                'password' => 'not exists',
                'database' => 'not exists',
            ), new Dispatcher);
        //Lazy conniction, only if we really use database
        $r = $this->connection->execute('SELECT 1');
    }
}
