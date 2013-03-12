<?php

namespace Grace\Bundle\ApiBundle\Model;

use Grace\Bundle\ApiBundle\Model\User;

interface ApiAsArrayAccessibleInterface
{
    /**
     * @abstract
     * @param User $user
     * @return array fields
     */
    public function asArrayByUser(User $user);
    /**
     * @abstract
     * @return array fields
     */
    public function asArrayForNodejs();
}