# laravel-cacheable
Simple trait to easy cache per-method in Laravel.


##How to use it:

> Note: This trait can be used in any class.

Features:
* Remember data from cache ( if exists returns or store )
* User Laravel Default TTL time
* Per-method cache level
* Cache become unreachable after a deploy. Auto-purge.

---
1. #### Add the 'use' clause:

```
use App\Traits\Cacheable;

class Controller
{
    use Cacheable;
}
```
2. #### Add this line to  `config/cache.php` to be able to read the Environment variable after a deploy
```
    ...
    'commit' => env('GIT_COMMIT', null),
    ...
```
3. #### Call it where you need:
```
    public function cacheableMethod( $cacheable_parameters )
    {
        $data = $this->remember(function(){
            return 'Cacheable data';
        });

        return response($data);
    }
```

4. #### (Optional) Configure TTL per-class in minutes
```
    protected function getTTL()
    {
        return 10;
    }
```

5. #### (Optional) Implement your own per-class generation key algorithm.
```
    protected function generateCacheKey($data)
    {
        return 'key';
    }
```




