# Lesson 5: Rate Limiting APIs

**Duration**: 2-3 hours
**Prerequisites**: Lessons 1-4 (API Routes, Resources, Authentication, Versioning)
**Objective**: Protect your APIs from abuse using rate limiting strategies

---

## Introduction

Imagine someone writes a script that makes 10,000 requests per second to your API. Your server crashes, legitimate users can't access it, and your hosting bill skyrockets.

**Rate limiting** prevents this by restricting how many requests a user can make in a time window.

Think of it like a speed limit on a highway: it prevents individual drivers from going too fast and endangering everyone else.

### Why Rate Limiting?

1. **Prevent abuse**: Stop malicious users from overwhelming your server
2. **Fair resource allocation**: Ensure all users get fair access
3. **Protect infrastructure**: Prevent server overload and crashes
4. **Control costs**: Limit bandwidth and database usage
5. **Encourage paid plans**: Free tier has limits, paid has more

### Common Rate Limit Examples

| Service | Free Tier | Paid Tier |
|---------|-----------|-----------|
| GitHub API | 60 requests/hour (unauthenticated) | 5,000 requests/hour |
| Twitter API | 15 requests/15min | Higher based on plan |
| Stripe API | No fixed limit but monitored | Same, but higher tolerance |

---

## Laravel's Built-in Rate Limiting

Laravel includes powerful rate limiting out of the box. It's defined in `app/Providers/RouteServiceProvider.php` or `bootstrap/app.php` (Laravel 11).

### Default Rate Limiter

By default, Laravel applies this to API routes:

```php
Route::middleware(['throttle:api'])->group(function () {
    // Your API routes
});
```

The `throttle:api` middleware uses the `api` limiter defined in `RouteServiceProvider`.

**Default limit**: 60 requests per minute per user (or per IP if not authenticated).

### Understanding the Syntax

```php
'throttle:60,1'
```

- `60` = 60 requests
- `1` = per 1 minute

```php
'throttle:100,1'  // 100 requests per minute
'throttle:1000,1' // 1000 requests per minute
'throttle:10,1'   // 10 requests per minute
```

---

## Basic Rate Limiting Examples

### Apply to All API Routes

In `routes/api.php`:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('posts', PostController::class);
    });
});
```

### Apply to Specific Routes

```php
// Public endpoints - stricter limits
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1'); // Only 5 registrations per minute

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1'); // Only 10 login attempts per minute

// Authenticated endpoints - more generous
Route::middleware(['auth:sanctum', 'throttle:100,1'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

### Different Limits for Different Routes

```php
// Low-cost operations - higher limits
Route::get('/tasks', [TaskController::class, 'index'])
    ->middleware('throttle:100,1');

// Expensive operations - lower limits
Route::post('/tasks/bulk-create', [TaskController::class, 'bulkCreate'])
    ->middleware('throttle:10,1');

// Critical operations - very strict
Route::post('/password/reset', [PasswordController::class, 'reset'])
    ->middleware('throttle:3,1'); // Only 3 password resets per minute
```

---

## Custom Rate Limiters

Define custom limiters in `bootstrap/app.php` (Laravel 11) or `app/Providers/RouteServiceProvider.php` (Laravel 10).

### Laravel 11 Syntax

In `bootstrap/app.php`:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

->withMiddleware(function (Middleware $middleware) {
    // Custom rate limiters
    RateLimiter::for('api', function (Request $request) {
        return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    });
})
```

### Named Rate Limiters

Create multiple named limiters:

```php
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

// In boot() method or withMiddleware()

// Standard API limiter
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Strict limiter for auth endpoints
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

// Generous limiter for authenticated users
RateLimiter::for('authenticated', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(1000)->by($request->user()->id)
        : Limit::perMinute(60)->by($request->ip());
});

// Upload limiter
RateLimiter::for('uploads', function (Request $request) {
    return Limit::perMinute(10)->by($request->user()->id);
});
```

Use in routes:

```php
// Use custom limiters
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth');

Route::middleware(['auth:sanctum', 'throttle:authenticated'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
});

Route::post('/upload', [FileController::class, 'upload'])
    ->middleware(['auth:sanctum', 'throttle:uploads']);
```

---

## Advanced Rate Limiting

### Per-User vs Per-IP

```php
// Rate limit by user ID (for authenticated users)
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(100)->by($request->user()->id)
        : Limit::perMinute(20)->by($request->ip());
});
```

**How it works**:
- Authenticated users: 100 requests/minute per user ID
- Unauthenticated users: 20 requests/minute per IP address

### Multiple Time Windows

```php
RateLimiter::for('api', function (Request $request) {
    return [
        Limit::perMinute(60), // 60 per minute
        Limit::perHour(1000), // AND 1000 per hour
    ];
});
```

User must satisfy **both** limits. If they exceed either, they're rate limited.

### Different Limits for Different Users

```php
RateLimiter::for('api', function (Request $request) {
    if (!$request->user()) {
        return Limit::perMinute(20)->by($request->ip());
    }

    // Premium users get higher limits
    if ($request->user()->isPremium()) {
        return Limit::perMinute(500)->by($request->user()->id);
    }

    // Standard users
    return Limit::perMinute(100)->by($request->user()->id);
});
```

### Response Callbacks

Customize the response when rate limit is exceeded:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)
        ->by($request->user()?->id ?: $request->ip())
        ->response(function (Request $request, array $headers) {
            return response()->json([
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => $headers['Retry-After'] ?? 60,
            ], 429, $headers);
        });
});
```

