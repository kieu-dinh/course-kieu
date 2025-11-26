# Lesson 01 - Laravel Breeze Installation & Setup

**Duration**: 60 minutes
**Objectives**: Install Laravel Breeze, understand what it provides, and compare to the auth system you built in Module 07

---

## Remember Module 07?

Before we dive in, let's have a moment of appreciation. In Module 07, you built an entire authentication system from scratch:

- Session management
- Password hashing
- Login/registration forms
- "Remember me" functionality
- Password reset flows
- Middleware protection
- Security measures

**That took you 2-3 weeks and hundreds of lines of code.**

Today, you'll install Laravel Breeze and get all of that (and more) in about 5 minutes.

But here's the important part: **You'll understand exactly what it's doing because you've already built it yourself!**

---

## What is Laravel Breeze?

Laravel Breeze is an official starter kit that provides:
- Complete authentication scaffolding
- Login, registration, password reset, email verification
- Profile management
- Pre-built views (Blade templates)
- Route definitions
- Controllers
- Middleware
- Password confirmation flows

It's called "Breeze" because it makes authentication a breeze (get it?).

### Breeze vs Other Options

Laravel offers several auth options:

1. **Laravel Breeze** (What we're using)
   - Minimal, simple implementation
   - Perfect for learning
   - Uses Blade templates + Tailwind CSS
   - Can use Alpine.js or React/Vue

2. **Laravel Jetstream**
   - More features (teams, two-factor auth, API tokens)
   - More complex
   - Uses Livewire or Inertia.js
   - Overkill for most projects

3. **Build it yourself**
   - What you did in Module 07!
   - Complete control
   - More work

**For this course, we use Breeze because it's simple, well-documented, and shows Laravel's conventions clearly.**

---

## Installation Steps

### Step 1: Create a Fresh Laravel Project

Let's start with a completely new Laravel project for this module:

```bash
cd /Users/pouget/Projects/cours-kieu/16-laravel-auth/exercises
laravel new auth-app
cd auth-app
```

This creates a fresh Laravel 11 installation.

### Step 2: Install Laravel Breeze

Now comes the magic:

```bash
composer require laravel/breeze --dev
```

This installs Breeze as a development dependency. The `--dev` flag means it's only needed during development (not on production servers).

**What just happened?**
- Composer downloaded the `laravel/breeze` package
- It was added to your `composer.json` file
- All its dependencies were installed

### Step 3: Install Breeze Scaffolding

Now we tell Breeze to install all its authentication files:

```bash
php artisan breeze:install
```

You'll be prompted with several questions:

```
Which Breeze stack would you like to install?
  blade ...................... (Default - recommended for this course)
  livewire ................... (We'll cover this in Module 17!)
  react ...................... (Not covered in this course)
  vue ........................ (Not covered in this course)
  api ........................ (For Lesson 08 of this module)
```

**Choose `blade` for now.** We'll explore the API option in Lesson 08.

Next question:

```
Would you like dark mode support? (yes/no) [no]
```

Type `yes` - dark mode is a nice feature to have!

Next:

```
Which testing framework do you prefer?
  Pest ...................... (Modern, cleaner syntax)
  PHPUnit ................... (Traditional)
```

Choose **Pest** - we'll cover testing later, and Pest is more beginner-friendly.

**What just happened?**

Breeze just created a TON of files for you. Let's explore them.

### Step 4: Install Frontend Dependencies

Breeze uses Tailwind CSS for styling. Install the Node dependencies:

```bash
npm install
npm run dev
```

Keep this running in a terminal tab. It watches for CSS/JS changes and rebuilds them automatically.

### Step 5: Configure Your Database

Open `.env` and make sure your database is configured:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=auth_app
DB_USERNAME=root
DB_PASSWORD=
```

If you're using Laravel Herd (which you should be), create the database:

```bash
# In TablePlus or your MySQL client:
CREATE DATABASE auth_app;
```

Or use the command line:

```bash
mysql -u root -e "CREATE DATABASE auth_app"
```

### Step 6: Run Migrations

Breeze comes with database migrations for the `users` table and password reset tokens:

```bash
php artisan migrate
```

**What just got created?**

```sql
-- users table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- password_reset_tokens table
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- sessions table (for database session driver)
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL
);
```

Sound familiar? This is almost identical to what you built in Module 07!

### Step 7: Start the Server and Test

Make sure Herd is running, or start the development server:

```bash
php artisan serve
```

Visit `http://localhost:8000` (or your Herd domain).

