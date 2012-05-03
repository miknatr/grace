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

/**
 * Provides db query logging
 * Used by all connection instances
 */
class QueryLogger
{
    private $timer = 0;
    private $counter = 0;
    private $queries = array();
    /**
     * Start timer for query
     * @param $queryString sql query string
     */
    public function startQuery($queryString)
    {
        $this->queries[$this->counter] = array('query' => $queryString);
        $this->timer                   = time() + microtime(true);
    }
    /**
     *  Stops timer and logs query information
     */
    public function stopQuery()
    {
        $this->queries[$this->counter]['time'] = (time() + microtime(true) - $this->timer);
        $this->counter++;
        $this->timer = 0;
    }
    /**
     * Gets all log information
     * Return example:
     *     array(
     *         array('query' => 'SELECT 1', 'time' => 0.054),
     *         array('query' => 'SHOW DATABASES', 'time' => 0.034),
     *     )
     * @return array queries array
     */
    public function getQueries()
    {
        return $this->queries;
    }
}