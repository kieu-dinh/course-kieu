# Lesson 04 - Authentication Middleware Deep Dive

**Duration**: 75 minutes
**Objectives**: Master Laravel middleware, understand how route protection works, create custom middleware

---

## What is Middleware?

Remember in Module 07 when you created a `requireAuth()` function?

```php
// Module 07 - Your middleware concept
function requireAuth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Then at the top of every protected page:
requireAuth();
```

**Middleware is Laravel's elegant solution to this problem.**

Middleware is code that runs BEFORE or AFTER a request reaches your controller. Think of it as a series of "layers" that a request passes through:

```
Browser Request
    ↓
    [Middleware 1: Check if logged in]
    ↓
    [Middleware 2: Check if verified email]
    ↓
    [Middleware 3: Check if admin]
    ↓
    Your Controller
    ↓
Response to Browser
```

If any middleware layer rejects the request, it stops there and redirects.

---

## Laravel's Built-in Auth Middleware

Laravel includes several authentication middleware out of the box. Let's explore them.

### 1. `Authenticate` Middleware

**File:** `vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php`

This is the `auth` middleware you've been using:

```php
<?php

namespace Illuminate\Auth\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    protected $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                return $this->auth->shouldUse($guard);
            }
        }

        $this->unauthenticated($request, $guards);
    }

    protected function unauthenticated($request, array $guards)
    {
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }

    protected function redirectTo($request)
    {
        //
    }
}
```

**How it works:**

1. **Check if user is authenticated** with `Auth::guard()->check()`
2. **If yes**, pass the request to the next middleware/controller with `$next($request)`
3. **If no**, throw `AuthenticationException`
4. Exception handler redirects to login page

**To customize the redirect, override in your app:**

Create `app/Http/Middleware/Authenticate.php`:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
```

This tells Laravel:
- If it's an API request (expects JSON), return 401 Unauthorized
- If it's a web request, redirect to login page

### 2. `RedirectIfAuthenticated` Middleware

**File:** `app/Http/Middleware/RedirectIfAuthenticated.php` (Breeze creates this)

This is the `guest` middleware - prevents logged-in users from seeing login/register pages:

```php
<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(route('dashboard'));
            }
        }

        return $next($request);
    }
}
```

**How it works:**

1. **Check if user is logged in**
2. **If yes**, redirect to dashboard
3. **If no**, allow access to login/register pages

**Example usage:**

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create']);
    Route::get('/register', [RegisteredUserController::class, 'create']);
});
```

Try it! Log in, then visit `/login` - you'll be redirected to the dashboard automatically.

### 3. `EnsureEmailIsVerified` Middleware

**File:** `vendor/laravel/framework/src/Illuminate/Auth/Middleware/EnsureEmailIsVerified.php`

This is the `verified` middleware:

```php
public function handle($request, Closure $next, $redirectToRoute = null)
{
    if (! $request->user() ||
        ($request->user() instanceof MustVerifyEmail &&
        ! $request->user()->hasVerifiedEmail())) {
        return $request->expectsJson()
                ? abort(403, 'Your email address is not verified.')
                : redirect()->route($redirectToRoute ?: 'verification.notice');
    }

    return $next($request);
}
```

**How it works:**

1. Check if user exists
2. Check if user implements `MustVerifyEmail` interface
3. Check if `email_verified_at` is not null
4. If not verified, redirect to verification notice page

**Usage:**

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

---

## Applying Middleware to Routes

There are several ways to apply middleware in Laravel.

### Method 1: Route Middleware

Apply to individual routes:

```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');
```

### Method 2: Middleware Groups

Apply to groups of routes:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/settings', [SettingsController::class, 'index']);
});
```

### Method 3: Multiple Middleware

Apply multiple middleware:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Method 4: Controller Constructor

Apply in the controller (older approach):

```php
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('verified')->only('index');
    }
}
```

**Best practice:** Use route groups for clarity.

### Method 5: Global Middleware Groups

Laravel defines middleware groups in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\HandleInertiaRequests::class,
    ]);

    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ]);
})
```

