# Exercise 20.2 - Caching Strategies

## Objective

Learn to implement effective caching strategies to improve application performance.

---

## Task

Implement caching at different levels of your application.

### Requirements

1. **Query Caching**
   - Cache expensive database queries
   - Implement cache keys with versioning
   - Set appropriate TTL

2. **View Caching**
   - Cache rendered views or components
   - Invalidate on model changes
   - Use cache busting strategies

3. **API Response Caching**
   - Cache API responses
   - Use HTTP caching headers
   - Implement conditional requests

4. **Cache Invalidation**
   - Invalidate on model updates
   - Use model observers
   - Implement cache tagging

5. **Cache Backends**
   - Use Redis for distributed caching
   - Use Memcached for high-throughput
   - Use File cache for development
   - Use Database cache for small projects

---

## Setup

```bash
# Install Redis (on macOS)
brew install redis

# Start Redis
redis-server

# Or use Docker
docker run -d -p 6379:6379 redis:alpine

# Update .env
CACHE_DRIVER=redis
```

---

## Query Caching

### Basic Query Caching

```php
// Cache a query result for 1 hour
$items = Cache::remember('items.all', 3600, function() {
    return Item::with('author')
        ->where('status', 'active')
        ->orderBy('created_at', 'desc')
        ->get();
});

// With dynamic key
$userId = auth()->id();
$userItems = Cache::remember("items.user.{$userId}", 1800, function() use ($userId) {
    return Item::where('user_id', $userId)
        ->with('author')
        ->get();
});

// Cache with tags for grouping
$items = Cache::tags(['items', 'products'])->remember('items.featured', 3600, function() {
    return Item::where('featured', true)->get();
});
```

### Cache Invalidation on Save

```php
// app/Models/Item.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected static function booted()
    {
        static::created(function ($model) {
            Cache::forget('items.all');
            Cache::tags(['items'])->flush();
        });

        static::updated(function ($model) {
            Cache::forget('items.all');
            Cache::forget("items.item.{$model->id}");
            Cache::tags(['items'])->flush();
        });

        static::deleted(function ($model) {
            Cache::forget('items.all');
            Cache::forget("items.item.{$model->id}");
            Cache::tags(['items'])->flush();
        });
    }
}
```

---

## API Response Caching

### Middleware for HTTP Caching

```php
// app/Http/Middleware/HttpCacheMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HttpCacheMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only cache GET requests
        if ($request->isMethod('get')) {
            $response->header('Cache-Control', 'public, max-age=3600'); // 1 hour
            $response->header('ETag', hash('md5', $response->content()));
        }

        return $response;
    }
}

// Register in kernel
protected $routeMiddleware = [
    'cache.http' => \App\Http\Middleware\HttpCacheMiddleware::class,
];
```

### Caching in Controller

```php
// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);

        // Create cache key
        $cacheKey = "items.page.{$page}.per_page.{$perPage}";

        // Get cached response or generate new
        $items = Cache::remember($cacheKey, 3600, function() use ($perPage) {
            return Item::with('author')
                ->paginate($perPage);
        });

        return ItemCollection::make($items);
    }

    public function show(Item $item)
    {
        // Cache single item
        $cached = Cache::remember("items.show.{$item->id}", 1800, function() use ($item) {
            return new ItemResource($item->load('author', 'comments'));
        });

        return $cached;
    }
}
```

---

## Cache Tagging

Useful for invalidating related caches together:

```php
// Cache with tags
Cache::tags(['items', 'products'])->put('items.list', $items, 3600);

// Retrieve from tagged cache
$items = Cache::tags(['items', 'products'])->get('items.list');

// Flush all caches with tag
Cache::tags(['items'])->flush(); // Removes all 'items' tagged caches

// In model observer
class ItemObserver
{
    public function updated(Item $item)
    {
        // Invalidate related caches
        Cache::tags(['items', 'categories'])->flush();
        Cache::tags(['user.' . $item->user_id])->flush();
    }
}
```

---

## Cache Strategies

### Strategy 1: Time-Based (TTL)

```php
// Cache for fixed duration
Cache::put('key', $value, 3600); // 1 hour

// Cache until specific time
Cache::until('key', $value, now()->addHours(2));

// Remember pattern
Cache::remember('key', 3600, function() {
    return expensiveOperation();
});
```

