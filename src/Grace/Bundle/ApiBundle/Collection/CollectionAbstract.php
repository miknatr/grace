<?php

namespace Grace\Bundle\ApiBundle\Collection;

use Grace\ORM\Collection;
use Grace\Bundle\ApiBundle\Model\User;

abstract class CollectionAbstract extends Collection
{
    public function asArrayByUser(User $user)
    {
        $r = array();
        foreach ($this as $record) {
            $r[] = $record->asArrayByUser($user);
        }

        return $r;
    }
    public function asArrayByUserExtendedList(User $user)
    {
        $r = array();
        foreach ($this as $record) {
            $r[] = $record->asArrayByUserExtendedList($user);
        }

        return $r;
    }
}