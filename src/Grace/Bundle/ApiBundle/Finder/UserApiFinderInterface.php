<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Model\User;

interface UserApiFinderInterface
{
    public function getByToken($token);
}