<?php

namespace Grace\Cache;

interface CacheInterface
{
    /**
     * @param string $key
     * @param int|string $ttl see AbstractAdapter::parseTtl(), example: '12d' - 12 days, '14h' - 14 hours, '13m' - 13 minutes, 0 - infinite
     * @param callable $cacheSetter
     * @return null|mixed
     */
    public function get($key, $ttl = 0, callable $cacheSetter = null);
    public function set($key, $value, $ttl = 0);
    public function remove($key);
    public function clean();
}
