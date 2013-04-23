<?php

namespace Grace\Tests\ORM\Service\GeneratorResources\Model;

use Grace\ORM\ModelAbstract;

/**
 * Test comment
 */
class NoActualGeneratedModel extends ModelAbstract
{
    public function getName()
    {
        //overridden method
    }
    public function setName($name)
    {
        //overridden method
    }


    /* BEGIN GRACE GENERATED CODE */
    //not actual generated code
    public function getPhone()
    {
        return 'BLAH';
    }
}
