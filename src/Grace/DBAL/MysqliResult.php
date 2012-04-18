<?php

namespace Grace\DBAL;

class MysqliResult extends AbstractResult {
    /** @var \mysqli_result */
    private $result;

    public function fetchOne() {
        return $this->result->fetch_assoc();
    }
    public function __construct(\mysqli_result $result) {
        $this->result = $result;
    }
    public function __destruct() {
        $this->result->free();
    }
}