<?php

namespace SeanJA\Cache;

use Closure;
use DateInterval;
use InvalidArgumentException;
use Psr\Cache\CacheItemPoolInterface;

trait CacheableTrait
{
    /**
     * @var CacheItemPoolInterface|null
     */
    private CacheItemPoolInterface|null $cache = null;

    /**
     * @var CacheItemPoolInterface|null
     */
    private CacheItemPoolInterface|null $previousCache = null;

    /**
     * Hashing algorithm for the key.
     * @var string
     */
    protected string $hashAlgo = 'sha256';

    /**
     * @param CacheItemPoolInterface|null $cache
     * @return void
     */
    protected function setCache(?CacheItemPoolInterface $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Disable cache
     * @return void
     */
    protected function disableCache(): void
    {
        $this->previousCache = $this->cache;
        $this->setCache(null);
    }

    /**
     * Restore the cache
     * @return void
     */
    protected function restoreCache(): void
    {
        if($this->previousCache){
            $this->setCache($this->previousCache);
            $this->previousCache = null;
        }
    }

    /**
     * Determine if something should be cacheable or not (default to true)
     * @param string $method
     * @param array $args
     * @return bool
     */
    protected function shouldCache(string $method, array $args): bool
    {
        return true;
    }

    /**
     * Set a custom cache id
     * @return string
     */
    protected function getCacheId(): string
    {
        return '00000000-0000-0000-0000-000000000000';
    }

    /**
     * @param Closure $function
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function remember(Closure $function): mixed
    {
        if (!$this->cache) {
            return $function();
        }

        $previous_call = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2)[1];

        $data = [
            'class' => $previous_call['class'],
            'method' => $previous_call['function'],
            'args' => $previous_call['args'],
        ];

        if (!$this->shouldCache($data['method'], $data['args'])) {
            return $function();
        }

        $key = $this->generateCacheKey($data['class'], $data['method'], $data['args']);

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set($function());
            $item->expiresAfter($this->getTTL($data['method'], $data['args']));
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     * Determine the time to cache the data (default 1 hour)
     * @param string $method
     * @param array $args
     * @return DateInterval
     */
    protected function getTTL(string $method, array $args): DateInterval
    {
        return DateInterval::createFromDateString('1 hour');
    }

    /**
     * Check if we can serialize this data
     * @param $arguments
     * @return void
     * @throws InvalidArgumentException
     */
    private function checkArguments($arguments): void
    {
        foreach ($arguments as $arg) {
            if ($arg instanceof Closure) {
                throw new InvalidArgumentException("Closure can't be serialized");
            }
        }
    }

    /**
     * Generate a cache key based on the input
     * @param string $class
     * @param string $method
     * @param array $args
     * @return string
     */
    protected function generateCacheKey(string $class, string $method, array $args): string
    {
        $this->checkArguments($args);

        return hash($this->hashAlgo, serialize([
            $class,
            $method,
            $args,
            $this->getCacheId()
        ]));
    }
}