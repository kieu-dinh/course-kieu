# Lesson 10 - Middleware Basics

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What is Middleware?

**Middleware** filters HTTP requests entering your application.

Think of middleware as **security checkpoints** at an airport:

```
Request → Middleware 1 → Middleware 2 → Controller
                ↓              ↓
         (Check passport) (Check ticket)
```

**Common uses:**
- Authentication (is user logged in?)
- Authorization (does user have permission?)
- CORS headers
- Logging
- Rate limiting
- Input sanitization

### Pure PHP Approach (Module 07)

**Checking authentication everywhere:**

```php
// dashboard.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Page content...

// posts/create.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Page content...

// profile.php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Page content...
```

**Problems:**
- **Repeated code** in every file
- **Easy to forget** - security risk!
- **Hard to maintain** - change logic everywhere
- **No centralization** - scattered across files

### Laravel Middleware

**Define once, apply to multiple routes:**

```php
// Middleware: app/Http/Middleware/Authenticate.php
public function handle($request, Closure $next)
{
    if (!auth()->check()) {
        return redirect('/login');
    }

    return $next($request);
}

// Apply to routes: routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::resource('posts', PostController::class);
    Route::get('/profile', [ProfileController::class, 'index']);
});
```

**Centralized, reusable, maintainable!**

---

## How Middleware Works

### Request Flow

```
Browser
   ↓
Request
   ↓
Middleware 1 (e.g., CSRF check)
   ↓
Middleware 2 (e.g., Authentication)
   ↓
Middleware 3 (e.g., Authorization)
   ↓
Controller
   ↓
Response
   ↓
Middleware 3 (after logic)
   ↓
Middleware 2 (after logic)
   ↓
Middleware 1 (after logic)
   ↓
Browser
```

**Middleware can run:**
- **Before** the request reaches the controller
- **After** the controller generates a response

### Basic Middleware Structure

```php
public function handle(Request $request, Closure $next)
{
    // Before logic (run before controller)

    $response = $next($request);  // Pass to next middleware or controller

    // After logic (run after controller)

    return $response;
}
```

**Example:**

```php
public function handle(Request $request, Closure $next)
{
    // Check authentication BEFORE controller
    if (!auth()->check()) {
        return redirect('/login');
    }

    $response = $next($request);

    // Log request AFTER controller
    Log::info('Request processed', [
        'url' => $request->url(),
        'status' => $response->status(),
    ]);

    return $response;
}
```

---

## Laravel's Built-in Middleware

Laravel includes several middleware out-of-the-box.

### View All Middleware

**In `app/Http/Kernel.php`:**

```php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    // ... more
];
```

### Common Middleware

**`auth`** - Ensure user is authenticated
```php
Route::middleware('auth')->get('/dashboard', ...);
```

**`guest`** - Ensure user is NOT authenticated (for login/register)
```php
Route::middleware('guest')->get('/login', ...);
```

**`verified`** - Ensure email is verified
```php
Route::middleware(['auth', 'verified'])->get('/dashboard', ...);
```

**`throttle`** - Rate limiting
```php
Route::middleware('throttle:60,1')->get('/api/posts', ...);
// 60 requests per minute
```

**`csrf`** - CSRF protection (applied to all POST/PUT/DELETE by default)

---

## Applying Middleware

### In Routes

**Single middleware:**
```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');
```

**Multiple middleware:**
```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified']);
```

**Middleware groups:**
```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::resource('posts', PostController::class);
});
```

**Nested groups:**
```php
Route::middleware('auth')->group(function () {
    // All authenticated users

    Route::prefix('admin')->middleware('admin')->group(function () {
        // Only admin users
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
    });

    Route::get('/profile', [ProfileController::class, 'index']);
});
```

### In Controllers

**Apply to all methods:**
```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // All methods require authentication
}
```

**Apply to specific methods:**
```php
public function __construct()
{
    $this->middleware('auth')->only(['create', 'store', 'edit', 'update', 'destroy']);
}
```

**Apply to all except:**
```php
public function __construct()
{
    $this->middleware('auth')->except(['index', 'show']);
}
```

---

## Creating Custom Middleware

### Generate Middleware

```bash
php artisan make:middleware CheckAge
```

Creates: `app/Http/Middleware/CheckAge.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAge
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->age < 18) {
            return redirect('home');
        }

        return $next($request);
    }
}
```

### Register Middleware

**In `app/Http/Kernel.php`:**

```php
protected $middlewareAliases = [
    // ... existing middleware
    'check.age' => \App\Http\Middleware\CheckAge::class,
];
```

### Use Custom Middleware

```php
Route::get('/adult-content', ...)->middleware('check.age');
```

---

## Middleware Parameters

Pass parameters to middleware:

### Define Middleware with Parameters

```php
public function handle(Request $request, Closure $next, $role)
{
    if (!auth()->user()->hasRole($role)) {
        abort(403, 'Unauthorized');
    }

    return $next($request);
}
```

### Use with Parameters

```php
Route::get('/admin', ...)->middleware('role:admin');
Route::get('/editor', ...)->middleware('role:editor');
```

