<?php

namespace Grace\Test\DBAL;

use Grace\DBAL\PgsqlConnection;
use Grace\DBAL\ExceptionConnection;
use Grace\DBAL\ExceptionQuery;

class PgsqlConnectionTest extends AbstractConnectionTest
{
    /** @var PgsqlConnection */
    protected $connection;

    protected function setUp()
    {
        $this->connection =
            new PgsqlConnection(TEST_PGSQL_HOST, TEST_PGSQL_PORT, TEST_PGSQL_NAME, TEST_PGSQL_PASSWORD, TEST_PGSQL_DATABASE);
    }
    protected function tearDown()
    {
        unset($this->connection);
    }
    public function testBadConnectionConfig()
    {
        $this->setExpectedException('Grace\DBAL\ExceptionConnection');
        unset($this->connection);
        $this->connection =
            new PgsqlConnection(TEST_PGSQL_HOST, TEST_PGSQL_PORT, 'SOME_BAD_NAME', TEST_PGSQL_PASSWORD, TEST_PGSQL_DATABASE);

        //Lazy connection, only if we really use database
        $r = $this->connection->execute('SELECT 1');
    }

    public function testSuccessfullQueryWithoutResults()
    {
        $r = $this->connection->execute('CREATE TEMP TABLE test(id serial);');
        $this->assertTrue($r);
    }

    public function testFieldEscaping()
    {
        $r = $this->connection->escapeField("field");
        $this->assertEquals('"field"', $r);
    }

    public function testEscaping()
    {
        $r = $this->connection->escape("quote ' quote");
        $this->assertEquals("quote '' quote", $r);
    }

    public function testReplacingPlaceholders()
    {
        $r = $this->connection->replacePlaceholders("SELECT ?q, '?e', \"?p\", ?f, ?F, ?l, ?i, ?q:named_pl: FROM DUAL", array(
            '\'quoted\'',
            '\'escaped\'',
            '\'plain\'',
            'test',
            'test1.test2',
            array('t1', 't2'),
            array('f1', 'f2'),
            'named_pl' => '\'named quoted\'',
        ));
        $this->assertEquals("SELECT '''quoted''', '''escaped''', \"'plain'\", \"test\", \"test1\".\"test2\", 't1', 't2', \"f1\", \"f2\", '''named quoted''' FROM DUAL", $r);
    }

    public function testGettingLastInsertIdBeforeConnectionEsbablished()
    {
        $this->setExpectedException('Grace\DBAL\ExceptionConnection');
        $this->connection->getLastInsertId();
    }

    public function testTransactionCommit()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TABLE test (id serial, "name" VARCHAR(255))');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, \'Mike\')');
        $this->connection->execute('INSERT INTO test VALUES (2, \'John\')');
        $this->connection->execute('INSERT INTO test VALUES (3, \'Bill\')');
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
        $this->connection->execute('CREATE TABLE test (id serial, "name" VARCHAR(255))');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, \'Mike\')');
        $this->connection->execute('INSERT INTO test VALUES (2, \'John\')');
        $this->connection->execute('INSERT INTO test VALUES (3, \'Bill\')');
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
        $this->connection->execute('CREATE TABLE test (id serial, "name" VARCHAR(255))');
        $this->connection->start();
        $this->connection->execute('INSERT INTO test VALUES (1, \'Mike\')');
        $this->connection->execute('INSERT INTO test VALUES (2, \'John\')');
        $this->connection->execute('INSERT INTO test VALUES (3, \'Bill\')');
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

    public function testGettingLastInsertId()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TEMP TABLE test (id serial, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (10, \'Mike\')');
        $this->setExpectedException('Grace\DBAL\ExceptionConnection');
        $this->connection->getLastInsertId();
    }

    public function testGettingAffectedRows()
    {
        $this->connection->execute('DROP TABLE IF EXISTS test');
        $this->connection->execute('CREATE TEMP TABLE test (id serial, name VARCHAR(255))');
        $this->connection->execute('INSERT INTO test VALUES (1, \'Mike\')');
        $this->connection->execute('INSERT INTO test VALUES (2, \'John\')');
        $this->connection->execute('INSERT INTO test VALUES (3, \'Bill\')');
        $this->connection->execute('UPDATE test SET name=\'Human\'');
        $this->assertEquals(3, $this->connection->getAffectedRows());
    }

    public function testGettingAffectedRowsBeforeConnectionEsbablished()
    {
        $this->assertEquals(false, $this->connection->getAffectedRows());
    }
}
