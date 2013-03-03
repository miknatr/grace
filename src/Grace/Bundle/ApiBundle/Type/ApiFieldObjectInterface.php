<?php

namespace Grace\Bundle\ApiBundle\Type;

interface ApiFieldObjectInterface
{
    /**
     * @return array|string
     */
    public function getApiValue();
}
