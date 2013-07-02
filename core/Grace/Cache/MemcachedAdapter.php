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

    public function get($key, $ttl = 0, callable $cacheSetter = null)
    {
        $r = null;

        if ($this->enabled) {
            $r = $this->adapter->get($this->formatKey($key));
            $resultCode = $this->adapter->getResultCode();

            if ($resultCode === \Memcached::RES_NOTFOUND) {
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
        $parsedTtl = $this->parseTtl($ttl);
        if ($parsedTtl != 0) {
            $parsedTtl += time();
        }

        $r = $this->adapter->set($this->formatKey($key), $value, $parsedTtl);
        if (!$r) {
            throw new \Exception('Memcached error: ' . $this->adapter->getResultCode() . ' ' . $this->adapter->getResultMessage() );
        }
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
