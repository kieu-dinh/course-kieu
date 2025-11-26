# Caching Strategies

**Duration**: 4-5 hours

---

## Introduction

The fastest query is the one you don't have to run. Caching stores frequently accessed data in fast storage (like memory) so you don't have to fetch it from the database every time. A well-implemented caching strategy can reduce page load times from seconds to milliseconds.

**What you'll learn:**
- Different cache drivers (file, Redis, Memcached)
- When and what to cache
- Cache invalidation strategies
- Laravel's caching features
- Real-world caching patterns

---

## Understanding Caching

### What Is Caching?

Caching is storing data in a fast-access location (usually memory) so you can retrieve it quickly without hitting the database.

**Without cache:**
```
User Request → Laravel → Database Query (50ms) → Process → Response
```

**With cache:**
```
User Request → Laravel → Cache (1ms) → Response
```

### Cache Hierarchy

Different storage types have different speeds:

1. **Memory (RAM)**: Fastest (< 1ms) - Redis, Memcached
2. **SSD**: Fast (5-10ms) - File cache
3. **Hard Drive**: Slower (20-50ms) - Database cache
4. **Database**: Slowest (50-200ms) - Original data

### When to Use Caching

**Good candidates for caching:**
- Data that changes infrequently (settings, configurations)
- Expensive computations (reports, analytics)
- API responses from external services
- Database query results
- Rendered views or HTML fragments
- Session data

**Bad candidates for caching:**
- Real-time data (live prices, inventory)
- User-specific data that changes frequently
- Data that must always be fresh
- Large objects (> 1MB)

---

## Cache Drivers in Laravel

### Available Drivers

Laravel supports multiple cache drivers:

**1. File Cache** (default)
- Stores cache in `storage/framework/cache`
- Good for: Development, small apps
- Pros: No setup, works everywhere
- Cons: Slower, not shared across servers

**2. Redis** (recommended)
- In-memory data store
- Good for: Production, multi-server setups
- Pros: Very fast, persistent, supports advanced features
- Cons: Requires Redis server

**3. Memcached**
- In-memory cache system
- Good for: High-traffic applications
- Pros: Very fast, distributed
- Cons: Not persistent (data lost on restart)

**4. Database**
- Stores in database table
- Good for: Shared hosting, when no Redis available
- Pros: Persistent, shared across servers
- Cons: Slower than memory

**5. Array** (for testing)
- Only exists during the request
- Good for: Unit tests
- Cons: Not persistent

### Configuring Cache Driver

**config/cache.php:**
```php
'default' => env('CACHE_DRIVER', 'file'),

'stores' => [
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],

    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],

    'database' => [
        'driver' => 'database',
        'table' => 'cache',
        'connection' => null,
    ],
],
```

**.env:**
```
CACHE_DRIVER=redis
```

### Setting Up Redis (Recommended)

**1. Install Redis on Mac with Herd:**
Redis is included with Laravel Herd - just start it from the Herd menu.

**2. Install PHP Redis extension:**
Herd includes this by default.

**3. Configure Laravel:**
```
# .env
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis

# For sessions too
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

**4. Test connection:**
```bash
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
```

---

## Basic Cache Operations

### Storing Data

**Simple storage:**
```php
use Illuminate\Support\Facades\Cache;

// Store for 60 seconds
Cache::put('key', 'value', 60);

// Store for specific time
Cache::put('key', 'value', now()->addMinutes(10));
Cache::put('key', 'value', now()->addHours(24));

// Store forever (until manually deleted)
Cache::forever('key', 'value');

// Store if doesn't exist
Cache::add('key', 'value', 60); // Returns false if exists
```

### Retrieving Data

**Basic retrieval:**
```php
// Get value
$value = Cache::get('key');

// Get with default
$value = Cache::get('key', 'default');

// Get with closure as default
$value = Cache::get('key', function () {
    return DB::table('settings')->get();
});
```

**Retrieve and store (cache if missing):**
```php
$value = Cache::remember('users', 60, function () {
    return User::all();
});
```

**Retrieve and store forever:**
```php
$value = Cache::rememberForever('settings', function () {
    return Setting::pluck('value', 'key');
});
```

**Pull (retrieve and delete):**
```php
$value = Cache::pull('key'); // Get and remove
```

### Checking Existence

```php
if (Cache::has('key')) {
    // Key exists
}

if (Cache::missing('key')) {
    // Key doesn't exist
}
```

### Deleting Data

```php
// Delete single key
Cache::forget('key');

// Delete multiple keys
Cache::delete(['key1', 'key2', 'key3']);

// Clear all cache
Cache::flush();
```

### Incrementing/Decrementing

```php
// Increment
Cache::increment('counter');
Cache::increment('counter', 5); // By 5

