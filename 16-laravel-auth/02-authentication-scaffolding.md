# Lesson 02 - Authentication Scaffolding Explained

**Duration**: 90 minutes
**Objectives**: Understand how Laravel's authentication system works internally, explore Guards, Providers, and the Auth facade

---

## What's Under the Hood?

In Lesson 01, you installed Breeze and got a working auth system. You could register, login, and logout. But HOW does it actually work?

Remember in Module 07 when you manually:
- Started sessions with `session_start()`
- Stored user IDs in `$_SESSION['user_id']`
- Checked `isset($_SESSION['user_id'])` to see if someone was logged in
- Retrieved user data from the database

**Laravel does all of this, but with a sophisticated, flexible system that lets you authenticate users in multiple ways.**

This lesson dives deep into Laravel's authentication internals.

---

## The Authentication System: Core Concepts

Laravel's auth system has three main components:

### 1. Guards

**A Guard defines HOW users are authenticated.**

Think of a guard as the "security checkpoint." Different checkpoints have different methods:
- **Session guard** - Uses sessions and cookies (traditional web apps) - what you built in Module 07
- **Token guard** - Uses API tokens (for APIs)
- **Sanctum guard** - Uses Laravel Sanctum tokens (SPAs and mobile apps)

You can have multiple guards in one application. For example:
- `web` guard for regular users using sessions
- `api` guard for API requests using tokens
- `admin` guard for admin users with separate authentication

**Default guard:** `web` (session-based, what Breeze uses)

### 2. Providers

**A Provider defines WHERE to retrieve users from.**

The provider is the "database" layer of authentication:
- **Eloquent provider** - Retrieves users from database using Eloquent (default)
- **Database provider** - Retrieves users using query builder (no Eloquent)
- **Custom provider** - Retrieve users from anywhere (LDAP, external API, etc.)

**Default provider:** `users` (uses Eloquent with the `User` model)

### 3. The Auth Facade

**The Auth facade is your interface to the authentication system.**

```php
use Illuminate\Support\Facades\Auth;

// Check if user is logged in
Auth::check();

// Get the current user
Auth::user();

// Get the user's ID
Auth::id();

// Log a user in
Auth::login($user);

// Log a user out
Auth::logout();

// Attempt to log in with credentials
Auth::attempt(['email' => $email, 'password' => $password]);
```

The facade is just a convenient way to access the underlying `Illuminate\Auth\AuthManager` class.

---

## Configuration: `config/auth.php`

Let's look at Laravel's auth configuration file:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
```

### Breaking It Down

**Defaults:**
- Default guard is `web` (session-based)
- Default password broker is `users` (for password resets)

**Guards:**
- `web` guard uses the `session` driver and the `users` provider
- `api` guard uses the `token` driver and the `users` provider

**Providers:**
- `users` provider uses Eloquent with the `User` model
- Alternatively, you could use the `database` driver with direct table queries

**Passwords:**
- Password reset tokens are stored in `password_reset_tokens` table
- Tokens expire after 60 minutes
- Only 1 reset attempt per 60 seconds (throttle)

**Password Confirmation:**
- Sensitive actions require password re-confirmation
- Confirmation lasts 10800 seconds (3 hours)

---

## The User Model: `app/Models/User.php`

Let's look at the `User` model that Breeze created:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

### Important Parts

**1. Extends `Authenticatable`**

This is key! `Authenticatable` is Laravel's base class for authenticatable models. It provides:
- `getAuthIdentifier()` - Returns the user's ID
- `getAuthPassword()` - Returns the user's hashed password
- `getRememberToken()` - Returns the remember token
- And more...

**2. `$fillable` Property**

Mass assignment protection. Only these fields can be filled using `User::create()`:

```php
User::create([
    'name' => 'John',
    'email' => 'john@example.com',
    'password' => Hash::make('password'),
]);
```

**3. `$hidden` Property**

These fields are hidden when the model is converted to JSON:

```php
return response()->json($user);
// { "id": 1, "name": "John", "email": "john@example.com" }
// password and remember_token are NOT included!
```

This is critical for security - you never want to accidentally expose passwords or tokens in API responses.

**4. `casts()` Method**

Automatic type casting:
- `email_verified_at` is cast to a `Carbon` datetime instance
- `password` is automatically hashed when set!

```php
$user->password = 'newpassword'; // Automatically hashed!
$user->save();
```

---

## The Authentication Flow

Let's trace through exactly what happens when a user logs in. This will show you how all the pieces work together.

### Step 1: User Submits Login Form

```html
<!-- resources/views/auth/login.blade.php -->
<form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="email" name="email" />
    <input type="password" name="password" />
    <button type="submit">Log in</button>
