<?php

namespace Grace\Tests\ORM\Service\GeneratorResources\Model;

use Grace\ORM\ModelAbstract;

/**
 * Test comment
 */
class EmptyOverriddenModel extends ModelAbstract
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
}
