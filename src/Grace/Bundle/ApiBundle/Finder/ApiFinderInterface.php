<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Collection\CollectionAbstract;
use Grace\Bundle\ApiBundle\Model\User;

interface ApiFinderInterface
{
    /**
     * @return int
     */
    public function count(User $user, array $params = array());
    /**
     * @param $start
     * @param $number
     * @return CollectionAbstract
     */
    public function get(User $user, array $params = array(), $start = null, $number = null);

    public function getFilters();
}