</form>
```

**POST request to `/login`**

### Step 2: Route Sends to Controller

```php
// routes/auth.php
Route::post('login', [AuthenticatedSessionController::class, 'store']);
```

### Step 3: Controller Handles Request

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();  // ← This is where the magic happens!

    $request->session()->regenerate();

    return redirect()->intended(route('dashboard', absolute: false));
}
```

### Step 4: LoginRequest Validates and Authenticates

```php
// app/Http/Requests/Auth/LoginRequest.php

public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

**What's happening here:**

1. **Rate limiting check** - Prevent brute force attacks
2. **`Auth::attempt()`** - The core authentication method
3. **If failed** - Increment rate limit counter and throw error
4. **If succeeded** - Clear rate limiter

### Step 5: `Auth::attempt()` - The Core Method

Let's break down what `Auth::attempt()` does:

```php
Auth::attempt(['email' => $email, 'password' => $password], $remember = false);
```

**Internally, this:**

1. **Retrieves the user from the database:**
   ```php
   $user = User::where('email', $email)->first();
   ```

2. **Verifies the password:**
   ```php
   if (Hash::check($password, $user->password)) {
       // Password correct!
   }
   ```

   Remember `password_verify()` from Module 07? `Hash::check()` does the same thing!

3. **If correct, logs the user in:**
   ```php
   Auth::login($user, $remember);
   ```

4. **Returns true/false:**
   - `true` if authentication succeeded
   - `false` if failed

### Step 6: `Auth::login()` - Starting the Session

When you call `Auth::login($user)`, Laravel:

1. **Stores user ID in session:**
   ```php
   session(['auth.id' => $user->id]);
   ```

   This is exactly what you did in Module 07 with `$_SESSION['user_id'] = $userId`!

2. **Regenerates session ID (security):**
   ```php
   $request->session()->regenerate();
   ```

   Prevents session fixation attacks. You did this too in Module 07!

3. **If "remember me" is true:**
   - Generates a random remember token
   - Stores it in the `users.remember_token` column
   - Sets a cookie with the token

   This is exactly the "Remember Me" functionality you built in Module 07, Lesson 08!

### Step 7: Redirect to Dashboard

```php
return redirect()->intended(route('dashboard'));
```

**`intended()`** redirects to:
- The page the user was trying to access before being redirected to login
- Or the dashboard if there's no intended destination

---

## Understanding `Auth::check()` and `Auth::user()`

Now let's look at how Laravel checks if someone is logged in.

### `Auth::check()`

```php
if (Auth::check()) {
    // User is logged in
}
```

**What it does internally:**

```php
public function check()
{
    return ! is_null($this->user());
}
```

Simple! It just checks if `user()` returns something.

### `Auth::user()`

```php
$user = Auth::user();
echo $user->name;
echo $user->email;
```

**What it does internally:**

1. **Checks if user is already loaded:**
   ```php
   if ($this->user !== null) {
       return $this->user;
   }
   ```

2. **If not, tries to retrieve from session:**
   ```php
   $id = $this->session->get('auth.id');
   ```

3. **Loads user from database:**
   ```php
   if ($id !== null) {
       $this->user = $this->provider->retrieveById($id);
   }
   ```

4. **Or checks remember token cookie:**
   ```php
   if ($this->user === null && $this->viaRemember()) {
       // Load user via remember token
   }
   ```

5. **Returns the user (or null):**
   ```php
   return $this->user;
   ```

**This is exactly the logic you implemented in Module 07!**

```php
// Your Module 07 code:
function getCurrentUser($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}
```

Laravel just wraps it in a cleaner interface!

---

## The `auth` Middleware

In Breeze, routes are protected with the `auth` middleware:

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

### What Does the Middleware Do?

Let's look at the actual middleware code:

```php
// Illuminate\Auth\Middleware\Authenticate

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
        if (Auth::guard($guard)->check()) {
            return Auth::guard($guard)->user();
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
```

**In plain English:**

1. Check if user is authenticated with `Auth::check()`
2. If yes, continue to the route
3. If no, throw `AuthenticationException`
4. Exception handler redirects to login page

**Compare to your Module 07 middleware:**

```php
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
```

Same concept! Laravel just has better error handling and flexibility.

---

## Multiple Guards Example

You can use different guards for different parts of your application.

### Configuration

```php
// config/auth.php

'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'admin' => [
        'driver' => 'session',
        'provider' => 'admins',
    ],

    'api' => [
        'driver' => 'sanctum',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],
],
```

### Usage

```php
// Check if regular user is logged in
if (Auth::guard('web')->check()) {
    $user = Auth::guard('web')->user();
}

