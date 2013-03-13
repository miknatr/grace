<?php

namespace Grace\Bundle\ApiBundle\Collection;

use Grace\Bundle\ApiBundle\Model\ApiAsArrayAccessibleInterface;
use Grace\Bundle\ApiBundle\Model\ResourceAbstract;
use Grace\ORM\Collection;
use Grace\Bundle\ApiBundle\Model\User;

abstract class CollectionAbstract extends Collection implements ApiAsArrayAccessibleInterface
{
    public function asArrayByUser(User $user)
    {
        $r = array();
        foreach ($this as $record) {
            /** @var $record ResourceAbstract */
            $r[] = $record->asArrayByUser($user);
        }
        return $r;
    }

    public function asArrayByUserExtendedList(User $user)
    {
        $r = array();
        foreach ($this as $record) {
            /** @var $record ResourceAbstract */
            $r[] = $record->asArrayByUserExtendedList($user);
        }
        return $r;
    }

    public function asArrayForNodejs()
    {
        $r = array();
        foreach ($this as $record) {
            /** @var $record ResourceAbstract */
            $r[] = $record->asArrayForNodejs();
        }
        return $r;
    }
}
