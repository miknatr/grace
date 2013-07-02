<?php

namespace Grace\Cache;

class MemcacheAdapter extends AbstractAdapter
{
    private $adapter;
    private $namespace;
    private $enabled;

    public function __construct(\Memcache $adapter, $namespace = '', $enabled = true)
    {
        $this->adapter = $adapter;
        $this->namespace = $namespace;
        $this->enabled = $enabled;
    }

    public function get($key, $ttl = 0, callable $cacheSetter = null)
    {
        $r = null;

        if ($this->enabled) {
            $r = $this->adapter->get($this->namespace . '__' . $key);
            //"false" storing is not supported
            if ($r === false) {
                if ($cacheSetter === null) {
                    return null;
                }
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
    public function set($key, $value, $ttl = 0)
    {
        if ($value === false) {
            throw new \Exception('"false" storing is not supported');
        }

        $parsedTtl = $this->parseTtl($ttl);
        if ($parsedTtl != 0) {
            $parsedTtl += time();
        }

        $this->adapter->set($this->namespace . '__' . $key, $value, 0, $parsedTtl);
    }
    public function remove($key)
    {
        $this->adapter->delete($this->namespace . '__' . $key);
    }
    public function clean()
    {
        $this->adapter->flush();
    }
}
