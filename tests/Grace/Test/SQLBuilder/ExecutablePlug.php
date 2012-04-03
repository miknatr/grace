<?php

namespace Grace\Test\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;

class ExecutablePlug implements InterfaceExecutable {
    public $query;
    public $arguments;

    public function execute($query, array $arguments = array()) {
        $this->query = $query;
        $this->arguments = $arguments;
        return $this;
    }
}