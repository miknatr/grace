<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Alex Polev <alex.v.polev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL;

/**
 * Pg connection concrete class
 */
class PgsqlConnection extends AbstractConnection
{
    private $resource;
    private $lastResult;
    private $transactionProcess = false;
    private $host;
    private $port;
    private $user;
    private $password;
    private $database;

    /**
     * Creates connection instance
     * All parameters are necessary
     * @param $host
     * @param $port
     * @param $user
     * @param $password
     * @param $database
     */
    public function __construct($host, $port, $user, $password, $database)
    {
        $this->host     = $host;
        $this->port     = $port;
        $this->user     = $user;
        $this->password = $password;
        $this->database = $database;
    }

    /**
     * @inheritdoc
     */
    public function execute($query, array $arguments = array())
    {
        //define if it command or fetch query
        $needResult = preg_match('/^(SELECT)/', ltrim($query));

        $query = $this->replacePlaceholders($query, $arguments);

        if (!is_resource($this->resource)) {
            $this->connect();
        }

        $this->getLogger()->startQuery($query);
        $this->lastResult = @pg_query($this->resource, $query);
        $this->getLogger()->stopQuery();

        if ($this->lastResult === false) {
            if ($this->transactionProcess) {
                $this->rollback();
            }
            throw new ExceptionQuery('Query error ' . pg_errormessage($this->resource) . ". \nSQL:\n" . $query);
        } elseif ($needResult) {
            return new PgsqlResult($this->lastResult);
        } else {
            return true;
        }
    }

    private function getNumRows()
    {
        if (!is_resource($this->lastResult)) {
            return false;
        }

        return pg_num_rows($this->lastResult);
    }

    /**
     * @inheritdoc
     */
    public function escape($value)
    {
        if (!is_resource($this->resource)) {
            $this->connect();
        }
        return pg_escape_string($value);
    }

    /**
     * @inheritdoc
     */
    public function escapeField($value)
    {
        if (!is_resource($this->resource)) {
            $this->connect();
        }
        if (!is_scalar($value) || strpos('"', $value)) {
            throw new ExceptionQuery('Possible SQL injection in field name');
        }
        return '"' . $value . '"';
    }

    /**
     * @inheritdoc
     */
    public function getAffectedRows()
    {
        if (!is_resource($this->lastResult)) {
            return false;
        }

        return pg_affected_rows($this->lastResult);
    }
    /**
     * @inheritdoc
     */
    public function start()
    {
        if (!is_resource($this->resource)) {
            $this->connect();
        }
        pg_query($this->resource, 'START TRANSACTION');
        $this->transactionProcess = true;
    }
    /**
     * @inheritdoc
     */
    public function commit()
    {
        pg_query($this->resource, 'COMMIT;');
        $this->transactionProcess = false;
    }
    /**
     * @inheritdoc
     */
    public function rollback()
    {
        pg_query($this->resource, 'ROLLBACK;');
        $this->transactionProcess = false;
    }

    //TODO поговорить на тему getLastInsertId который крайне криво реализуется для PostgresSQL
    //В принципе если передать в getLastInsertId имя таблицы и договориться SEQUENCE для всех таблиц именовать
    //Так как они именуются при автогенерации при использовании field_name SERIAL, то это вполне решаемо.
    // Но в таком случае у нас все равно рассыпается интерфейс, т.к. в обычном случае контекст не нужно передавать,
    // А сюда надо.
    public function getLastInsertId()
    {
        throw new ExceptionConnection('Undefined behavior');
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        $this->close();
    }
    /**
     * Establishes connection
     * @throws ExceptionConnection
     */
    private function close()
    {
        if ($this->resource) {
            pg_close($this->resource);
        }
    }
    /**
     * Establishes connection
     * @throws ExceptionConnection
     */
    private function connect($selectDb = true)
    {
        if (!function_exists("pg_connect")) {
            throw new ExceptionConnection("Function pg_connect doesn't exists");
        }

        //Can throw warning, if have incorrect connection params
        //So we need '@'
        $this
            ->getLogger()
            ->startConnection('Pgsql connection');
        $connectString = $this->generateConnectionString($selectDb);
        $this->resource = @\pg_connect($connectString);
        $this
            ->getLogger()
            ->stopConnection();

        if (!$this->resource) {
            $error = \error_get_last();
            throw new ExceptionConnection('Error ' . $error['message']);
        }
    }

    private function generateConnectionString($selectDb = true)
    {
        return "host={$this->host} port={$this->port} user={$this->user} password={$this->password}"
            . ($selectDb ? " dbname={$this->database}" : '')
            . " options='--client_encoding=UTF8'";
    }
    /**
     * @inheritdoc
     */
    public function createDatabaseIfNotExist()
    {
        $this->connect(false);
        $isExist = $this->execute("SELECT ?f FROM ?f WHERE ?f=?q", array('datname', 'pg_database', 'datname', $this->database))->fetchResult();
        if (!$isExist) {
            $this->execute('CREATE DATABASE ?f', array($this->database));
        }
        $this->close();
        $this->connect();
    }
}
