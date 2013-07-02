<?php

namespace Grace\Cache;

abstract class AbstractAdapter implements CacheInterface
{
    protected function parseTtl($ttl = 0)
    {
        switch (substr($ttl, -1)) {
            case 'd':
                return intval($ttl) * 3600 * 24;
            case 'h':
                return intval($ttl) * 3600;
            case 'm':
                return intval($ttl) * 60;
            default:
                return intval($ttl);
        }
    }
}
