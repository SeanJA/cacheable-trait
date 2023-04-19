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
    private CacheItemPoolInterface|null $cache;

    /**
     * @var CacheItemPoolInterface|null
     */
    private CacheItemPoolInterface|null $previousCache;

    /**
     * Hashing algorithm for the key.
     * @var string
     */
    protected string $hash_algo = 'sha256';

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
    protected function unsetCache(): void
    {
        $this->setCache(null);
    }

    /**
     * Determine if something should be cacheable or not (default to true)
     * @param string $function
     * @param array $args
     * @return bool
     */
    protected function shouldCache(string $function, array $args): bool
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
            'function' => $previous_call['function'],
            'args' => $previous_call['args'],
        ];

        if (!$this->shouldCache($data['function'], $data['args'])) {
            return $function();
        }

        $key = $this->generateCacheKey($data);

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $item->set($function());
            $item->expiresAfter($this->getTTL($data['function'], $data['args']));
            $this->cache->save($item);
        }

        return $item->get();
    }

    /**
     * Determine the time to cache the data (default 1 hour)
     * @param string $function
     * @param array $args
     * @return DateInterval
     */
    protected function getTTL(string $function, array $args): DateInterval
    {
        return DateInterval::createFromDateString('1 hour');
    }

    /**
     * Check if we can serialize this data
     * @param $arguments
     * @throws InvalidArgumentException
     * @return void
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
     * @param array $data
     * @return string
     */
    protected function generateCacheKey(array $data): string
    {
        $this->checkArguments($data['args']);

        return hash($this->hash_algo, serialize([
            $data['class'],
            $data['function'],
            $data['args'],
            $this->getCacheId()
        ]));
    }
}