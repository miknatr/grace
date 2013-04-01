<?php

namespace Grace\Test\DBAL;

use Grace\DBAL\InterfaceConnection;
use Grace\DBAL\InterfaceResult;
use Grace\SQLBuilder\Factory;

abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    abstract public function testBadConnectionConfig();
    abstract public function testFetchingOne();
    abstract public function testFetchingAll();
    abstract public function testFetchingResult();
    abstract public function testFetchingColumn();
    abstract public function testFetchingHash();
    abstract public function testGettingLastInsertIdBeforeConnectionEsbablished();
    abstract public function testGettingLastInsertId();
    abstract public function testGettingAffectedRowsBeforeConnectionEsbablished();
    abstract public function testGettingAffectedRows();
    abstract public function testEscaping();
    abstract public function testReplacingPlaceholders();
    abstract public function testTransactionCommitBeforeAnyQueries();
    abstract public function testTransactionCommit();
    abstract public function testTransactionManualRollback();
    abstract public function testTransactionRollbackOnError();

    /** @var InterfaceConnection */
    protected $connection;

    public function testGettingSQLBuilder()
    {
        $this->assertTrue($this->connection->getSQLBuilder() instanceof Factory);
    }
    public function testEstablishConnection()
    {
        $this->assertTrue($this->connection instanceof InterfaceConnection);
    }
    public function testSuccessfullQueryWithResults()
    {
        $r = $this->connection->execute('SELECT 1');
        $this->assertTrue($r instanceof InterfaceResult);
    }
    public function testSuccessfullQueryWithoutResults()
    {
        $r = $this->connection->execute('DO 1');
        $this->assertTrue($r);
    }
    public function testFailQuery()
    {
        $this->setExpectedException('Grace\DBAL\ExceptionQuery');
        $r = $this->connection->execute('NO SQL SYNTAX');
    }
}
