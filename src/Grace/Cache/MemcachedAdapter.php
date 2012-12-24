<?php

namespace Grace\Cache;

class MemcachedAdapter extends AbstractAdapter
{
    private $adapter;
    private $namespace;
    private $enabled;

    public function __construct(\Memcached $adapter, $namespace = '', $enabled = true)
    {
        $this->adapter = $adapter;
        $this->namespace = $namespace;
        $this->enabled = $enabled;
    }
    
    public function get($key, $ttl = null, callable $cacheSetter = null)
    {
        $r = false;

        if ($this->enabled) {
            $r = $this->adapter->get($this->formatKey($key));
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
        $this->adapter->set($this->formatKey($key), $value, time() + $this->parseTtl($ttl));
    }
    public function remove($key)
    {
        $this->adapter->delete($this->formatKey($key));
    }
    public function clean()
    {
        $this->adapter->flush();
    }

    protected function formatKey($key)
    {
        return $this->namespace . '__' . $key;
    }
}