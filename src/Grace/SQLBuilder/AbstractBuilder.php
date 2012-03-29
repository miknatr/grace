<?php

namespace Grace\SQLBuilder;

use Grace\DBAL\InterfaceExecutable;
use Grace\DBAL\InterfaceResult;

abstract class AbstractBuilder {
    /** @var InterfaceExecutable */
    private $executable;
    protected $from;

    public function __construct($fromTable, InterfaceExecutable $executable) {
        $this->from = $fromTable;
        $this->executable = $executable;
    }
    /**
     * @return InterfaceResult
     */
    public function execute() {
        return $this->executable->execute($this->getQueryString(),
                $this->getQueryArguments());
    }
    abstract protected function getQueryString();
    abstract protected function getQueryArguments();
}

