<?php

namespace Grace\Test\DBAL;

use Grace\DBAL\InterfaceConnection;
use Grace\DBAL\InterfaceResult;
use Grace\DBAL\ExceptionConnection;
use Grace\DBAL\ExceptionQuery;
use Grace\SQLBuilder\Factory;

abstract class AbstractConnectionTest extends \PHPUnit_Framework_TestCase {
    /** @var InterfaceConnection */
    protected $connection;

    public function testEstablishConnection() {
        $this->assertTrue($this->connection instanceof InterfaceConnection);
    }
    public function testSuccessfullQueryWithResults() {
        $r = $this->connection->execute('SELECT 1');
        $this->assertTrue($r instanceof InterfaceResult);
    }
    public function testSuccessfullQueryWithoutResults() {
        $r = $this->connection->execute('DO 1');
        $this->assertTrue($r);
    }
    public function testFailQuery() {
        $this->setExpectedException('Grace\DBAL\ExceptionQuery');
        $r = $this->connection->execute('NO SQL SYNTAX');
    }
    public function testFetchingOne() {
        $r = $this->connection->execute('SELECT 1, 2, 3 FROM DUAL')->fetchOne();
        $this->assertEquals(array('1' => '1', '2' => '2', '3' => '3'), $r);
    }
    public function testFetchingAll() {
        $r = $this->connection->execute('SELECT 1, 2, 3 FROM DUAL')->fetchAll();
        $this->assertEquals(array(array('1' => '1', '2' => '2', '3' => '3')), $r);
    }
    public function testFetchingResult() {
        $r = $this->connection->execute('SELECT 1, 2, 3 FROM DUAL')->fetchResult();
        $this->assertEquals('1', $r);
    }
    public function testFetchingColumn() {
        $r = $this->connection->execute('SELECT 1, 2, 3 FROM DUAL')->fetchColumn();
        $this->assertEquals(array('1'), $r);
    }
    public function testGettingLastInsertIdBeforeConnectionEsbablished() {
        $this->assertEquals(false, $this->connection->getLastInsertId());
    }
    public function testGettingLastInsertId() {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (10, "Mike")');
        $this->assertEquals('10', $this->connection->getLastInsertId());
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testGettingAffectedRowsBeforeConnectionEsbablished() {
        $this->assertEquals(false, $this->connection->getAffectedRows());
    }
    public function testGettingAffectedRows() {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id INT(10) PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (1, "Mike")');
        $this->connection->execute('INSERT INTO test VALUES (2, "John")');
        $this->connection->execute('INSERT INTO test VALUES (3, "Bill")');
        $this->connection->execute('UPDATE test SET name="Human"');
        $this->assertEquals(3, $this->connection->getAffectedRows());
        $this->connection->execute('DROP TABLE IF EXISTS test');
    }
    public function testEscaping() {
        $r = $this->connection->escape("quote ' quote");
        $this->assertEquals("quote \' quote", $r);
    }
    public function testReplacingPlaceholders() {
        $r = $this->connection
            ->replacePlaceholders("SELECT ?q, '?e', \"?p\" FROM DUAL",
            array(
            '\'quoted\'',
            '\'escaped\'',
            '\'plain\'',
            ));
        $this->assertEquals(
            "SELECT '\'quoted\'', '\'escaped\'', \"'plain'\" FROM DUAL", $r);
    }
    public function testTransactionCommitBeforeAnyQueries() {
        //There is a validation that we can start transaction
        //before any queries and connection object will establish
        //connection automatically (lazy connection)
        $this->connection->start();
        $this->connection->execute('SELECT 1');
        $this->connection->commit();
        $this->assertTrue(true);
    }
    public function testTransactionCommit() {
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
    public function testTransactionManualRollback() {
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
    public function testTransactionRollbackOnError() {
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
    public function testGettingSQLBuilder() {
        $this->assertTrue($this->connection->getSQLBuilder() instanceof Factory);
    }
}
