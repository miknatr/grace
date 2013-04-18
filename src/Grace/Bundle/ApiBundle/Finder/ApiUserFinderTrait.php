<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Model\User;

trait ApiUserFinderTrait
{
    public function getByToken($token)
    {
        /** @var $cache \Grace\Bundle\CommonBundle\Cache\Cache */
        $cache = $this->getOrm()->getCache();

        return $cache->get(User::CACHE_PREFIX_TOKEN . $token, '3m', function () use ($token) {
                return $this
                    ->getSelectBuilder()
                    ->eq('token', $token)
                    ->fetchOneOrFalse();
            });
    }
}
