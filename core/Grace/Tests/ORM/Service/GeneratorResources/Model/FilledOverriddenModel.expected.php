<?php

namespace Grace\Tests\ORM\Service\GeneratorResources\Model;

use Grace\ORM\ModelAbstract;

/**
 * Test comment
 */
class FilledOverriddenModel extends ModelAbstract
{
    public function getName()
    {
        //overridden method
    }
    public function setName($name)
    {
        //overridden method
    }
    public function getPhone()
    {
        //overridden method
    }
    public function setPhone($phone)
    {
        //overridden method
    }


    /* BEGIN GRACE GENERATED CODE */

    public function getNameGenerated()
    {
        return $this->properties['name'];
    }
    public function setNameGenerated($name)
    {
        $this->properties['name'] = $this->orm->typeConverter->convertOnSetter('string', $name);
        $this->markAsChanged();
        return $this;
    }
    public function getPhoneGenerated()
    {
        return $this->properties['phone'];
    }
    public function setPhoneGenerated($phone)
    {
        $this->properties['phone'] = $this->orm->typeConverter->convertOnSetter('string', $phone);
        $this->markAsChanged();
        return $this;
    }
}
