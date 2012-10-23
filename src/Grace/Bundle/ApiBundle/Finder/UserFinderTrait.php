<?php

namespace Grace\Bundle\ApiBundle\Finder;

use Grace\Bundle\ApiBundle\Model\User;

trait UserFinderTrait
{
    public function getByUsername($username)
    { 
        /** @var $cache \Grace\Bundle\CommonBundle\Cache\Cache */
        //        $cache = $this->getContainer()->getCache();
        //
        //        return $cache->get(User::CACHE_PREFIX_USERNAME . $username, '3m', function () use ($username) {

        $prefix = static::USERNAME_PREFIX;

        if (substr($username, 0, strlen($prefix)) != $prefix) {
            return false;
        }

        $trimmed = substr($username, strlen($prefix));

        return $this
            ->getSelectBuilder()
            ->eq('phone', $trimmed)
            ->fetchOneOrFalse();
        //        });
    }
    public function getByToken($token)
    {
        /** @var $cache \Grace\Bundle\CommonBundle\Cache\Cache */
        $cache = $this->getContainer()->getCache();

        return $cache->get(User::CACHE_PREFIX_TOKEN . $token, '3m', function () use ($token) {
                return $this
                    ->getSelectBuilder()
                    ->eq('token', $token)
                    ->fetchOneOrFalse();
            });
    }
}