### Strategy 2: Event-Based (Invalidation)

```php
// Register observer
Item::observe(ItemObserver::class);

// Observer invalidates cache on changes
class ItemObserver
{
    public function created(Item $item) { Cache::forget('items.all'); }
    public function updated(Item $item) { Cache::forget('items.all'); }
    public function deleted(Item $item) { Cache::forget('items.all'); }
}
```

### Strategy 3: Conditional (Smart Cache)

```php
// Different cache duration based on time
public function getHotItems()
{
    $hour = now()->hour;

    // Cache longer during off-peak hours
    $duration = ($hour >= 9 && $hour <= 17) ? 600 : 3600;

    return Cache::remember('items.hot', $duration, function() {
        return Item::where('views', '>', 1000)->get();
    });
}
```

### Strategy 4: Layered Cache

```php
// Try multiple caches in order
public function getItem($id)
{
    // Try Redis
    $item = Cache::store('redis')->get("item.{$id}");

    if (!$item) {
        // Fall back to File cache
        $item = Cache::store('file')->get("item.{$id}");
    }

    if (!$item) {
        // Fetch from DB
        $item = Item::find($id);

        // Store in both caches
        Cache::store('redis')->put("item.{$id}", $item, 3600);
        Cache::store('file')->put("item.{$id}", $item, 3600);
    }

    return $item;
}
```

---

## Cache Key Generation

```php
// Consistent cache keys
class CacheKeyGenerator
{
    public static function itemsList($page = 1, $filters = []): string
    {
        $hash = hash('md5', json_encode($filters));
        return "items.list.page.{$page}.{$hash}";
    }

    public static function userItems($userId, $page = 1): string
    {
        return "user.{$userId}.items.page.{$page}";
    }

    public static function categoryItems($categoryId, $sortBy = 'newest'): string
    {
        return "category.{$categoryId}.items.{$sortBy}";
    }
}

// Usage
$key = CacheKeyGenerator::itemsList(1, ['status' => 'active']);
$items = Cache::remember($key, 3600, function() { /* ... */ });
```

---

## Testing Cache

```php
// tests/Feature/CachingTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CachingTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_are_cached()
    {
        Item::factory(10)->create();

        // First call - hits database
        $items1 = Cache::remember('items.test', 3600, function() {
            return Item::all();
        });

        // Second call - from cache
        $items2 = Cache::remember('items.test', 3600, function() {
            return Item::all();
        });

        $this->assertEquals($items1->count(), $items2->count());
    }

    public function test_cache_is_invalidated_on_create()
    {
        Cache::put('items.all', Item::all(), 3600);

        Item::factory()->create();

        // Cache should be invalidated
        $this->assertFalse(Cache::has('items.all'));
    }

    public function test_tagged_cache_can_be_flushed()
    {
        Cache::tags(['items'])->put('key1', 'value1', 3600);
        Cache::tags(['items'])->put('key2', 'value2', 3600);

        Cache::tags(['items'])->flush();

        $this->assertFalse(Cache::tags(['items'])->has('key1'));
        $this->assertFalse(Cache::tags(['items'])->has('key2'));
    }
}
```

---

## Checklist

- [ ] Choose cache driver (Redis, File, etc.)
- [ ] Implement query caching
- [ ] Add cache invalidation
- [ ] Use cache tags
- [ ] Set appropriate TTL values
- [ ] Test cache behavior
- [ ] Monitor cache hits/misses
- [ ] Document cache strategy
- [ ] Handle cache failures gracefully

---

## Performance Improvements

With proper caching:
- Reduce database queries by 80%
- Decrease response time by 60%
- Reduce server load significantly
- Improve user experience

---

## Bonus Challenges

1. Implement cache warming (pre-populate cache)
2. Create cache statistics dashboard
3. Implement atomic cache updates
4. Add cache compression for large values
5. Implement distributed caching with multiple servers
6. Monitor cache performance and set alerts

---

## Solution Check

Test caching implementation:
```bash
php artisan test tests/Feature/CachingTest.php
```

Monitor cache:
```bash
# Check Redis
redis-cli
> KEYS *
> DBSIZE
> FLUSHALL
```