---

## Rate Limit Headers

When rate limiting is active, Laravel automatically adds headers to responses:

```
X-RateLimit-Limit: 60        (Total requests allowed)
X-RateLimit-Remaining: 58    (Requests remaining)
Retry-After: 42              (Seconds until limit resets)
```

### Example Response

**Within limit**:
```bash
$ curl -i http://localhost/api/tasks

HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

**Exceeded limit**:
```bash
$ curl -i http://localhost/api/tasks

HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
Retry-After: 42

{"message": "Too many requests"}
```

### Reading Headers in Client

**JavaScript example**:

```javascript
async function apiCall() {
    const response = await fetch('/api/tasks', {
        headers: {
            'Authorization': 'Bearer ' + token
        }
    });

    // Check rate limit headers
    const limit = response.headers.get('X-RateLimit-Limit');
    const remaining = response.headers.get('X-RateLimit-Remaining');

    console.log(`API calls remaining: ${remaining}/${limit}`);

    if (response.status === 429) {
        const retryAfter = response.headers.get('Retry-After');
        console.log(`Rate limited. Retry after ${retryAfter} seconds`);

        // Wait and retry
        await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
        return apiCall(); // Retry
    }

    return response.json();
}
```

---

## Rate Limiting Strategies by Endpoint Type

### Authentication Endpoints (Strict)

```php
RateLimiter::for('auth', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->ip()),
        Limit::perHour(20)->by($request->ip()),
    ];
});

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth');
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:auth');
```

**Why strict?** Prevent brute-force attacks and spam registrations.

### Read Operations (Generous)

```php
RateLimiter::for('reads', function (Request $request) {
    return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
});

Route::get('/tasks', [TaskController::class, 'index'])
    ->middleware('throttle:reads');
Route::get('/posts', [PostController::class, 'index'])
    ->middleware('throttle:reads');
```

**Why generous?** Read operations are cheap, users often refresh pages.

### Write Operations (Moderate)

```php
RateLimiter::for('writes', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()->id);
});

Route::post('/tasks', [TaskController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:writes']);
Route::put('/tasks/{task}', [TaskController::class, 'update'])
    ->middleware(['auth:sanctum', 'throttle:writes']);
```

**Why moderate?** Write operations use more resources but are common.

### Expensive Operations (Very Strict)

```php
RateLimiter::for('expensive', function (Request $request) {
    return [
        Limit::perMinute(5)->by($request->user()->id),
        Limit::perHour(50)->by($request->user()->id),
    ];
});

Route::post('/export', [ExportController::class, 'export'])
    ->middleware(['auth:sanctum', 'throttle:expensive']);
Route::post('/tasks/bulk-import', [TaskController::class, 'bulkImport'])
    ->middleware(['auth:sanctum', 'throttle:expensive']);
```

**Why very strict?** These operations use significant server resources.

---

## Testing Rate Limits

### Manual Testing with cURL

```bash
# Make rapid requests
for i in {1..65}; do
    curl http://localhost/api/tasks \
        -H "Authorization: Bearer YOUR_TOKEN" \
        -H "Accept: application/json"
    echo "Request $i"
done
```

After 60 requests (within a minute), you'll get 429 responses.

### Automated Testing

In `tests/Feature/RateLimitTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rate_limits_api_requests()
    {
        $user = User::factory()->create();

        // Make 60 successful requests
        for ($i = 0; $i < 60; $i++) {
            $response = $this->actingAs($user, 'sanctum')
                ->getJson('/api/tasks');

            $response->assertStatus(200);
        }

        // 61st request should be rate limited
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertStatus(429);
        $response->assertJson(['message' => 'Too Many Attempts.']);
    }

    /** @test */
    public function it_includes_rate_limit_headers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /** @test */
    public function it_rate_limits_login_attempts()
    {
        // Attempt 5 logins
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password'
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertStatus(429);
    }
}
```

---

## Bypassing Rate Limits (Admin Users)

Allow admins or system accounts to bypass limits:

```php
RateLimiter::for('api', function (Request $request) {
    // Admins have no limits
    if ($request->user()?->is_admin) {
        return Limit::none();
    }

    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

---

## Redis for Rate Limiting (Production)

By default, Laravel uses the cache driver for rate limiting. In production, use Redis for better performance.

### Install Redis

```bash
composer require predis/predis
```

### Configure Redis

In `.env`:

```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

In `config/cache.php` (already configured by default):

```php
'default' => env('CACHE_DRIVER', 'redis'),
```

**Why Redis?**
- Much faster than file/database cache
- Handles high concurrency
- Built-in expiration (TTL)
- Distributed rate limiting across multiple servers

---

## Custom Middleware for Complex Logic

Create custom middleware for complex rate limiting logic:

```bash
php artisan make:middleware RateLimitPerPlan
```

In `app/Http/Middleware/RateLimitPerPlan.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitPerPlan
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            // Not authenticated - strict limit
            $limit = Limit::perMinute(20)->by($request->ip());
        } else {
            // Authenticated - varies by plan
            $limit = match($user->plan) {
                'free' => Limit::perMinute(60),
                'basic' => Limit::perMinute(200),
                'premium' => Limit::perMinute(1000),
                'enterprise' => Limit::none(), // Unlimited
                default => Limit::perMinute(60),
            };
            $limit = $limit->by($user->id);
        }

        // Apply limit
        $key = 'api_limit:' . ($user?->id ?? $request->ip());
        $maxAttempts = $limit->maxAttempts;
        $decayMinutes = 1;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'message' => 'Too many requests. Upgrade your plan for higher limits.',
                'current_plan' => $user?->plan ?? 'none',
                'current_limit' => $maxAttempts . ' per minute',
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }
}
```

Use in routes:

```php
Route::middleware(['auth:sanctum', RateLimitPerPlan::class])->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

