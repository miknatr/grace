<?php
/*
 * This file is part of the Grace package.
 *
 * (c) Mikhail Natrov <miknatr@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Grace\DBAL;

use Grace\SQLBuilder\Factory;

/**
 * Provides common connection interface
 */
interface InterfaceConnection extends InterfaceExecutable
{
    /**
     * Escapes value for sql statement
     * @abstract
     * @param $value
     * @return string escaped value
     */
    public function escape($value);
    /**
     * Escapes field name for sql statement
     * @abstract
     * @param $value
     * @return string escaped value
     */
    public function escapeField($value);
    /**
     * Replaces and escapes arguments in query
     *
     * Query can contain named and ordered placeholders like ?i:name: and ?i respectively
     * which are replaced in this method by real values from arguments array
     *
     * Named placeholders are replaced by associative part of $arguments array
     * Ordered placeholders are replaced by numeric part of $arguments array
     *
     * Method throws QueryException
     * if $arguments is not contain one of named placeholders from $query
     * or number of ordered placeholders $query is greater than number of numeric members in $arguments
     *
     * @abstract
     * @param       $query
     * @param array $arguments
     * @return string sql query
     */
    public function replacePlaceholders($query, array $arguments);
    /**
     * Returns last insert id
     * @abstract
     * @return string last insert id
     */
    public function getLastInsertId();
    /**
     * Returns number of affected rows
     * @abstract
     * @return int affected row number
     */
    public function getAffectedRows();
    /**
     * Starts transaction if it haven't started before
     * @abstract
     */
    public function start();
    /**
     * Commit transaction
     * @abstract
     */
    public function commit();
    /**
     * Rollback transaction if it have started
     * @abstract
     */
    public function rollback();
    /**
     * Returns new instance of SQLBuilder\Factory
     * @abstract
     * @return Factory;
     */
    public function getSQLBuilder();
    /**
     * Returns instance of DBAL\Logger
     * Creates object if necessary (once)
     * @abstract
     * @return QueryLogger;
     */
    public function getLogger();
    /**
     * Sets logger for this connection
     * @abstract
     * @param QueryLogger $logger
     * @return InterfaceConnection
     */
    public function setLogger(QueryLogger $logger);
    /**
     * Returns instance of \Grace\Cache\CacheInterface
     * @abstract
     * @return \Grace\Cache\CacheInterface;
     */
    public function getCache();
    /**
     * Sets cache for this connection
     * @abstract
     * @param \Grace\Cache\CacheInterface $logger
     * @return InterfaceConnection
     */
    public function setCache(\Grace\Cache\CacheInterface $logger);
    /**
     * Generate new id for insert
     * @return mixed
     */
    public function generateNewId($table);
}
