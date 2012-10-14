<?php

namespace Grace\Bundle\CommonBundle\Cache;

class Cache implements CacheInterface
{
    private $adapter;
    private $namespace;
    private $enabled;

    public function __construct(\Zend_Cache_Core $adapter, $namespace = '', $enabled = true)
    {
        $this->adapter = $adapter;
        $this->namespace = $namespace;
        $this->enabled = $enabled;
    }
    
    public function get($key, $ttl = null, callable $cacheSetter = null)
    {
        $r = false;

        if ($this->enabled) {
            $r = $this->adapter->load($this->namespace . '__' . $key);
            if ($r === false && $cacheSetter !== null) {
                $r = call_user_func($cacheSetter);
                $this->set($key, $r, $ttl);
            }
        } else {
            if ($cacheSetter !== null) {
                $r = call_user_func($cacheSetter);
            }
        }

        return $r;
    }
    public function set($key, $value, $ttl = null)
    {
        $this->adapter->save($value, $this->namespace . '__' . $key, array(), $this->parseTtl($ttl));
    }
    public function remove($key)
    {
        $this->adapter->remove($this->namespace . '__' . $key);
    }
    public function clean()
    {
        $this->adapter->clean();
    }
    private function parseTtl($ttl = null)
    {
        if ($ttl === null) {
            return false;
        }

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