<?php

namespace Grace\Tests\Cache;

use Grace\Cache\CacheInterface;
use Grace\Cache\MemcacheAdapter;
use Grace\Cache\MemcachedAdapter;

class CacheInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function cacheProvider()
    {
        $md = new \Memcached();
        $md->addServer('localhost', 11211);
        $m = new \Memcache();
        $m->addServer('localhost', 11211);

        return array(
            array(new MemcachedAdapter($md, 'cache_test', true)),
            array(new MemcacheAdapter($m, 'cache_test', true)),
        );
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
}
