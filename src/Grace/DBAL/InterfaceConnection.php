<?php

namespace Grace\DBAL;

use Grace\EventDispatcher\Dispatcher;
use Grace\SQLBuilder\Factory;

interface InterfaceConnection extends InterfaceExecutable {
    const EVENT_DB_QUERY = 'eventDBQuery';
    const EVENT_DB_CONNECT = 'eventDBConnect';
    public function __construct(array $config, Dispatcher $dispatcher);
    public function escape($value);
    public function replacePlaceholders($query, array $arguments);
    public function getLastInsertId();
    public function getAffectedRows();
    /**
     * Starts transaction if it haven't started before 
     */
    public function start();
    /**
     * Commit transaction 
     */
    public function commit();
    /**
     * Rollback transaction if it have started 
     */
    public function rollback();
    /**
     * @return Factory; 
     */
    public function getSQLBuilder();
}