You should see:
- A "Login" link in the top right
- A "Register" link

**Click around! Try registering an account!**

---

## What Files Did Breeze Create?

Let's explore the massive amount of code Breeze just gave you for free.

### 1. Routes (`routes/auth.php`)

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

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

**Compare to Module 07:**
Remember creating all these routes yourself? `login.php`, `register.php`, `logout.php`, `forgot-password.php`, `reset-password.php`? Laravel does it all with named routes and RESTful conventions.

### 2. Controllers (`app/Http/Controllers/Auth/`)

Breeze created 9 controllers:

```
app/Http/Controllers/Auth/
├── AuthenticatedSessionController.php     (Login/Logout)
├── ConfirmablePasswordController.php      (Password confirmation for sensitive actions)
├── EmailVerificationNotificationController.php
├── EmailVerificationPromptController.php
├── NewPasswordController.php              (Reset password)
├── PasswordController.php                 (Update password)
├── PasswordResetLinkController.php        (Forgot password)
├── RegisteredUserController.php           (Registration)
└── VerifyEmailController.php              (Email verification)
```

Let's look at one - `RegisteredUserController.php`:

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
    public function create(): View
    {
        return view('auth.register');
    }

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

**Compare to your Module 07 registration code:**
- Validation is declarative and clean
- `Hash::make()` instead of `password_hash()`
- `Auth::login()` instead of manual session setting
- Events system (`Registered` event) for extensibility
- Type hints everywhere
- Named routes for redirects

### 3. Views (`resources/views/auth/`)

```
resources/views/auth/
├── confirm-password.blade.php
├── forgot-password.blade.php
├── login.blade.php
├── register.blade.php
├── reset-password.blade.php
└── verify-email.blade.php
```

And layout files:

```
resources/views/
├── layouts/
│   ├── app.blade.php        (Main layout for authenticated users)
│   └── guest.blade.php      (Layout for login/register pages)
├── profile/
│   └── edit.blade.php       (Profile management)
└── dashboard.blade.php      (Default dashboard)
```

### 4. Middleware

Check `bootstrap/app.php` - Breeze configured middleware aliases:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
        // ... more
    ]);
})
```

**Remember your `requireAuth()` and `requireGuest()` functions from Module 07?** Laravel has built-in middleware for this!

---

## Comparing: Module 07 vs Laravel Breeze

Let's do a side-by-side comparison to appreciate what Laravel gives you.

### Registration

**Module 07 (Pure PHP):**

```php
<?php
// register.php
session_start();
require_once 'includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    $errors = [];

    // Validation
    if (!$email) {
        $errors[] = "Invalid email address";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    if ($password !== $password_confirm) {
        $errors[] = "Passwords do not match";
    }

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        $errors[] = "Email already registered";
    }

    if (empty($errors)) {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
        $stmt->execute([
            'email' => $email,
            'password' => $hashedPassword
        ]);

        // Log user in
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['email'] = $email;

        header('Location: dashboard.php');
        exit;
    }
}
?>
```

**Laravel Breeze:**

```php
<?php
// app/Http/Controllers/Auth/RegisteredUserController.php

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
```

**Wow.** Look at how much cleaner that is!

### Login

**Module 07:**

```php
<?php
// login.php
session_start();
require_once 'includes/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID (security)
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];

            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Invalid credentials";
        }
    }
}
?>
```

**Laravel Breeze:**

```php
<?php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php

public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    return redirect()->intended(route('dashboard', absolute: false));
}
```

**Just 3 lines!** And look - it even handles `intended()` redirect (remember the protected page flow?).

### Protected Routes

**Module 07:**

```php
<?php
// dashboard.php
session_start();
require_once 'includes/middleware.php';

requireAuth(); // Custom function you wrote