// Decrement
Cache::decrement('counter');
Cache::decrement('counter', 3); // By 3
```

---

## Practical Caching Patterns

### Pattern 1: Caching Database Queries

**Without cache:**
```php
public function index()
{
    $posts = Post::with('user')
        ->published()
        ->latest()
        ->paginate(20);

    return view('posts.index', compact('posts'));
}
// Every page load: database query
```

**With cache:**
```php
public function index()
{
    $page = request('page', 1);

    $posts = Cache::remember("posts:page:{$page}", 600, function () {
        return Post::with('user')
            ->published()
            ->latest()
            ->paginate(20);
    });

    return view('posts.index', compact('posts'));
}
// First load: database query
// Next 10 minutes: cached result
```

### Pattern 2: Caching Expensive Computations

```php
public function stats()
{
    $stats = Cache::remember('dashboard:stats', 3600, function () {
        return [
            'total_users' => User::count(),
            'total_posts' => Post::count(),
            'total_revenue' => Order::sum('total'),
            'avg_order_value' => Order::avg('total'),
            'popular_posts' => Post::withCount('views')
                ->orderBy('views_count', 'desc')
                ->limit(10)
                ->get(),
        ];
    });

    return view('dashboard', compact('stats'));
}
```

### Pattern 3: Caching Settings/Configuration

```php
// SettingService.php
class SettingService
{
    public function get($key, $default = null)
    {
        $settings = Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public function set($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        // Invalidate cache
        Cache::forget('settings');
    }
}

// Usage
$siteName = app(SettingService::class)->get('site_name');
```

### Pattern 4: Caching API Responses

```php
public function getWeather($city)
{
    return Cache::remember("weather:{$city}", 1800, function () use ($city) {
        $response = Http::get("https://api.weather.com/v1/forecast", [
            'city' => $city,
            'apikey' => config('services.weather.key'),
        ]);

        return $response->json();
    });
}
```

### Pattern 5: User-Specific Caching

```php
public function dashboard()
{
    $userId = auth()->id();

    $data = Cache::remember("user:{$userId}:dashboard", 600, function () use ($userId) {
        return [
            'posts' => Post::where('user_id', $userId)->count(),
            'comments' => Comment::where('user_id', $userId)->count(),
            'followers' => Follow::where('following_id', $userId)->count(),
            'recent_activity' => Activity::where('user_id', $userId)
                ->latest()
                ->limit(10)
                ->get(),
        ];
    });

    return view('dashboard', compact('data'));
}
```

---

## Cache Invalidation

The hardest problem in computer science: "There are only two hard things in Computer Science: cache invalidation and naming things."

### When to Invalidate

Invalidate cache when:
1. **Data changes** (create, update, delete)
2. **User performs action** (likes, follows)
3. **Time-based** (daily reports)
4. **Manual** (admin clears cache)

### Strategy 1: Manual Invalidation

```php
// When creating a post
public function store(Request $request)
{
    $post = Post::create($request->validated());

    // Invalidate posts cache
    Cache::forget('posts:page:1');
    Cache::forget('posts:page:2');
    // ... need to clear all pages!

    return redirect()->route('posts.show', $post);
}
```

**Problem:** Hard to know which keys to invalidate.

### Strategy 2: Cache Tags (Redis/Memcached only)

```php
// Storing with tags
Cache::tags(['posts', 'homepage'])->put('posts:latest', $posts, 3600);
Cache::tags(['posts'])->put('posts:popular', $popular, 3600);
Cache::tags(['posts', 'user:1'])->put('user:1:posts', $userPosts, 3600);

// Invalidate all posts cache
Cache::tags(['posts'])->flush();

// Invalidate only homepage cache
Cache::tags(['homepage'])->flush();
```

**Real example:**
```php
public function index()
{
    $posts = Cache::tags(['posts', 'posts:index'])->remember('posts:list', 600, function () {
        return Post::with('user')->published()->latest()->get();
    });

    return view('posts.index', compact('posts'));
}

public function store(Request $request)
{
    $post = Post::create($request->validated());

    // Invalidate all post-related cache
    Cache::tags(['posts'])->flush();

    return redirect()->route('posts.show', $post);
}
```

### Strategy 3: Model Events

Automatically invalidate cache when models change:

```php
// Post model
protected static function booted()
{
    static::created(function ($post) {
        Cache::tags(['posts'])->flush();
    });

    static::updated(function ($post) {
        Cache::tags(['posts'])->flush();
        Cache::forget("post:{$post->id}");
    });

    static::deleted(function ($post) {
        Cache::tags(['posts'])->flush();
        Cache::forget("post:{$post->id}");
    });
}
```

### Strategy 4: Observer Pattern

More organized approach using observers:

```php
// app/Observers/PostObserver.php
class PostObserver
{
    public function created(Post $post)
    {
        $this->clearCache();
    }