---

## Best Practices

### 1. Use Different Limits for Different Endpoints

```php
// Not this - same limit for everything
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/login', ...);
    Route::get('/tasks', ...);
    Route::post('/upload', ...);
});

// Do this - tailored limits
Route::post('/login', ...)->middleware('throttle:5,1');
Route::get('/tasks', ...)->middleware('throttle:200,1');
Route::post('/upload', ...)->middleware('throttle:10,1');
```

### 2. Rate Limit by User ID, Not IP (When Possible)

```php
// ❌ BAD - Multiple users behind same IP (office, NAT) get same limit
Limit::perMinute(60)->by($request->ip())

// ✅ GOOD - Each user has own limit
Limit::perMinute(60)->by($request->user()->id)
```

### 3. Provide Clear Error Messages

```php
->response(function () {
    return response()->json([
        'message' => 'Rate limit exceeded. You can make 60 requests per minute.',
        'retry_after_seconds' => 42,
        'upgrade_url' => '/pricing', // Suggest upgrade
    ], 429);
})
```

### 4. Document Your Rate Limits

In your API documentation:

```markdown
## Rate Limits

All API endpoints are rate limited to prevent abuse.

### Limits by Plan

| Plan | Requests per minute |
|------|---------------------|
| Free | 60 |
| Basic | 200 |
| Premium | 1000 |
| Enterprise | Unlimited |

### Rate Limit Headers

All responses include these headers:
- `X-RateLimit-Limit`: Total requests allowed
- `X-RateLimit-Remaining`: Requests remaining
- `Retry-After`: Seconds until limit resets (only when limited)

### HTTP 429 Response

When you exceed the limit:
```json
{
    "message": "Too many requests",
    "retry_after": 42
}
```
```

### 5. Monitor Rate Limit Hits

Log when users hit rate limits:

```php
->response(function (Request $request) {
    Log::warning('Rate limit exceeded', [
        'user_id' => $request->user()?->id,
        'ip' => $request->ip(),
        'endpoint' => $request->path(),
    ]);

    return response()->json(['message' => 'Too many requests'], 429);
})
```

### 6. Test in Staging Before Production

Always test rate limits with realistic traffic before deploying.

---

## Quick Quiz

1. **What is rate limiting and why is it important?**

2. **What does `throttle:60,1` mean?**

3. **What HTTP status code is returned when rate limited?**

4. **Should you rate limit by IP or user ID? When?**

5. **What are the rate limit headers Laravel includes?**

6. **How would you give premium users higher rate limits?**

---

## Practice Exercise

**Build a rate-limited Notes API with tiered limits**:

1. Create a User model with `plan` field (free, premium, enterprise)
2. Create Notes API with authentication
3. Implement rate limiting:
   - Login: 5 requests/minute
   - Free users: 60 requests/minute
   - Premium users: 200 requests/minute
   - Enterprise users: 1000 requests/minute
4. Add custom 429 response with upgrade suggestion
5. Write tests for rate limiting
6. Document rate limits in a README

**Bonus**:
- Different limits for read vs write operations
- Track and log rate limit hits
- Create a dashboard showing rate limit usage

---

## What's Next?

Congratulations! You've completed the API section. In the next lesson, you'll dive into **PHPUnit and Testing Basics** - learning how to write automated tests to ensure your code works correctly. Testing is essential for professional development!

Rate limiting protects your API and ensures fair usage for all users. It's a critical part of any production API!
