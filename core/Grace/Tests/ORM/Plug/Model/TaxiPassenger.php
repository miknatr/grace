<?php

namespace Grace\Tests\ORM\Plug\Model;

use Grace\ORM\ModelAbstract;

class TaxiPassenger extends ModelAbstract
{
    public function getName()
    {
        return $this->properties['name'];
    }
    public function setName($name)
    {
        $this->properties['name'] = $name;
        $this->markAsChanged();
        return $this;
    }
    public function getPhone()
    {
        return $this->properties['phone'];
    }
    public function setPhone($phone)
    {
        $this->properties['phone'] = $phone;
        $this->markAsChanged();
        return $this;
    }
}
