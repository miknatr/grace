<?php

namespace Grace\Bundle\ApiBundle\Type;

use Grace\ORM\FieldObjectAbstract;

abstract class ApiFieldObjectAbstract extends FieldObjectAbstract
{
    /**
     * @return array|string
     */
    abstract public function getApiValue();
}
