<?php

namespace Grace\Bundle\ApiBundle\Model;

use Grace\Bundle\ApiBundle\Model\User;

interface ResourceInterface
{
    /**
     * @abstract
     * @param array $fields
     * @param User $user
     * @return array $fields
     */
    public function editByUser(User $user, array $fields);
    /**
     * @abstract
     * @param User $user
     * @return bool
     */
    public function deleteByUser(User $user);
    /**
     * @abstract
     * @param User $user
     * @return array fields
     */
    public function asArrayByUser(User $user);
    /**
     * @abstract
     * @param User $user
     * @return array fields
     */
    public function asArrayByUserExtendedById(User $user);
    /**
     * @abstract
     * @param User $user
     * @return array
     */
    public function getPrivilegeForUser(User $user);
}