    public function updated(Post $post)
    {
        $this->clearCache();
        Cache::forget("post:{$post->id}");
    }

    public function deleted(Post $post)
    {
        $this->clearCache();
        Cache::forget("post:{$post->id}");
    }

    protected function clearCache()
    {
        Cache::tags(['posts'])->flush();
    }
}

// AppServiceProvider.php
public function boot()
{
    Post::observe(PostObserver::class);
}
```

### Strategy 5: Time-Based Expiration

Let cache expire naturally:

```php
// Cache for 10 minutes - accept slightly stale data
$posts = Cache::remember('posts:latest', 600, function () {
    return Post::latest()->limit(10)->get();
});

// Cache longer for less critical data
$stats = Cache::remember('stats:monthly', 3600, function () {
    return Order::whereMonth('created_at', now()->month)->sum('total');
});
```

**Good for:** Data where being slightly out of date is acceptable.

---

## Advanced Caching Techniques

### 1. Cache Locks (Prevent Cache Stampede)

**The problem:** 1000 users hit uncached page at once, all try to rebuild cache simultaneously.

```php
// ❌ Bad: Cache stampede
$value = Cache::remember('expensive', 3600, function () {
    sleep(5); // Simulating expensive operation
    return 'result';
});
// All 1000 users run this expensive operation!
```

```php
// ✅ Good: Use cache lock
$value = Cache::lock('expensive:lock')->get(function () {
    return Cache::remember('expensive', 3600, function () {
        sleep(5);
        return 'result';
    });
});
// Only first user runs operation, others wait for result
```

**Better: Block with timeout:**
```php
$lock = Cache::lock('expensive:lock', 10);

if ($lock->get()) {
    try {
        $value = Cache::remember('expensive', 3600, function () {
            // Expensive operation
            return 'result';
        });
    } finally {
        $lock->release();
    }
}
```

### 2. Atomic Operations

```php
// Increment safely (won't have race conditions)
Cache::increment('page:views');

// Add only if doesn't exist (returns false if exists)
$added = Cache::add('key', 'value', 60);
```

### 3. Cache Warming

Pre-fill cache before users need it:

```php
// Console/Commands/WarmCache.php
class WarmCache extends Command
{
    protected $signature = 'cache:warm';

