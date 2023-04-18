# laravel-cacheable
Simple trait to easy cache per-method.


##How to use it:

> Note: This trait can be used in any class.

Features:
* Remember data from cache ( if exists returns or store )
* Custom cache ttl

---
##### Add use and pass in the psr6 cache item pool interface:

```php

use CacheableTrait\CacheableTrait;

class Controller
{
    use CacheableTrait;
    public function __construct(CacheItemPoolInterface $cache)
    {
      $this->setCache($cache);
    }
}
```
##### Call it where you need:
```php
    public function cacheableMethod( $cacheable_parameters )
    {
        $data = $this->remember(function(){
            return 'Cacheable data';
        });

        return response($data);
    }
```

##### (Optional) Configure TTL per-class
```php
    protected function getTTL(): DateInterval
    {
        return DateInterval::createFromDateString('1 day');
    }
```

##### (Optional) Implement your own key algorithm per-class
```php
    protected function generateCacheKey($data): string
    {
        return 'key';
    }
```

##### (Optional) Add an environment variable to the cache key per-class
```php
    protected function getCacheId($data): string
    {
        return $_ENV['RELEASE_VERSION'];
    }
```

##### (Optional) Decide if something should be cached per-class
```php
    protected function shouldCache(string $class, string $function, array $args): bool
    {
        return $args[0] === 'plz cache';
    }
```

##### Disable the cache
```php
    public function shouldDisableCaching(): void
    {
        $this->unsetCache();
    }
```