The `web` middleware group is automatically applied to all routes in `routes/web.php`.

---

## Creating Custom Middleware

Let's create some custom middleware to understand how it works.

### Example 1: Check if User is Admin

**Create the middleware:**

```bash
php artisan make:middleware EnsureUserIsAdmin
```

This creates `app/Http/Middleware/EnsureUserIsAdmin.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->is_admin) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
```

**Register the middleware alias in `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth' => \App\Http\Middleware\Authenticate::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

**Add `is_admin` column to users table:**

```bash
php artisan make:migration add_is_admin_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false)->after('email');
    });
}
```

```bash
php artisan migrate
```

**Use the middleware:**

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
    Route::get('/admin/users', [AdminUserController::class, 'index']);
});
```

Now only users with `is_admin = 1` can access these routes!

### Example 2: Log All Requests

Let's create middleware that logs every request (useful for debugging).

```bash
php artisan make:middleware LogRequests
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        // Before the controller
        Log::info('Request:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_id' => auth()->id(),
        ]);

        $response = $next($request);

        // After the controller
        Log::info('Response:', [
            'status' => $response->status(),
        ]);

        return $response;
    }
}
```

**Register globally in `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\LogRequests::class);
})
```

Now every request is logged to `storage/logs/laravel.log`!

### Example 3: Require Password Confirmation

Laravel includes a `password.confirm` middleware for sensitive actions:

```php
Route::middleware(['auth', 'password.confirm'])->group(function () {
    Route::get('/settings/delete-account', [AccountController::class, 'deleteForm']);
    Route::delete('/settings/delete-account', [AccountController::class, 'destroy']);
});
```

When a user visits these routes, they'll be prompted to re-enter their password if they haven't confirmed it in the last 3 hours (configurable in `config/auth.php`).

**How it works internally:**

```php
public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null)
{
    if ($this->shouldConfirmPassword($request, $passwordTimeoutSeconds)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Password confirmation required.'], 423);
        }

        return redirect()->route($redirectToRoute ?? 'password.confirm');
    }

    return $next($request);
}

protected function shouldConfirmPassword($request, $passwordTimeoutSeconds = null)
{
    $confirmedAt = time() - $request->session()->get('auth.password_confirmed_at', 0);

    return $confirmedAt > ($passwordTimeoutSeconds ?? $this->passwordTimeout);
}
```

---

## Middleware Parameters

You can pass parameters to middleware.

### Example: Role-Based Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== $role) {
            abort(403, "You must be a {$role} to access this page.");
        }

        return $next($request);
    }
}
```

**Usage:**

```php
Route::middleware('role:admin')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});

Route::middleware('role:moderator')->group(function () {
    Route::get('/moderate', [ModeratorController::class, 'index']);
});
```

### Multiple Parameters

```php
public function handle(Request $request, Closure $next, string $role, string $permission): Response
{
    // Check role and permission
}

// Usage
Route::middleware('permission:admin,edit-posts')->get('/posts/edit', ...);
```

---

## Middleware Order Matters!

Middleware is executed in the order you define it.

**Example:**

```php
Route::middleware(['guest', 'auth'])->get('/dashboard', ...);
```

This won't work! The `guest` middleware redirects authenticated users, so the `auth` middleware never runs.

**Correct order:**

```php
Route::middleware(['auth', 'verified'])->get('/dashboard', ...);
```

1. First, check if logged in (`auth`)
2. Then, check if email verified (`verified`)

---

## Middleware in Breeze Routes

Let's examine how Breeze uses middleware in `routes/auth.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Guest routes (must NOT be logged in)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// Authenticated routes (must be logged in)
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

**Notice:**

