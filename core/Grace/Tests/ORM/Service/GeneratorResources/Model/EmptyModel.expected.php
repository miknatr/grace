<?php

namespace Grace\Tests\ORM\Service\GeneratorResources\Model;

use Grace\ORM\ModelAbstract;

/**
 * Test comment
 */
class EmptyModel extends ModelAbstract
{


    /* BEGIN GRACE GENERATED CODE */

    public function getName()
    {
        return $this->properties['name'];
    }
    public function setName($name)
    {
        $this->properties['name'] = $this->orm->typeConverter->convertOnSetter('string', $name);
        $this->markAsChanged();
        return $this;
    }
    public function getPhone()
    {
        return $this->properties['phone'];
    }
    public function setPhone($phone)
    {
        $this->properties['phone'] = $this->orm->typeConverter->convertOnSetter('string', $phone);
        $this->markAsChanged();
        return $this;
    }
}
