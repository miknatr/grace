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

    final protected function setPropertiesFromDbArray(array $dbArray)
    {
        // id
        $value = $dbArray['id'];
        if ($value === null) {
            throw new \Grace\ORM\Type\ConversionImpossibleException('Null is not allowed in Driver.id');
        }
        $this->properties['id'] = (int) $value;

        // name
        $value = $dbArray['name'];
        if ($value === null) {
            throw new \Grace\ORM\Type\ConversionImpossibleException('Null is not allowed in Driver.name');
        }
        $this->properties['name'] = $value;

        // phone
        $value = $dbArray['phone'];
        if ($value === null) {
            throw new \Grace\ORM\Type\ConversionImpossibleException('Null is not allowed in Driver.phone');
        }
        $this->properties['phone'] = $value;
    }
}
