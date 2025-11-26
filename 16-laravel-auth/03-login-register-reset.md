# Lesson 03 - Login, Register, and Password Reset Deep Dive

**Duration**: 90 minutes
**Objectives**: Understand Breeze's authentication controllers, form validation, email verification, and password reset flows

---

## Overview

In Lesson 02, you learned HOW Laravel's auth system works internally. Now let's examine the actual implementation in the controllers, requests, and views that Breeze created.

We'll cover:
1. Registration flow
2. Login flow
3. Password reset flow
4. Email verification
5. Form validation
6. Customizing the views

---

## 1. Registration Flow

Let's trace through the complete registration process.

### The Registration Controller

Open `app/Http/Controllers/Auth/RegisteredUserController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
```

### Breaking Down the `store` Method

**Step 1: Validation**

```php
$request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);
```

Let's examine each validation rule:

**Name:**
- `required` - Must be present
- `string` - Must be a string
- `max:255` - Maximum 255 characters

**Email:**
- `required` - Must be present
- `string` - Must be a string
- `lowercase` - Automatically converts to lowercase
- `email` - Must be a valid email format
- `max:255` - Maximum 255 characters
- `unique:User` - Must not exist in the `users` table

**Password:**
- `required` - Must be present
- `confirmed` - Must have a matching `password_confirmation` field
- `Rules\Password::defaults()` - Uses Laravel's password rules

**What are the default password rules?**

Check `config/auth.php` or `Illuminate\Validation\Rules\Password`:

```php
Password::min(8)  // At least 8 characters
    ->letters()   // At least one letter
    ->mixedCase() // At least one uppercase and one lowercase
    ->numbers()   // At least one number
    ->symbols()   // At least one symbol
    ->uncompromised(); // Not in a data breach
```

You can customize these in `App\Providers\AppServiceProvider`:

```php
use Illuminate\Validation\Rules\Password;

public function boot(): void
{
    Password::defaults(function () {
        return Password::min(8)
            ->letters()
            ->numbers()
            ->mixedCase()
            ->symbols();
    });
}
```

**Compare to Module 07:**

```php
// Your validation code
$errors = [];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

if ($password !== $password_confirm) {
    $errors[] = "Passwords do not match";
}

// Check unique email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    $errors[] = "Email already exists";
}
```

Laravel's validation is so much cleaner!

**Step 2: Create User**

```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
]);
```

**What's happening:**

1. `User::create()` - Eloquent mass assignment (remember Module 15?)
2. `Hash::make()` - Hashes the password using bcrypt

Remember from Lesson 02, the `User` model has:

```php
protected function casts(): array
{
    return [
        'password' => 'hashed',
    ];
}
```

So you could actually just do:

```php
$user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => $request->password, // Automatically hashed!
]);
```

But Breeze uses `Hash::make()` explicitly for clarity.

**Compare to Module 07:**

```php
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
$stmt->execute([
    'email' => $email,
    'password' => $hashedPassword
]);

$userId = $pdo->lastInsertId();
```

Same concept, cleaner syntax!

**Step 3: Fire Registered Event**

```php
event(new Registered($user));
```

This fires the `Registered` event, which triggers:
- Email verification notification (if enabled)
- Any custom listeners you've registered

**Events** are Laravel's way of decoupling code. Instead of cluttering the controller with email sending logic, we fire an event and let listeners handle it.

**Step 4: Log User In**

```php
Auth::login($user);
```

As we learned in Lesson 02, this:
- Stores user ID in session
- Regenerates session ID (security)
- Sets up the authentication state

**Step 5: Redirect**

```php
return redirect(route('dashboard', absolute: false));
```

Redirect to the dashboard using named routes.

### The Registration View

Open `resources/views/auth/register.blade.php`:

```blade
<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
```

**Key points:**

1. **Blade Components:** `<x-guest-layout>`, `<x-input-label>`, `<x-text-input>`, etc.
2. **CSRF Token:** `@csrf` protects against CSRF attacks (Module 08!)
3. **Old Input:** `:value="old('name')"` repopulates form on validation errors
4. **Error Messages:** `<x-input-error :messages="$errors->get('name')" />`
5. **Translation:** `__('Name')` allows for internationalization

---

## 2. Login Flow

Now let's examine the login process.

### The Login Controller

Open `app/Http/Controllers/Auth/AuthenticatedSessionController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login form.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
```

Notice the controller is very clean! The logic is in `LoginRequest`.

### The Login Request

Open `app/Http/Requests/Auth/LoginRequest.php`:

```php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     */
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

    /**
     * Ensure the login request is not rate limited.
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
```

### Breaking Down the `authenticate` Method

**Step 1: Rate Limiting Check**

```php
$this->ensureIsNotRateLimited();
```

This prevents brute force attacks by limiting login attempts:
- Maximum 5 attempts per email + IP address
- If exceeded, throws validation error with lockout time

