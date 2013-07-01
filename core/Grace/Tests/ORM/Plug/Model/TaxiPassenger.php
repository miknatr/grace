<?php

namespace Grace\Tests\ORM\Plug\Model;

use Grace\ORM\ModelAbstract;

class TaxiPassenger extends ModelAbstract
{
    public function getName()
    {
        return $this->getProperty('name');
    }
    public function setName($name)
    {
        return $this->setProperty('name', $name);
    }
    public function getPhone()
    {
        return $this->getProperty('phone');
    }
    public function setPhone($phone)
    {
        return $this->setProperty('phone', $phone);
    }
}
