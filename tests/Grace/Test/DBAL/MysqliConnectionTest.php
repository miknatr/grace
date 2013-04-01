<?php

namespace Grace\Test\DBAL;

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
        //Lazy connection, only if we really use database
        $r = $this->connection->execute('SELECT 1');
    }
    public function testFetchingOne()
    {
        $r = $this->connection
            ->execute('SELECT 1 AS "1", 2 AS "2", 3 AS "3"')
            ->fetchOne();
        $this->assertEquals(array(
            '1'  => '1',
            '2'  => '2',
            '3'  => '3'
        ), $r);
    }
    public function testFetchingAll()
    {
        $r = $this->connection
            ->execute('select 1 AS "1",2 AS "2",3 AS "3"')
            ->fetchAll();
        $this->assertEquals(array(
            array(
                '1' => '1',
                '2' => '2',
                '3' => '3'
            )
        ), $r);
    }
    public function testFetchingResult()
    {
        $r = $this->connection
            ->execute('select 1 AS "1",2 AS "2",3 AS "3"')
            ->fetchResult();
        $this->assertEquals('1', $r);
    }
    public function testFetchingColumn()
    {
        $r = $this->connection
            ->execute('SELECT 1 AS "1"')
            ->fetchColumn();
        $this->assertEquals(array('1'), $r);
    }
    public function testFetchingHash()
    {
        $r = $this->connection
            ->execute('SELECT \'kkk\' AS "kkk", \'vvv\' AS "vvv" ')
            ->fetchHash();
        $this->assertEquals(array('kkk' => "vvv"), $r);
    }
    public function testGettingLastInsertIdBeforeConnectionEsbablished()
    {
        $this->assertEquals(false, $this->connection->getLastInsertId());
    }
    public function testGettingLastInsertId()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (10, "Mike")');
        $this->assertEquals('10', $this->connection->getLastInsertId());
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testGettingAffectedRowsBeforeConnectionEsbablished()
    {
        $this->assertEquals(false, $this->connection->getAffectedRows());
    }
    public function testGettingAffectedRows()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (1, "Mike")');
        $this->connection->execute('INSERT INTO test VALUES (2, "John")');
        $this->connection->execute('INSERT INTO test VALUES (3, "Bill")');
        $this->connection->execute('UPDATE test SET name="Human"');
        $this->assertEquals(3, $this->connection->getAffectedRows());
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testEscaping()
    {
        $r = $this->connection->escape("quote ' quote");
        $this->assertEquals("quote \' quote", $r);
    }
    public function testReplacingPlaceholders()
    {
        $r = $this->connection->replacePlaceholders("SELECT ?q, '?e', \"?p\", ?f, ?F, ?l, ?i, ?q:named_pl: FROM DUAL",
            array(
                '\'quoted\'',
                '\'escaped\'',
                '\'plain\'',
                'test',
                'test1.test2',
                array('t1', 't2'),
                array('f1', 'f2'),
                'named_pl' => '\'named quoted\'',
            ));
        $this->assertEquals("SELECT '\'quoted\'', '\'escaped\'', \"'plain'\", `test`, `test1`.`test2`, 't1', 't2', `f1`, `f2`, '\'named quoted\'' FROM DUAL", $r);
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
    public function testTransactionCommit()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255)) ENGINE=InnoDB');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, "Mike")');
        $this->connection->execute('INSERT INTO test VALUES (2, "John")');
        $this->connection->execute('INSERT INTO test VALUES (3, "Bill")');
        $this->connection->commit();
        $r = $this->connection
            ->execute('SELECT COUNT(id) FROM test')
            ->fetchResult();
        $this->assertEquals('3', $r);
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testTransactionManualRollback()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255)) ENGINE=InnoDB');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, "Mike")');
        $this->connection->execute('INSERT INTO test VALUES (2, "John")');
        $this->connection->execute('INSERT INTO test VALUES (3, "Bill")');
        $this->connection->rollback();
        $r = $this->connection
            ->execute('SELECT COUNT(id) FROM test')
            ->fetchResult();
        $this->assertEquals('0', $r);
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testTransactionRollbackOnError()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255)) ENGINE=InnoDB');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, "Mike")');
        $this->connection->execute('INSERT INTO test VALUES (2, "John")');
        $this->connection->execute('INSERT INTO test VALUES (3, "Bill")');
        try {
            $this->connection->execute('NO SQL SYNTAX');
        } catch (ExceptionQuery $e) {
            ;
        }
        $r = $this->connection
            ->execute('SELECT COUNT(id) FROM test')
            ->fetchResult();
        $this->assertEquals('0', $r);
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
}
