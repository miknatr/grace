<?php

namespace Grace\Test\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;

class ExecutablePlug implements InterfaceExecutable
{
    public $query;
    public $arguments;

    protected $sqlEscapeSymbol;
    protected $dataEscapeSymbol = '\'';

    public function setDataEscapeSymbol($dataEscapeSymbol)
    {
        $this->dataEscapeSymbol = $dataEscapeSymbol;
    }

    public function setSqlEscapeSymbol($sqlEscapeSymbol)
    {
        $this->sqlEscapeSymbol = $sqlEscapeSymbol;
    }

    public function execute($query, array $arguments = array())
    {
        $this->query     = $query;
        $this->arguments = $arguments;
        return $this;
    }

    public function getSqlEscapeSymbol()
    {
        return $this->sqlEscapeSymbol;
    }

    public function getDataEscapeSymbol()
    {
        return $this->dataEscapeSymbol;
    }
}
