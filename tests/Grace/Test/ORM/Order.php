<?php

namespace Grace\Test\ORM;

use Grace\ORM\RecordAbstract;

class Order extends RecordAbstract
{
    static protected $fieldNames = array('id', 'name', 'phone');
    static protected $noDbFieldNames = array();

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
}
