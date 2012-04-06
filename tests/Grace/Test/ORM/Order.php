<?php

namespace Grace\Test\ORM;

use Grace\ORM\Record;

class Order extends Record {
    public function getName() {
        return $this->fields['name'];
    }
    public function setName($name) {
        $this->fields['name'] = $name;
        return $this;
    }
    public function getPhone() {
        return $this->fields['phone'];
    }
    public function setPhone($phone) {
        $this->fields['phone'] = $phone;
        return $this;
    }
    public function getEventDispatcherPublic() {
        return parent::getEventDispatcher();
    }
}