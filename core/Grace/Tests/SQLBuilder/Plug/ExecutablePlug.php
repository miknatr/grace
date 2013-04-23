<?php

namespace Grace\Tests\SQLBuilder\Plug;

use Grace\DBAL\ConnectionAbstract\ExecutableInterface;

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
