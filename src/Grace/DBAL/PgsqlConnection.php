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
    private $last_result;
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
     * @return mixed
     * @throws ExceptionQuery
     */
    public function execute($query, array $arguments = array())
    {
        $query = $this->replacePlaceholders($query, $arguments);

        if (!is_resource($this->resource)) {
            $this->connect();
        }

        $this
            ->getLogger()
            ->startQuery($query);
        $this->last_result = @pg_query($this->resource, $query);
        $this
            ->getLogger()
            ->stopQuery();

        if ($this->last_result === false) {
            if ($this->transactionProcess) {
                $this->rollback();
            }
            throw new ExceptionQuery('Query error ' . pg_errormessage($this->resource) . ". \nSQL:\n" . $query);
        } elseif($this->getNumRows()) {
            return new PgsqlResult($this->last_result);
        }
        else {
            return true;
        }
    }

    private function getNumRows()
    {
        if (!is_resource($this->last_result)) {
            return false;
        }

        return pg_num_rows($this->last_result);
    }

    /**
     * @inheritdoc                                Connection
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
    public function getAffectedRows()
    {
        if (!is_resource($this->last_result)) {
            return false;
        }

        return pg_affected_rows($this->last_result);
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
        throw new ExceptionConnection('UndefinedBehavior.');
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        if ($this->resource) {
            pg_close($this->resource);
        }
    }
    /**
     * Establishes connection
     * @throws ExceptionConnection
     */
    private function connect()
    {
        lala_function();
        if (!function_exists("pg_connect")) {
            throw new ExceptionConnection("Function pg_connect doesn't exist");
        }

        $timer = time() + microtime(false);
        //Can throw warning, if have incorrect connection params
        //So we need '@'
        $this
            ->getLogger()
            ->startConnection('Pgsql connection');
        $connect_string = $this->generateConnectionString();
        $this->resource = @\pg_connect($connect_string);
        $this
            ->getLogger()
            ->stopConnection();

        if (!$this->resource) {
            $error = \error_get_last();
            throw new ExceptionConnection('Error ' . $error['message']);
        }
    }

    private function generateConnectionString()
    {
        $connectionString  =  '';
        $params = array(
            'host',
            'port',
            'user',
            'password',
            'database',
        );

        foreach($params  as  $param_name){

            $value = $this->$param_name;

            if(!$value){
                continue;
            }

            switch($param_name){
                case 'database':
                    $connectionString .= 'dbname='.$value;
                break;

                default:
                    $connectionString  .=  $param_name.'='.$value;
                    break;
            }

            $connectionString  .=  ' ';
        }


        $connectionString  .=  'options=\'--client_encoding=UTF8\'';
        return  rtrim($connectionString);
    }
}