**Compare to Module 07:**
You probably didn't implement rate limiting in Module 07 (it's complex!). Laravel gives you this for free.

**Step 2: Attempt Authentication**

```php
if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
    RateLimiter::hit($this->throttleKey());

    throw ValidationException::withMessages([
        'email' => trans('auth.failed'),
    ]);
}
```

- `Auth::attempt()` - Try to log in (we covered this in Lesson 02)
- If failed, increment rate limiter
- Throw validation error

**Step 3: Clear Rate Limiter**

```php
RateLimiter::clear($this->throttleKey());
```

If login succeeds, clear the failed attempt counter.

### The Login View

Open `resources/views/auth/login.blade.php`:

```blade
<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
```

Notice the "Remember Me" checkbox - this is passed to `Auth::attempt()` as the second parameter.

---

## 3. Password Reset Flow

This is a multi-step process that's complex in pure PHP but elegant in Laravel.

### The Flow

1. User clicks "Forgot Password"
2. User enters email
3. Laravel sends password reset link via email
4. User clicks link in email
5. User enters new password
6. Password is updated, user redirected to login

### Step 1 & 2: Request Password Reset

**Controller:** `PasswordResetLinkController.php`

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request form.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }
}
```

**What does `Password::sendResetLink()` do?**

1. **Checks if email exists:**
   ```php
   $user = User::where('email', $email)->first();
   ```

2. **Generates a random token:**
   ```php
   $token = Str::random(64);
   ```

3. **Stores token in `password_reset_tokens` table:**
   ```php
   DB::table('password_reset_tokens')->insert([
       'email' => $email,
       'token' => Hash::make($token),
       'created_at' => now(),
   ]);
   ```

4. **Sends email with reset link:**
   ```php
   $user->sendPasswordResetNotification($token);
   ```

   The email contains a link like:
   ```
   http://your-app.test/reset-password/{token}?email=user@example.com
   ```

**Compare to Module 07:**

Remember in Lesson 09, you had to:
- Manually generate a token
- Store it in the database with expiration
- Build the reset URL
- Simulate sending an email

Laravel does all of this for you!

### Step 3 & 4: Click Link and Show Reset Form

**Route:**
```php
Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');
```

**Controller:**
```php
public function create(Request $request): View
{
    return view('auth.reset-password', ['request' => $request]);
}
```

The view receives:
- `$token` from the URL
- `$email` from the query string

### Step 5 & 6: Reset Password

**Controller:** `NewPasswordController.php`

```php
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'token' => ['required'],
        'email' => ['required', 'email'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) use ($request) {
            $user->forceFill([
                'password' => Hash::make($request->password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        }
    );

    return $status == Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
}
```

**What does `Password::reset()` do?**

1. **Validates the token:**
   ```php
   $record = DB::table('password_reset_tokens')
       ->where('email', $email)
       ->first();

   if (!$record || !Hash::check($token, $record->token)) {
       // Invalid token
   }
   ```

2. **Checks token expiration** (default 60 minutes):
   ```php
   if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
       // Token expired
   }
   ```

3. **Updates the password:**
   ```php
   $user->forceFill([
       'password' => Hash::make($newPassword),
       'remember_token' => Str::random(60), // Invalidate existing sessions
   ])->save();
   ```

4. **Deletes the reset token:**
   ```php
   DB::table('password_reset_tokens')->where('email', $email)->delete();
   ```

5. **Fires PasswordReset event**

---

## 4. Email Verification

Laravel includes email verification out of the box. Let's enable it!

### Step 1: Mark User Model as `MustVerifyEmail`

Open `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

Notice: `implements MustVerifyEmail`

### Step 2: Protect Routes with `verified` Middleware

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

Now users must verify their email before accessing the dashboard!

### The Email Verification Flow

1. **User registers**
2. **`Registered` event fires**
3. **Listener sends verification email** (automatically!)
4. **User clicks link in email**
5. **Email is marked as verified**
6. **User can access protected routes**

### Email Verification Controller

`VerifyEmailController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
```

**What happens:**

1. Check if already verified
2. If not, mark as verified: `$user->email_verified_at = now()`
3. Fire `Verified` event
4. Redirect to dashboard

### Resend Verification Email

`EmailVerificationNotificationController.php`:

```php
public function store(Request $request): RedirectResponse
{
    if ($request->user()->hasVerifiedEmail()) {
        return redirect()->intended(route('dashboard', absolute: false));
    }

    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');
}
```

---

## 5. Form Validation in Detail

Laravel's validation system is incredibly powerful. Let's explore it.

### Validation Rules

```php
$request->validate([
    'email' => ['required', 'email', 'unique:users'],
    'password' => ['required', 'min:8', 'confirmed'],
]);
```

**Common rules:**