// Rest of your page code
?>
```

**Laravel Breeze:**

```php
// routes/web.php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
```

Middleware applied declaratively to route groups!

---

## Understanding What Just Happened

When you ran `php artisan breeze:install`, Laravel:

1. **Created controllers** - All auth logic organized by responsibility
2. **Created views** - Beautiful Blade templates with Tailwind CSS
3. **Created routes** - RESTful, named routes following Laravel conventions
4. **Configured middleware** - Auth protection ready to use
5. **Set up validation** - Form request classes with validation rules
6. **Added tests** - Feature tests for all auth flows (we'll use these later!)
7. **Configured session** - Secure session handling out of the box

**All the things you spent weeks building in Module 07, automatically!**

---

## Testing Your Installation

Let's make sure everything works:

### 1. Register a New Account

1. Visit `http://localhost:8000/register`
2. Fill out the form:
   - Name: Your Name
   - Email: test@example.com
   - Password: password123
   - Confirm Password: password123
3. Click "Register"

You should be:
- Logged in automatically
- Redirected to `/dashboard`
- See a welcome message with your name

### 2. Check the Database

Open TablePlus and look at the `users` table:

```sql
SELECT * FROM users;
```

You'll see:
- Your user record
- Password is hashed (bcrypt)
- `email_verified_at` is NULL (we haven't verified yet)
- `remember_token` is NULL (we haven't checked "remember me" yet)

### 3. Logout and Login

1. Click "Log Out" in the navigation
2. You're redirected to the home page
3. Click "Log In"
4. Enter your credentials
5. You're back at the dashboard!

### 4. Test Password Reset

1. Click "Forgot your password?" on the login page
2. Enter your email
3. You'll see a message about the password reset link

**Note:** In development, Laravel doesn't actually send emails. It logs them to `storage/logs/laravel.log`. In Lesson 03, we'll configure Mailtrap to test email sending.

---

## Quick Quiz

Before moving to the next lesson, make sure you understand:

1. **What is Laravel Breeze?**
   - An official authentication starter kit
   - Provides complete auth scaffolding
   - Minimal and simple implementation

2. **What command installs Breeze?**
   - `composer require laravel/breeze --dev`
   - `php artisan breeze:install`

3. **What files does Breeze create?**
   - Controllers in `app/Http/Controllers/Auth/`
   - Views in `resources/views/auth/`
   - Routes in `routes/auth.php`
   - Migrations for users and password resets

4. **How is this different from Module 07?**
   - Much less code
   - Better organized (MVC pattern)
   - More features (email verification, password confirmation)
   - Laravel conventions and best practices
   - But the underlying concepts are the same!

---

## Exercises

### Exercise 1: Explore the Code

Open these files and read through them:
- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `routes/auth.php`

**Questions to think about:**
- How does Laravel validate the registration form?
- Where is the password hashed?
- How does `Auth::login()` work? (We'll dive deeper in Lesson 02)
- What does `event(new Registered($user))` do?

### Exercise 2: Compare to Your Module 07 Code

Pull up your Module 07 auth system. Compare:
- Your `register.php` to `RegisteredUserController.php`
- Your `login.php` to `AuthenticatedSessionController.php`
- Your `logout.php` to the `destroy` method

**Write down:**
- What's the same?
- What's different?
- What does Laravel do better?
- What do you understand now that you didn't before Module 07?

### Exercise 3: Customize the Dashboard

Open `resources/views/dashboard.blade.php`. Try adding:
- A welcome message with the user's name
- The user's email
- The date they registered

**Hint:** You have access to `auth()->user()` in Blade templates!

```blade
<p>Welcome, {{ auth()->user()->name }}!</p>
```

---

## What's Next?

In **Lesson 02 - Authentication Scaffolding Explained**, we'll dive deep into:
- How Laravel's auth system works internally
- The `Auth` facade
- User providers and guards
- Session management
- The authentication flow step-by-step

You'll understand exactly what happens when you call `Auth::login()` and `Auth::check()`.

---

## Key Takeaways

1. **Laravel Breeze provides complete authentication in minutes** - What took weeks in Module 07 takes 5 minutes with Breeze

2. **But you understand what it's doing** - Because you built it yourself in pure PHP

3. **Less code, more features** - Breeze includes email verification, password confirmation, and more

4. **Laravel conventions** - See how Laravel structures auth: controllers, routes, middleware, views

5. **This is the power of frameworks** - They provide tested, secure, conventional solutions to common problems

6. **But frameworks aren't magic** - Every line of Breeze code is PHP you could have written yourself (and basically did in Module 07!)

Now you appreciate both approaches: the deep understanding from building it yourself, and the productivity boost from using a framework.

Let's keep going!
