# cacheable-trait
Simple configurable trait to add caching per-method.

##How to use it:

---
##### Add use and pass in the psr6 cache item pool interface, hopefully using a 

```php

use SeanJA\CacheableTrait\CacheableTrait;

class Controller
{
    use CacheableTrait;
    public function __construct(CacheItemPoolInterface $cache)
    {
      $this->setCache($cache);
    }
}
```
##### Using it in a method:
```php
    public function cacheableMethod( $cacheable_parameters )
    {
        $data = $this->remember(function(){
            return 'Cacheable data';
        });

        return response($data);
    }
```

##### Configure TTL
Add a protected method called `getTTL` to your class that returns a date interval
```php
    protected function getTTL(string $function, array $args): DateInterval
    {
        return match ($function) {
            'method1' => DateInterval::createFromDateString('1 day'),
            'method2' => DateInterval::createFromDateString('10 seconds'),
            'method3' => DateInterval::createFromDateString('10 seconds'),
            default => DateInterval::createFromDateString('6 minutes'),
        };
    }
```

##### Implement your own key algorithm per-class
```php
    protected function generateCacheKey($data): string
    {
        return 'key';
    }
```

##### Add an environment variable to the cache key per-class
```php
    protected function getCacheId($data): string
    {
        return $_ENV['RELEASE_VERSION']; // or something else, I dunno
    }
```

##### Decide if something should be cached
```php
    protected function shouldCache(string $function, array $args): bool
    {
        return $args[0] === 'plz cache';
    }
```

##### Disable the cache (remember will do nothing because the cache is null)
```php
    public function shouldDisableCaching(): void
    {
        $this->unsetCache();
    }
```