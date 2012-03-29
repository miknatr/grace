<?php

namespace Grace\DBAL;

interface InterfaceConnection extends InterfaceExecutable {
    public function __construct(array $config);
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
    public function getSQLBuilder();
}