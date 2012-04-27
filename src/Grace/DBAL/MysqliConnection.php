<?php

namespace Grace\DBAL;

class MysqliConnection extends AbstractConnection
{
    /** @var \mysqli */
    private $dbh;
    private $transactionProcess = false;

    public function execute($query, array $arguments = array())
    {
        $query = $this->replacePlaceholders($query, $arguments);

        if (!is_object($this->dbh)) {
            $this->connect();
        }

        $timer  = time() + microtime(false);
        $result = $this->dbh->query($query);
        $timer  = (time() + microtime(false) - $timer);
        $this
            ->getEventDispatcher()
            ->notify(InterfaceConnection::EVENT_DB_QUERY, array(
                                                               //TODO magic strings
                                                               'time'  => $timer,
                                                               'query' => $query,
                                                          ));

        if ($result === false) {
            if ($this->transactionProcess) {
                $this->rollback();
            }
            throw new ExceptionQuery(
                'Query error ' . $this->dbh->errno . ' - ' . $this->dbh->error . ". \nSQL:\n" . $query);
        } elseif (is_object($result)) {
            return new MysqliResult($result);
        } else {
            return true;
        }
    }
    public function escape($value)
    {
        if (!is_object($this->dbh)) {
            $this->connect();
        }
        return $this->dbh->real_escape_string($value);
    }
    public function getLastInsertId()
    {
        if (!is_object($this->dbh)) {
            return false;
        }
        return $this->dbh->insert_id;
    }
    public function getAffectedRows()
    {
        if (!is_object($this->dbh)) {
            return false;
        }
        return $this->dbh->affected_rows;
    }
    public function start()
    {
        if (!is_object($this->dbh)) {
            $this->connect();
        }
        $this->dbh->autocommit(false);
        $this->transactionProcess = true;
    }
    public function commit()
    {
        $this->dbh->commit();
        $this->dbh->autocommit(true);
        $this->transactionProcess = false;
    }
    public function rollback()
    {
        $this->dbh->rollback();
        $this->dbh->autocommit(true);
        $this->transactionProcess = false;
    }
    public function __destruct()
    {
        if (is_object($this->dbh)) {
            $this->dbh->close();
        }
    }
    private function connect()
    {
        //TODO
        //$this->_logger->startQuery();

        if (!function_exists("mysqli_connect")) {
            throw new ExceptionConnection("Function mysqli_connect doesn\'t exists");
        }

        $config = $this->getConfig();

        $timer = time() + microtime(false);
        //Can throw warning, if have incorrect connection params
        //So we need '@'
        $this->dbh = @mysqli_connect($config['host'], $config['user'], $config['password'], $config['database'],
                                     (int)$config['port']);
        $timer     = (time() + microtime(false) - $timer);
        $this
            ->getEventDispatcher()
            ->notify(InterfaceConnection::EVENT_DB_CONNECT, array(
                                                                 //TODO magic strings
                                                                 'time'  => $timer,
                                                                 'query' => 'CONNECT',
                                                            ));

        if (mysqli_connect_error()) {
            throw new ExceptionConnection('Error ' . mysqli_connect_errno() . ' - ' . mysqli_connect_error());
        }

        $this->dbh->query("SET character SET 'utf8'");
        $this->dbh->query('SET character_set_client = utf8');
        $this->dbh->query('SET character_set_results = utf8');
        $this->dbh->query('SET character_set_connection = utf8');
        $this->dbh->query("SET SESSION collation_connection = 'utf8_general_ci'");
        //TODO
        //        $this->_logger->stopQuery('Mysqli connect');
    }
}