// Check if admin is logged in
if (Auth::guard('admin')->check()) {
    $admin = Auth::guard('admin')->user();
}

// Login an admin
Auth::guard('admin')->login($adminUser);

// Logout an admin
Auth::guard('admin')->logout();
```

### Protecting Routes with Specific Guards

```php
// routes/web.php

// Protected by 'web' guard (default)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Protected by 'admin' guard
Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index']);
});
```

Notice the syntax: `auth:admin` tells the middleware to use the `admin` guard.

---

## The Remember Me Feature

Let's dive deeper into how "Remember Me" works in Laravel.

### Database Column

The `users` table has a `remember_token` column:

```php
$table->string('remember_token', 100)->nullable();
```

### When User Checks "Remember Me"

1. **User submits login with remember checkbox:**
   ```php
   Auth::attempt($credentials, $remember = true);
   ```

2. **Laravel generates a random token:**
   ```php
   $token = Str::random(60);
   ```

3. **Stores token in database:**
   ```php
   $user->remember_token = $token;
   $user->save();
   ```

4. **Sets a long-lived cookie:**
   ```php
   Cookie::queue('remember_web', $userId . '|' . $token, 2628000); // 5 years
   ```

### When User Returns (Session Expired)

1. **Session is empty** (user closed browser, session expired)

2. **Laravel checks for remember cookie:**
   ```php
   if ($cookie = $request->cookies->get('remember_web')) {
       // Cookie exists!
   }
   ```

3. **Extracts user ID and token:**
   ```php
   [$id, $token] = explode('|', $cookie, 2);
   ```

4. **Retrieves user from database:**
   ```php
   $user = User::find($id);
   ```

5. **Verifies token matches:**
   ```php
   if ($user && hash_equals($user->remember_token, $token)) {
       Auth::login($user); // Log them back in!
   }
   ```

**This is exactly what you built in Module 07, Lesson 08!**

---

## Comparing to Module 07

Let's create a comparison table:

| Feature | Module 07 (Pure PHP) | Laravel Breeze |
|---------|---------------------|----------------|
| **Check if logged in** | `isset($_SESSION['user_id'])` | `Auth::check()` |
| **Get current user** | `$_SESSION` + manual DB query | `Auth::user()` |
| **Login user** | `$_SESSION['user_id'] = $id` | `Auth::login($user)` |
| **Logout user** | `session_destroy()` | `Auth::logout()` |
| **Attempt login** | Manual: fetch user, verify password, set session | `Auth::attempt($credentials)` |
| **Remember me** | Manual: generate token, store in DB, set cookie | Handled automatically by `Auth::attempt($credentials, true)` |
| **Session regeneration** | `session_regenerate_id(true)` | Automatic |
| **Rate limiting** | Manual implementation | Built-in with `RateLimiter` |
| **Password hashing** | `password_hash()` | `Hash::make()` |
| **Password verification** | `password_verify()` | `Hash::check()` |
| **Protected routes** | `requireAuth()` function | `auth` middleware |

**The underlying concepts are identical.** Laravel just provides a cleaner, more flexible API.

---

## Hands-On: Exploring the Auth System

Let's write some code to see how this works in practice.

### Exercise 1: Playing with Auth Methods

Create a test route in `routes/web.php`:

```php
Route::get('/auth-test', function () {
    // Check if logged in
    $isLoggedIn = Auth::check();

    // Get current user (or null)
    $user = Auth::user();

    // Get user ID (or null)
    $userId = Auth::id();

    // Get the guard being used
    $guardName = Auth::getDefaultDriver(); // 'web'

    return response()->json([
        'is_logged_in' => $isLoggedIn,
        'user' => $user,
        'user_id' => $userId,
        'guard' => $guardName,
    ]);
});
```

Visit `/auth-test` in your browser:
- **When logged out:** `is_logged_in: false`, `user: null`
- **When logged in:** `is_logged_in: true`, `user: {...}`

### Exercise 2: Manual Login

Create a route that manually logs in a user (for testing):

```php
Route::get('/manual-login/{id}', function ($id) {
    $user = \App\Models\User::find($id);

    if ($user) {
        Auth::login($user);
        return redirect('/dashboard');
    }

    return "User not found";
});
```

Visit `/manual-login/1` - you're instantly logged in as user ID 1!

**Warning:** Don't leave this route in production. It's a security hole!

### Exercise 3: Check Session Data

Create a route to see what's actually in the session:

```php
Route::get('/session-test', function () {
    return response()->json(session()->all());
});
```

Visit `/session-test` when logged in. You'll see something like:

```json
{
    "_token": "abc123...",
    "_previous": {...},
    "_flash": {...},
    "login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d": 1
}
```

See that weird key? `login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d: 1`

That's your user ID stored in the session! The key is hashed for security.

**This is Laravel's version of `$_SESSION['user_id'] = 1`!**

---

## Quick Quiz

Test your understanding:

1. **What is a Guard?**
   - Defines HOW users are authenticated (session, token, etc.)

2. **What is a Provider?**
   - Defines WHERE to retrieve users from (database, Eloquent, API, etc.)

3. **What does `Auth::attempt()` do?**
   - Retrieves user by email
   - Verifies password with `Hash::check()`
   - If correct, logs user in with `Auth::login()`
   - Returns true/false

4. **What does `Auth::check()` do?**
   - Returns `true` if user is logged in, `false` otherwise
   - Internally calls `Auth::user()` and checks if it's not null

5. **How does "Remember Me" work?**
   - Generates random token
   - Stores in database `remember_token` column
   - Sets long-lived cookie
   - On return, validates token and auto-logs in

6. **What does the `auth` middleware do?**
   - Checks if user is logged in with `Auth::check()`
   - If not, throws exception and redirects to login
   - If yes, allows request to continue

---

## Deep Dive: The AuthManager Class

Want to see the actual Laravel code? Let's peek at the `AuthManager`:

```php
// vendor/laravel/framework/src/Illuminate/Auth/AuthManager.php