**Multiple parameters:**
```php
public function handle(Request $request, Closure $next, $role, $permission)
{
    if (!auth()->user()->hasRole($role) || !auth()->user()->can($permission)) {
        abort(403);
    }

    return $next($request);
}

// Usage
Route::get('/posts/edit', ...)->middleware('role:editor,edit-posts');
```

---

## Real-World Middleware Examples

### 1. Authentication Check

```php
// app/Http/Middleware/Authenticate.php
public function handle(Request $request, Closure $next)
{
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Please login to continue.');
    }

    return $next($request);
}
```

**Pure PHP equivalent (Module 07):**
```php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
```

### 2. Admin Check

```php
// app/Http/Middleware/IsAdmin.php
public function handle(Request $request, Closure $next)
{
    if (!auth()->check() || !auth()->user()->is_admin) {
        abort(403, 'Unauthorized action.');
    }

    return $next($request);
}
```

**Usage:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/users', [AdminController::class, 'users']);
});
```

### 3. Force HTTPS

```php
// app/Http/Middleware/ForceHttps.php
public function handle(Request $request, Closure $next)
{
    if (!$request->secure() && app()->environment('production')) {
        return redirect()->secure($request->getRequestUri());
    }

    return $next($request);
}
```

### 4. Logging

```php
// app/Http/Middleware/LogRequests.php
public function handle(Request $request, Closure $next)
{
    Log::info('Request started', [
        'url' => $request->url(),
        'method' => $request->method(),
        'user_id' => auth()->id(),
    ]);

    $response = $next($request);

    Log::info('Request completed', [
        'status' => $response->status(),
        'time' => now(),
    ]);

    return $response;
}
```

### 5. Rate Limiting

```php
// app/Http/Middleware/RateLimiter.php
public function handle(Request $request, Closure $next)
{
    $key = $request->ip();

    if (Cache::has($key) && Cache::get($key) > 100) {
        abort(429, 'Too many requests');
    }

    Cache::increment($key);
    Cache::put($key, Cache::get($key, 0), now()->addMinute());

    return $next($request);
}
```

**Laravel's built-in is better:**
```php
Route::middleware('throttle:100,1')->group(function () {
    // 100 requests per minute
});
```

### 6. API Token Check

```php
// app/Http/Middleware/CheckApiToken.php
public function handle(Request $request, Closure $next)
{
    $token = $request->header('X-API-Token');

    if (!$token || !User::where('api_token', $token)->exists()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    return $next($request);
}
```

**Usage:**
```php
Route::middleware('api.token')->group(function () {
    Route::get('/api/posts', [ApiPostController::class, 'index']);
});
```

### 7. Sanitize Input

```php
// app/Http/Middleware/SanitizeInput.php
public function handle(Request $request, Closure $next)
{
    $input = $request->all();

    array_walk_recursive($input, function (&$value) {
        $value = strip_tags($value);
    });

    $request->merge($input);

    return $next($request);
}
```

**Caution:** Can interfere with intended HTML input. Use validation instead!

### 8. Check Post Owner

```php
// app/Http/Middleware/CheckPostOwner.php
public function handle(Request $request, Closure $next)
{
    $post = $request->route('post');

    if ($post && $post->user_id !== auth()->id()) {
        abort(403, 'You can only edit your own posts.');
    }

    return $next($request);
}
```

**Usage:**
```php
Route::put('/posts/{post}', [PostController::class, 'update'])
    ->middleware(['auth', 'post.owner']);
```

---

## Middleware Groups

Laravel defines **middleware groups** for common combinations.

**In `app/Http/Kernel.php`:**

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],

    'api' => [
        'throttle:api',
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
    ],
];
```

**`web` group includes:**
- Cookie encryption
- Session handling
- CSRF protection
- Error sharing with views

**`api` group includes:**
- Rate limiting
- Route model binding

**All routes in `routes/web.php` automatically have `web` middleware!**

**Create custom group:**

```php
protected $middlewareGroups = [
    'admin' => [
        'auth',
        'verified',
        'admin',
        'log.requests',
    ],
];

// Usage
Route::middleware('admin')->group(function () {
    // Admin routes
});
```

---

## Terminable Middleware

**Run code AFTER response is sent to browser.**

```php
public function handle(Request $request, Closure $next)
{
    return $next($request);
}

public function terminate(Request $request, Response $response)
{
    // This runs after response is sent
    // Good for: logging, analytics, cleanup
    Log::info('Response sent', [
        'url' => $request->url(),
        'status' => $response->status(),
        'time' => microtime(true) - LARAVEL_START,
    ]);
}
```

**Why?** User gets response faster, heavy operations run in background.

---

## Middleware Priority

**Order matters!**

```php
Route::middleware(['auth', 'admin'])->get('/dashboard', ...);
```

1. **auth** runs first (checks if logged in)
2. **admin** runs second (checks if admin)

**If auth fails, admin never runs!**

**Global middleware order** (in `Kernel.php`):

```php
protected $middleware = [
    // These run on EVERY request
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class,
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
    // ...
];
```

---

## Skipping Middleware

**Exclude specific routes from middleware:**

```php
Route::get('/webhook', [WebhookController::class, 'handle'])
    ->withoutMiddleware(['csrf']);
```

---

## Testing Middleware

### Example Test

```php
public function test_guest_cannot_access_dashboard()
{
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
}

public function test_authenticated_user_can_access_dashboard()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
}
```

---

## Complete Example: Blog with Authentication

**routes/web.php:**

```php
<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Guest routes (not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Public post viewing
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Post management
    Route::resource('posts', PostController::class)->except(['index', 'show']);
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/posts', [AdminController::class, 'posts'])->name('posts.index');
});
```

**Clear structure:**
- Public routes: No middleware
- Guest routes: Only for non-authenticated
- Auth routes: Require login
- Admin routes: Require login + admin role

---

## Best Practices

### 1. Use Middleware for Cross-Cutting Concerns

**Good uses:**
- Authentication
- Authorization
- Logging
- Rate limiting
- CORS
- Content negotiation

**Bad uses:**
- Business logic (belongs in controller/model)
- Database queries (belongs in controller)

### 2. Keep Middleware Focused

**Bad:**
```php
public function handle(Request $request, Closure $next)
{
    // Check auth
    if (!auth()->check()) { ... }

    // Check admin
    if (!auth()->user()->is_admin) { ... }

    // Log request
    Log::info(...);

    // Rate limit
    if (Cache::has(...)) { ... }

    return $next($request);
}
```

**Good:**
```php
// Separate middleware for each concern
Route::middleware(['auth', 'admin', 'log', 'throttle'])->group(...);
```

### 3. Order Middleware Correctly

```php
// ✅ Correct order
Route::middleware(['auth', 'verified', 'admin'])->group(...);

// ❌ Wrong order - wastes time checking admin before auth
Route::middleware(['admin', 'verified', 'auth'])->group(...);
```

### 4. Use Controller Middleware for Fine Control

```php
public function __construct()
{
    $this->middleware('auth')->except(['index', 'show']);
}
```

**Better than applying to individual routes.**

### 5. Don't Overuse Middleware

**Bad:**
```php
Route::middleware(['middleware1', 'middleware2', 'middleware3', 'middleware4', 'middleware5'])->group(...);
```

**Good:**
```php
// Create middleware group
'admin' => ['auth', 'verified', 'admin', 'log'],

Route::middleware('admin')->group(...);
```

---

## Summary

**What You Learned:**
- What middleware is and why it's useful
- How middleware works (request/response flow)
- Laravel's built-in middleware (auth, guest, throttle, etc.)
- How to apply middleware (routes, controllers)
- Creating custom middleware
- Middleware parameters
- Middleware groups
- Real-world examples
- Best practices

**Key Takeaways:**
1. **Middleware filters requests** before they reach controllers
2. **`auth` middleware** protects routes (must be logged in)
3. **Apply to routes or controllers** - both work
4. **Groups organize middleware** - DRY principle
5. **Custom middleware** for app-specific logic
6. **Order matters** - auth before admin, etc.
7. **Keep middleware focused** - one concern per middleware

**Pure PHP vs Laravel:**
- Pure PHP: Repeated auth checks in every file
- Laravel: Middleware applied once to many routes

**Module 14 Complete!** You now understand Laravel basics. Next module covers Eloquent relationships and advanced database features!

---

## Practice Exercise

**Create a blog with middleware protection:**

1. **Public routes** (no middleware)
   - Homepage
   - Post index
   - Post show

2. **Guest routes** (guest middleware)
   - Login
   - Register

3. **Auth routes** (auth middleware)
   - Dashboard
   - Create/edit/delete own posts
   - Profile

4. **Admin routes** (auth + admin middleware)
   - User management
   - Moderate all posts

5. **Create custom middleware**
   - `CheckPostOwner` - Ensure user owns post before editing
   - `LogPageViews` - Log every page view

---

## Quick Quiz

**1. What is middleware?**
- Filters that run before/after request reaches controller

**2. How do you apply middleware to a route?**
```php
Route::middleware('auth')->get('/dashboard', ...);
```

**3. How do you create custom middleware?**
```bash
php artisan make:middleware CheckAge
```

**4. What's the difference between `auth` and `guest` middleware?**
- `auth`: Must be logged in
- `guest`: Must NOT be logged in

**5. How do you apply multiple middleware?**
```php
Route::middleware(['auth', 'verified'])->get(...);
```

**6. Where do you register middleware aliases?**
- `app/Http/Kernel.php` in `$middlewareAliases`

**7. What does `$next($request)` do?**
- Passes request to next middleware or controller

**8. How do you apply middleware to specific controller methods?**
```php
$this->middleware('auth')->only(['create', 'store']);
```

---

**Congratulations!** You've completed Module 14 - Laravel Basics!

**You learned:**
- Laravel installation and structure
- Artisan CLI
- Routing
- Controllers
- Views and Blade
- Migrations and databases
- Models and Eloquent
- Request and Response
- Middleware

**Next Module:** Module 15 - Eloquent ORM (relationships, eager loading, advanced queries)!
