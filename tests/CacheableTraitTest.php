<?php

namespace Tests\SeanJA\Cache;

use SeanJA\Cache\CacheableTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheableTraitTest extends TestCase
{
    public function testWithoutCache()
    {
        $notCached = new class() {
            use CacheableTrait;

            public function __construct()
            {
                $this->setCache(null);
            }

            function getValue()
            {
                return $this->remember(function () {
                    return microtime(true);
                });
            }
        };

        $first = $notCached->getValue();
        sleep(1);
        $second = $notCached->getValue();

        $this->assertNotEquals($first, $second);
    }

    public function testWithCache()
    {
        $cache = new ArrayAdapter();

        $notCached = new class($cache) {
            use CacheableTrait;

            public function __construct(CacheItemPoolInterface $cache)
            {
                $this->setCache($cache);
            }

            function getValue()
            {
                return $this->remember(function () {
                    return microtime(true);
                });
            }
        };

        $first = $notCached->getValue();
        sleep(1);
        $second = $notCached->getValue();

        $this->assertEquals($first, $second);
    }

    public function testShouldCache()
    {
        $cache = new ArrayAdapter();

        $mightCache = new class($cache) {
            use CacheableTrait;

            public function __construct(CacheItemPoolInterface $cache)
            {
                $this->setCache($cache);
            }

            function getValue($arg1)
            {
                return $this->remember(function () {
                    return microtime(true);
                });
            }

            protected function shouldCache(string $method, array $args): bool
            {
                return $args[0] === 1;
            }
        };

        $first = $mightCache->getValue(1);
        sleep(1);
        $second = $mightCache->getValue(1);
        $third = $mightCache->getValue(2);

        $this->assertEquals($first, $second);
        $this->assertNotEquals($first, $third);
    }

    public function testUnsetCache()
    {
        $cache = new ArrayAdapter();

        $willNotCache = new class($cache) {
            use CacheableTrait;

            public function __construct(CacheItemPoolInterface $cache)
            {
                $this->setCache($cache);
            }

            function getValue($arg1)
            {
                $this->disableCache();
                return $this->remember(function () {
                    return microtime(true);
                });
            }

            protected function shouldCache(string $method, array $args): bool
            {
                return $args[0] === 1;
            }
        };

        $first = $willNotCache->getValue(1);
        sleep(1);
        $second = $willNotCache->getValue(1);

        $this->assertNotEquals($first, $second);
    }

    public function testRestoreCache()
    {
        $cache = new ArrayAdapter();

        $class = new class($cache) {
            use CacheableTrait;

            public function __construct(CacheItemPoolInterface $cache)
            {
                $this->setCache($cache);
            }

            function getValue()
            {
                return $this->remember(function () {
                    return microtime(true);
                });
            }

            function getValueNoCache()
            {
                $this->disableCache();
                $result = $this->getValue();
                $this->restoreCache();
                return $result;
            }
        };

        $first = $class->getValue();
        sleep(1);
        $second = $class->getValueNoCache();
        $third = $class->getValue();

        $this->assertNotEquals($first, $second);
        $this->assertEquals($first, $third);
    }
    public function testNoClosuresPlease()
    {
        $cache = new ArrayAdapter();

        $class = new class($cache) {
            use CacheableTrait;

            public function __construct(CacheItemPoolInterface $cache)
            {
                $this->setCache($cache);
            }

            function getValue()
            {
                return $this->remember(function () {
                    return microtime(true);
                });
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $class->getValue(function () {
            return 'what';
        });
    }
}
