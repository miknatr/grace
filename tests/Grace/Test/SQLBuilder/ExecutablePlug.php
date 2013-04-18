<?php

namespace Grace\Test\SQLBuilder;

use Grace\DBAL\AbstractConnection\ExecutableInterface;

class ExecutablePlug implements ExecutableInterface
{
    public $query;
    public $arguments;

    public function execute($query, array $arguments = array())
    {
        $this->query     = $query;
        $this->arguments = $arguments;
        return $this;
    }
}