    public function handle()
    {
        $this->info('Warming cache...');

        // Popular posts
        Cache::tags(['posts'])->remember('posts:popular', 3600, function () {
            return Post::withCount('views')
                ->orderBy('views_count', 'desc')
                ->limit(20)
                ->get();
        });

        // Latest posts
        Cache::tags(['posts'])->remember('posts:latest', 600, function () {
            return Post::latest()->limit(10)->get();
        });

        // Settings
        Cache::rememberForever('settings', function () {
            return Setting::pluck('value', 'key');
        });

        $this->info('Cache warmed!');
    }
}
```

Run after deployment:
```bash
php artisan cache:clear
php artisan cache:warm
```

### 4. Fragment Caching in Views

Cache portions of views:

```blade
{{-- Cache sidebar for 1 hour --}}
@cache('sidebar', 3600)
    <div class="sidebar">
        <h3>Popular Posts</h3>
        @foreach(Post::popular()->limit(5)->get() as $post)
            <a href="{{ route('posts.show', $post) }}">
                {{ $post->title }}
            </a>
        @endforeach
    </div>
@endcache
```

**Create the directive (AppServiceProvider):**
```php
use Illuminate\Support\Facades\Blade;

public function boot()
{
    Blade::directive('cache', function ($expression) {
        return "<?php if(! cache()->has($expression)) : cache()->put($expression, true, 3600); ?>";
    });

    Blade::directive('endcache', function () {
        return "<?php endif; ?>";
    });
}
```

### 5. Query Result Caching

Cache specific query results:

```php
// In your model
public function scopePopular($query)
{
    return Cache::tags(['posts'])->remember('posts:popular', 3600, function () use ($query) {
        return $query->withCount('views')
            ->orderBy('views_count', 'desc')
            ->limit(20)
            ->get();
    });
}

// Usage
$popular = Post::popular()->get();
```

---

## Cache Best Practices

### 1. Use Descriptive Keys

```php
// ❌ Bad
Cache::put('p', $posts, 60);
Cache::put('data', $data, 60);

// ✅ Good
Cache::put('posts:latest:page:1', $posts, 60);
Cache::put('user:5:profile', $profile, 60);
Cache::put('stats:dashboard:monthly', $stats, 60);
```

**Key naming convention:**
```
{entity}:{identifier}:{variant}
posts:123:full
posts:latest:summary
user:5:posts
user:5:followers:count
```

### 2. Set Appropriate TTL

```php
// Very dynamic - 5 minutes
Cache::remember('posts:latest', 300, fn() => Post::latest()->get());

// Semi-static - 1 hour
Cache::remember('posts:popular', 3600, fn() => Post::popular()->get());

// Rarely changes - 24 hours
Cache::remember('categories:all', 86400, fn() => Category::all());

// Almost never changes - forever
Cache::rememberForever('settings', fn() => Setting::all());
```

### 3. Don't Cache Everything

```php
// ❌ Bad: Caching user-specific, rapidly changing data
Cache::remember('cart:' . $userId, 600, function () use ($userId) {
    return Cart::where('user_id', $userId)->get();
});

// ✅ Good: Use sessions for user-specific data
session()->put('cart', $cartItems);
```

### 4. Monitor Cache Hit Ratio

```php
// Track hits vs misses
public function getCachedData($key)
{
    if (Cache::has($key)) {
        Log::debug("Cache HIT: {$key}");
        return Cache::get($key);
    }

    Log::debug("Cache MISS: {$key}");
    $data = $this->fetchData();
    Cache::put($key, $data, 600);
    return $data;
}
```

**Good hit ratio:** > 80%
**Poor hit ratio:** < 50% (might need different TTL or strategy)

### 5. Use Cache for Rate Limiting

```php
public function attemptLogin(Request $request)
{
    $key = 'login:attempts:' . $request->ip();

    $attempts = Cache::get($key, 0);

    if ($attempts >= 5) {
        return back()->with('error', 'Too many attempts. Try again in 15 minutes.');
    }

    // Try login
    if (Auth::attempt($credentials)) {
        Cache::forget($key);
        return redirect()->dashboard();
    }

    // Increment attempts
    Cache::put($key, $attempts + 1, now()->addMinutes(15));

    return back()->with('error', 'Invalid credentials.');
}
```

---

## Testing with Cache

### Fake Cache in Tests

```php
use Illuminate\Support\Facades\Cache;

public function test_posts_are_cached()
{
    Cache::shouldReceive('remember')
        ->once()
        ->with('posts:latest', 600, Closure::class)
        ->andReturn(collect([...]));

    $response = $this->get('/posts');

    $response->assertSuccessful();
}
```

### Use Array Driver in Tests

```php
// phpunit.xml
<env name="CACHE_DRIVER" value="array"/>
```

Or in specific test:
```php
public function setUp(): void
{
    parent::setUp();
    config(['cache.default' => 'array']);
}
```

---

## Common Pitfalls

### 1. Forgetting to Invalidate

```php
// ❌ Bad: Update without invalidating
public function update(Request $request, Post $post)
{
    $post->update($request->validated());
    // Cache still has old data!

    return redirect()->back();
}

// ✅ Good
public function update(Request $request, Post $post)
{
    $post->update($request->validated());
    Cache::forget("post:{$post->id}");
    Cache::tags(['posts'])->flush();

    return redirect()->back();
}
```

### 2. Caching User-Specific Data Globally

```php
// ❌ Bad: All users see the same cached data
Cache::remember('dashboard', 600, function () {
    return auth()->user()->dashboard();
});

// ✅ Good: Include user ID in key
$userId = auth()->id();
Cache::remember("dashboard:user:{$userId}", 600, function () use ($userId) {
    return User::find($userId)->dashboard();
});
```

### 3. Not Handling Cache Failures

```php
// ❌ Bad: Assumes cache always works
$data = Cache::get('critical:data');
$data->process(); // Fatal error if cache miss!

// ✅ Good: Always have fallback
$data = Cache::get('critical:data') ?? $this->fetchData();
```

---

## Quick Reference

```php
// Store
Cache::put('key', 'value', 60);
Cache::forever('key', 'value');
Cache::add('key', 'value', 60); // Only if doesn't exist

// Retrieve
Cache::get('key');
Cache::get('key', 'default');
Cache::remember('key', 60, fn() => DB::table('users')->get());

// Check
Cache::has('key');
Cache::missing('key');

// Delete
Cache::forget('key');
Cache::flush();

// Tags (Redis only)
Cache::tags(['posts'])->put('key', 'value', 60);
Cache::tags(['posts'])->flush();

// Locks
Cache::lock('key', 10)->get(fn() => ...);

// Increment
Cache::increment('counter');
Cache::decrement('counter');
```

---

## Practice Questions

1. **When should you use caching?** Give three examples.

2. **What's the difference between File and Redis cache?**

3. **What is cache invalidation?** Why is it challenging?

4. **What are cache tags?** When would you use them?

5. **What is a cache stampede?** How do you prevent it?

6. **Should you cache user-specific data?** How would you do it?

---

## Next Steps

Now you understand caching! Next, you'll learn about queue jobs for handling time-consuming tasks in the background.

**Coming up in Lesson 04:**
- Queue jobs and workers
- Background processing
- Job queues (sync, database, Redis)
- Failed jobs and retries
