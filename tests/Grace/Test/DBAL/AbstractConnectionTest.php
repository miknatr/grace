<?php

namespace Grace\Test\DBAL;

use Grace\DBAL\AbstractConnection\InterfaceConnection;
use Grace\DBAL\AbstractConnection\ResultInterface;
use Grace\SQLBuilder\Factory;

abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase
{
    abstract public function testBadConnectionConfig();
    abstract public function testGettingLastInsertIdBeforeConnectionEsbablished();
    abstract public function testGettingLastInsertId();
    abstract public function testGettingAffectedRowsBeforeConnectionEsbablished();
    abstract public function testGettingAffectedRows();
    abstract public function testEscaping();
    abstract public function testFieldEscaping();
    abstract public function testReplacingPlaceholders();
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
    public function testSuccessfulQueryWithResults()
    {
        $r = $this->connection->execute('SELECT 1');
        $this->assertTrue($r instanceof ResultInterface);
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
    public function testFetchingOne()
    {
        $r = $this->connection->execute('SELECT 1 AS "1", 2 AS "2", 3 AS "3"')->fetchOneOrFalse();
        $this->assertEquals(array('1' => '1', '2' => '2', '3' => '3'), $r);
    }
    public function testFetchingAll()
    {
        $r = $this->connection->execute('select 1 AS "1",2 AS "2",3 AS "3"')->fetchAll();
        $this->assertEquals(array(array('1' => '1', '2' => '2', '3' => '3')), $r);
    }
    public function testFetchingResult()
    {
        $r = $this->connection->execute('select 1 AS "1",2 AS "2",3 AS "3"')->fetchResult();
        $this->assertEquals('1', $r);
    }
    public function testFetchingColumn()
    {
        $r = $this->connection->execute('SELECT 1 AS "1"')->fetchColumn();
        $this->assertEquals(array('1'), $r);
    }
    public function testFetchingHash()
    {
        $r = $this->connection->execute('SELECT \'kkk\' AS "kkk", \'vvv\' AS "vvv" ')->fetchHash();
        $this->assertEquals(array('kkk' => "vvv"), $r);
    }
    public function testTransactionCommitBeforeAnyQueries()
    {
        //There is a validation that we can start transaction
        //before any queries and connection object will establish
        //connection automatically (lazy connection)
        $this->connection->start();
        $this->connection->execute('SELECT 1');
        $this->connection->commit();
        $this->assertTrue(true);
    }
}
