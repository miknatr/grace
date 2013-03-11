<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Collection\CollectionAbstract;
use Grace\Bundle\ApiBundle\Model\User;
use Grace\SQLBuilder\SelectBuilder;

trait ApiFinderTrait
{
    public function count(User $user, array $params = array())
    {
        return $this
            ->prepareBuilder($params)
            ->count()
            ->fetchResult();
    }
    public function get(User $user, array $params = array(), $start = null, $number = null)
    {
        $builder = $this->prepareBuilder($params);

        if (!is_null($start) and !is_null($number)) {
            $builder->limit($start, $number);
        }

        return $builder->order('id DESC')->fetchAll();
    }

    /**
     * @param array $params
     * @return SelectBuilder
     */
    abstract protected function prepareBuilder(User $user, array $params = array());
}
