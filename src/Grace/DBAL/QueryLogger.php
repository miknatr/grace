<?php

namespace Grace\DBAL;

class QueryLogger
{
    private $timer = 0;
    private $counter = 0;
    private $queries = array();
    public function startQuery($queryString) {
        $this->queries[$this->counter] = array('query' => $queryString);
        $this->timer = time() + microtime(true);
    }
    public function stopQuery() {
        $this->queries[$this->counter]['time'] = (time() + microtime(true) - $this->timer);
        $this->counter++;
        $this->timer = 0;
    }
    public function getQueries() {
        return $this->queries;
    }
}