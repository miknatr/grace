<?php

namespace Grace\Tests\Cache;

use Grace\Cache\CacheInterface;
use Grace\Cache\MemcacheAdapter;
use Grace\Cache\MemcachedAdapter;

class CacheInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function cacheProvider()
    {
        $data = array();

        if (class_exists('\\Memcached')) {
            $memcached = new \Memcached();
            $memcached->addServer('localhost', 11211);
            $data[] = array(new MemcachedAdapter($memcached, 'cache_test', true));
        };

        if (class_exists('\\Memcache')) {
            $memcache = new \Memcache();
            $memcache->addServer('localhost');
            $data[] = array(new MemcacheAdapter($memcache, 'cache_test', true));
        }

        return $data;
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testSimpleInterface(CacheInterface $cache)
    {
        $cache->clean();
        $this->assertEquals(null, $cache->get('foo'));

        $cache->set('foo', 'bar', 0);
        $this->assertEquals('bar', $cache->get('foo'));

        $cache->remove('foo');
        $this->assertEquals(null, $cache->get('foo'));
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testClosure(CacheInterface $cache)
    {
        $cache->clean();

        $isCalled = false;
        $value = $cache->get('foo', 0, function () use (&$isCalled) {
            $isCalled = true;
            return 'bar';
        });
        $this->assertEquals('bar', $value);
        $this->assertTrue($isCalled);

        $isCalled = false;
        $value = $cache->get('foo', 0, function () use (&$isCalled) {
            $isCalled = true;
            return 'zzz';
        });
        $this->assertEquals('bar', $value);
        $this->assertFalse($isCalled);
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testObjectEquality(CacheInterface $cache)
    {
        $cache->clean();

        $node = new \stdClass();
        $container = array($node, $node);
        $cache->set('foo', $container);

        $cachedContainer = $cache->get('foo');

        $this->assertNotEquals(spl_object_hash($container[0]), spl_object_hash($cachedContainer[0]));
        $this->assertEquals(spl_object_hash($cachedContainer[0]), spl_object_hash($cachedContainer[1]));
    }
}
