<?php

namespace Grace\DBAL;

interface InterfaceExecutable {
    /**
     *
     * @param string $query
     * @param array $arguments
     * @return InterfaceResult 
     */
    public function execute($query, array $arguments = array());
}