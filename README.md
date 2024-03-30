# cacheable-trait
Simple configurable trait to add caching per-method.

## How to use it:

---
##### Add use and pass in the psr6 cache item pool interface

```php
class Controller
{
    use SeanJA\Cache\CacheableTrait;
    
    public function __construct(CacheItemPoolInterface $cache)
    {
      $this->setCache($cache);
    }
}
```
##### Using it in a method:
By default, this value will be remembered for 1 hour
```php
    public function cacheableMethod( $cacheable_parameters )
    {
        $data = $this->remember(function(){
            return 'Cacheable data';
        });

        return response($data);
    }
```

##### Configure TTL (per class)
Add a protected method called `getTTL` to your class that returns a custom date interval based on the input
```php
    protected function getTTL(string $method, array $args): DateInterval
    {
        return match ($method) {
            'method1' => DateInterval::createFromDateString('1 day'),
            'method2' => DateInterval::createFromDateString('10 seconds'),
            'method3' => DateInterval::createFromDateString('10 seconds'),
            default => DateInterval::createFromDateString('6 minutes'),
        };
    }
```

##### Implement your own key (per class)
You can change the way the key is generated, it should be unique for each place you use remember otherwise you will
end up overwriting things in weird ways
```php
    protected function generateCacheKey(string $class, string $method, array $args): string
    {
        return 'key';
    }
```

##### Add a custom id to the cache (per class)
Add a custom value to the cache, can be used to bust the cache when you do a deploy, or you could set it manually to
bust the cache at any point.
```php
    protected function getCacheId(): string
    {
        return $_ENV['RELEASE_VERSION'];
    }
```

##### Decide if something should be cached (per class)
Can be used to avoid caching certain method calls
```php
    protected function shouldCache(string $method, array $args): bool
    {
        return $method === 'maybeCache', $args[0] === 'plz cache';
    }
```

##### Disable the cache (remember will now do nothing because the cache is null)
This will disable caching for the class until you restore it
```php
    public function shouldDisableCaching(): void
    {
        $this->disableCache();
    }
```

##### Disable the cache temporarily
If you really want to
```php
class CachedClass{
    use \SeanJA\Cache\CacheableTrait;
    
    public function cachedTime(){
        return $this->remember(function(){
            return time();
        });
    }

    public function uncachedTime(): void
    {
        $this->disableCache();
        $data = $this->cachedTime();
        $this->restoreCache();
        return $data; 
    }
}
```