| Rule | Description |
|------|-------------|
| `required` | Field must be present |
| `email` | Must be valid email |
| `min:8` | Minimum 8 characters |
| `max:255` | Maximum 255 characters |
| `confirmed` | Must have matching `_confirmation` field |
| `unique:users` | Must be unique in `users` table |
| `unique:users,email,{id}` | Unique except for user with `{id}` |
| `in:foo,bar` | Must be one of the values |
| `numeric` | Must be a number |
| `integer` | Must be an integer |
| `date` | Must be a valid date |
| `before:date` | Must be before date |
| `after:date` | Must be after date |

### Custom Validation Messages

```php
$request->validate([
    'email' => ['required', 'email'],
], [
    'email.required' => 'Please provide your email address',
    'email.email' => 'Please provide a valid email address',
]);
```

### Displaying Errors in Views

```blade
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Or per-field: -->
<x-input-error :messages="$errors->get('email')" />
```

### Old Input

When validation fails, Laravel automatically redirects back and preserves the input:

```blade
<input type="text" name="name" value="{{ old('name') }}" />
```

---

## 6. Customizing the Views

Let's make some customizations to understand how it all works.

### Customizing the Registration Form

Open `resources/views/auth/register.blade.php` and add a "Username" field:

```blade
<!-- Username -->
<div class="mt-4">
    <x-input-label for="username" :value="__('Username')" />
    <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required />
    <x-input-error :messages="$errors->get('username')" class="mt-2" />
</div>
```

Update the controller:

```php
// RegisteredUserController.php

public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'username' => ['required', 'string', 'max:255', 'unique:users'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // ...
}
```

Add migration:

```bash
php artisan make:migration add_username_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('username')->unique()->after('name');
    });
}
```

Run it:

```bash
php artisan migrate
```

Update the `User` model:

```php
protected $fillable = [
    'name',
    'username',
    'email',
    'password',
];
```

Done! Now you have a username field.

### Customizing the Login Process

Want to allow login with username OR email?

Update `LoginRequest.php`:

```php
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    $credentials = $this->only('email', 'password');

    // Check if it's an email or username
    $field = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    if (! Auth::attempt([$field => $this->email, 'password' => $this->password], $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

Update the view label:

```blade
<x-input-label for="email" :value="__('Email or Username')" />
```

Now users can log in with either!

---

## Quick Quiz

Test your understanding:

1. **What does `Hash::make()` do?**
   - Hashes a password using bcrypt (same as `password_hash()`)

2. **What does `Auth::attempt()` return?**
   - `true` if authentication succeeded, `false` otherwise

3. **How does Laravel prevent brute force login attacks?**
   - Rate limiting with `RateLimiter` (max 5 attempts per email+IP)

4. **What is the `confirmed` validation rule?**
   - Checks for a matching `_confirmation` field (e.g., `password_confirmation`)

5. **How do you enable email verification?**
   - Add `implements MustVerifyEmail` to the User model
   - Protect routes with `verified` middleware

6. **How long are password reset tokens valid?**
   - 60 minutes by default (configurable in `config/auth.php`)

---

## Exercises

### Exercise 1: Add Phone Number to Registration

1. Add a `phone` field to the registration form
2. Add validation (required, numeric, 10 digits)
3. Create migration to add `phone` column to users table
4. Update the controller to save the phone number
5. Display it on the dashboard

### Exercise 2: Customize Password Rules

Make passwords require:
- Minimum 10 characters
- At least one uppercase letter
- At least one number
- At least one symbol

### Exercise 3: Customize Login Error Messages

Change the error messages to be more user-friendly:
- "Invalid credentials" → "The email or password you entered is incorrect"
- "Too many attempts" → "Too many failed login attempts. Please try again in {minutes} minutes"

---

## What's Next?

In **Lesson 04 - Auth Middleware**, we'll dive deep into:
- How middleware works
- Creating custom middleware
- Middleware groups
- Protecting routes
- Redirecting guests and authenticated users

---

## Key Takeaways

1. **Breeze provides complete auth scaffolding:**
   - Registration with validation
   - Login with rate limiting
   - Password reset with email
   - Email verification

2. **Form Request classes encapsulate validation:**
   - `LoginRequest` handles login validation and authentication
   - Keeps controllers clean and focused

3. **Laravel's validation is declarative and powerful:**
   - Dozens of built-in rules
   - Custom messages
   - Automatic error handling

4. **Password reset is a multi-step flow:**
   - Request reset → Send email → Validate token → Update password
   - All handled by Laravel's `Password` facade

5. **Email verification is built-in:**
   - Implement `MustVerifyEmail` interface
   - Protect routes with `verified` middleware
   - Automatic email sending

6. **Everything is customizable:**
   - Add fields to forms
   - Change validation rules
   - Modify the flow to fit your needs

You now understand how all of Breeze's authentication features work. In the next lesson, we'll explore middleware in depth!
