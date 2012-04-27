<?php

namespace Grace\Test\DBAL;

use Grace\EventDispatcher\Dispatcher;
use Grace\DBAL\MysqliConnection;
use Grace\DBAL\ExceptionConnection;
use Grace\DBAL\ExceptionQuery;

class MysqliConnectionTest extends AbstractConnectionTest
{
    /** @var MysqliConnection */
    protected $connection;

    protected function setUp()
    {
        $this->connection =
            new MysqliConnection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, TEST_MYSQLI_NAME, TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);
    }
    protected function tearDown()
    {
        unset($this->connection);
    }
    public function testBadConnectionConfig()
    {
        unset($this->connection);
        $this->setExpectedException('Grace\DBAL\ExceptionConnection');
        $this->connection =
            new MysqliConnection(TEST_MYSQLI_HOST, TEST_MYSQLI_PORT, 'SOME BAD NAME', TEST_MYSQLI_PASSWORD, TEST_MYSQLI_DATABASE);
        //Lazy conniction, only if we really use database
        $r = $this->connection->execute('SELECT 1');
    }
}