- **Guest middleware** on login/register (can't access if already logged in)
- **Auth middleware** on logout/verify-email (must be logged in)
- **Signed middleware** on email verification (prevents URL tampering)
- **Throttle middleware** on email sending (rate limiting)

---

## Comparing to Module 07

Let's compare middleware approaches:

### Module 07: Manual Protection

```php
<?php
// dashboard.php
session_start();
require_once 'includes/middleware.php';

requireAuth(); // Must call this on every protected page!

// Rest of page...
?>
```

**Problems:**
- Easy to forget
- Duplicate code
- Can't easily protect groups of pages
- No consistent redirect logic

### Laravel: Declarative Protection

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/settings', [SettingsController::class, 'index']);
    // 100 more routes...
});
```

**Benefits:**
- Declared once, applied to many routes
- Centralized logic
- Easy to see which routes are protected
- Consistent behavior

---

## Hands-On Exercises

### Exercise 1: Create a "Banned" Middleware

Create middleware that checks if a user is banned:

1. Add `is_banned` column to users table
2. Create `CheckIfBanned` middleware
3. If banned, log them out and show a message
4. Apply to all authenticated routes

**Hint:**

```php
if (auth()->user()->is_banned) {
    auth()->logout();
    return redirect()->route('login')
        ->with('error', 'Your account has been banned.');
}
```

### Exercise 2: Create a "Verified Phone" Middleware

Similar to email verification, but for phone numbers:

1. Add `phone_verified_at` column to users
2. Create `EnsurePhoneIsVerified` middleware
3. Check if phone is verified
4. If not, redirect to phone verification page

### Exercise 3: Create a "Subscribe" Middleware

Check if user has an active subscription:

1. Add `subscription_expires_at` column to users
2. Create `CheckSubscription` middleware
3. Check if subscription is active
4. If expired, redirect to subscription page
5. Apply to premium routes

---

## Debugging Middleware

Want to see which middleware is applied to a route?

```bash
php artisan route:list
```

Output:

```
  GET|HEAD  dashboard ...... dashboard › DashboardController@index
                             ├ web
                             ├ auth
                             └ verified
```

See? It shows all middleware applied to each route!

For a specific route:

```bash
php artisan route:list --name=dashboard
```

---

## Quick Quiz

Test your understanding:

1. **What is middleware?**
   - Code that runs before/after a request reaches the controller

2. **What does the `auth` middleware do?**
   - Checks if user is logged in
   - If not, redirects to login page

3. **What does the `guest` middleware do?**
   - Checks if user is NOT logged in
   - If logged in, redirects to dashboard

4. **How do you create custom middleware?**
   - `php artisan make:middleware MiddlewareName`

5. **How do you register a middleware alias?**
   - Add to `bootstrap/app.php` in the `withMiddleware` closure

6. **Can middleware accept parameters?**
   - Yes! Define parameters after `$next`
   - Example: `public function handle($request, Closure $next, string $role)`

---

## What's Next?

In **Lesson 05 - Gates and Policies**, we'll dive into **authorization**:
- What's the difference between authentication and authorization?
- Defining Gates for simple checks
- Creating Policies for model-based authorization
- Checking permissions in controllers and views

---

## Key Takeaways

1. **Middleware is Laravel's way of protecting routes:**
   - Replaces manual `requireAuth()` checks
   - Runs before the controller
   - Can redirect or abort the request

2. **Laravel includes several auth middleware:**
   - `auth` - Requires authentication
   - `guest` - Requires NOT authenticated
   - `verified` - Requires verified email
   - `password.confirm` - Requires password confirmation

3. **Creating custom middleware is easy:**
   - `php artisan make:middleware`
   - Check conditions
   - Return redirect/abort or `$next($request)`

4. **Middleware can be applied in multiple ways:**
   - Individual routes
   - Route groups (best practice)
   - Controller constructors
   - Globally

5. **Middleware can accept parameters:**
   - Pass parameters after the middleware name
   - Example: `'role:admin'`

6. **Order matters:**
   - Apply `auth` before `verified`
   - Apply `guest` to login/register routes
   - Think about the logical flow

You now have complete mastery over Laravel's authentication middleware! Next, we'll learn about authorization - controlling what authenticated users can do.
