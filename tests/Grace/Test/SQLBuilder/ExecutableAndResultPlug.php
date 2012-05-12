<?php

namespace Grace\Test\SQLBuilder;

use Grace\DBAL\InterfaceResult;

class ExecutableAndResultPlug extends ExecutablePlug implements InterfaceResult
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
    public function fetchOne()
    {
        return 'one';
    }
    public function fetchOneOrFalse()
    {
        return 'one or false';
    }
}