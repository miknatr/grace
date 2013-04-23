<?php

namespace Grace\Cache;

interface CacheInterface
{
    public function get($key, $ttl = null, callable $cacheSetter = null);
    public function set($key, $value, $ttl = null);
    public function remove($key);
    public function clean();
}