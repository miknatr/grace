<?php

namespace Grace\Cache;

interface PresetInterface
{
    public function get($key);
    public function set($key, $value, $ttl);
}