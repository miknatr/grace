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
    public function fetchAll() {
        return $this->execute()->fetchAll();
    }
    public function fetchOne() {
        return $this->execute()->fetchOne();
    }
    public function fetchResult() {
        return $this->execute()->fetchResult();
    }
    public function fetchColumn() {
        return $this->execute()->fetchColumn();
    }
    abstract protected function getQueryString();
    abstract protected function getQueryArguments();
}

