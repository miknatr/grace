<?php

namespace Grace\Test\ORM;

use Grace\ORM\Record;

class Order extends Record
{
    public function getName()
    {
        return $this->fields['name'];
    }
    public function setName($name)
    {
        $this->fields['name'] = $name;
        $this->markAsChanged();
        return $this;
    }
    public function getPhone()
    {
        return $this->fields['phone'];
    }
    public function setPhone($phone)
    {
        $this->fields['phone'] = $phone;
        $this->markAsChanged();
        return $this;
    }
    public function getContainerPublic()
    {
        return parent::getContainer();
    }
}