<?php

namespace Grace\Cache;

interface ManagerInterface
{
    public function get($presetName, $key, $ttl = 0, callable $cacheSetter = null);
    public function set($presetName, $key, $value, $ttl = 0);
}