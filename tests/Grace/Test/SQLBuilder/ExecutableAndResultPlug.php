<?php

namespace Grace\Test\SQLBuilder;

use Grace\DBAL\AbstractConnection\ResultInterface;

class ExecutableAndResultPlug extends ExecutablePlug implements ResultInterface
{
    public function fetchAll()
    {
        return 'all';
    }
    public function fetchResult()
    {
        return 'result';
    }
    public function fetchColumn()
    {
        return 'column';
    }
    public function fetchHash()
    {
        return 'hash';
    }
    public function fetchOneOrFalse()
    {
        return 'one or false';
    }
}