class AuthManager
{
    public function check()
    {
        return ! is_null($this->user());
    }

    public function user()
    {
        return $this->guard()->user();
    }

    public function id()
    {
        return $this->guard()->id();
    }

    public function attempt(array $credentials = [], $remember = false)
    {
        return $this->guard()->attempt($credentials, $remember);
    }

    public function login(Authenticatable $user, $remember = false)
    {
        $this->guard()->login($user, $remember);
    }

    public function logout()
    {
        $this->guard()->logout();
    }

    public function guard($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->guards[$name] ?? $this->guards[$name] = $this->resolve($name);
    }
}
```

See? It's just PHP classes and methods! Nothing magical.

---

## What's Next?

In **Lesson 03 - Login, Register, and Password Reset**, we'll:
- Examine Breeze's auth controllers in detail
- Understand form validation
- Explore email verification
- Test password reset flows
- Customize the auth views

---

## Key Takeaways

1. **Laravel's auth system has three layers:**
   - Guards (HOW to authenticate)
   - Providers (WHERE to get users)
   - The Auth facade (interface to everything)

2. **The Auth facade provides a clean API:**
   - `Auth::check()` - Is user logged in?
   - `Auth::user()` - Get current user
   - `Auth::login()` - Log a user in
   - `Auth::logout()` - Log a user out
   - `Auth::attempt()` - Try to log in with credentials

3. **Sessions work the same as Module 07:**
   - User ID stored in session
   - Session cookie sent to browser
   - Laravel just wraps it in a better API

4. **Remember Me works the same too:**
   - Random token stored in database
   - Cookie with token sent to browser
   - On return, token validated and user logged in

5. **Middleware protects routes:**
   - `auth` middleware checks `Auth::check()`
   - Redirects to login if not authenticated
   - Same concept as your `requireAuth()` function

6. **Everything you built in Module 07 is here:**
   - Just with better organization
   - More features
   - Cleaner code
   - But the core concepts are identical!

You now understand how Laravel's authentication works at a deep level. In the next lesson, we'll explore the actual implementation in Breeze's controllers and views.
