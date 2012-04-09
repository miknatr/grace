<?php

namespace Grace\Test\ORM;

use Grace\ORM\Collection;

class OrderCollection extends Collection {
    public function setName($name) {
        foreach ($this as $record) {
            $record->setName($name);
        }
        return $this;
    }
    public function setPhone($phone) {
        foreach ($this as $record) {
            $record->setPhone($phone);
        }
        return $this;
    }
}