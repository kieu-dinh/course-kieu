# 01 - Install Breeze

## Objective
Install and configure Laravel Breeze to add complete authentication scaffolding to a Laravel application.

## Prerequisites
- Completed Module 14 exercises
- Understanding of Laravel directory structure
- Node.js installed for frontend build tools

## Instructions

### Step 1: Create a New Laravel Project
```bash
composer create-project laravel/laravel breeze-app
cd breeze-app
```

### Step 2: Install Laravel Breeze
Laravel Breeze is a minimal authentication scaffold:

```bash
composer require laravel/breeze --dev
php artisan breeze:install
```

You'll be prompted to choose a stack. Select one:
- Blade (simple, server-side)
- Livewire (interactive)
- Inertia (Vue.js based)

We'll use Blade for simplicity:

```bash
php artisan breeze:install blade
```

### Step 3: Install Frontend Dependencies
```bash
npm install
```

### Step 4: Build Frontend Assets
```bash
npm run build
```

For development with auto-reload:

```bash
npm run dev
```

### Step 5: Configure Database
Update `.env`:

```
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

Or for MySQL:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=breeze_app
DB_USERNAME=root
DB_PASSWORD=
```

### Step 6: Run Migrations
```bash
php artisan migrate
```

### Step 7: Start Development Server
In one terminal:

```bash
php artisan serve
```

In another terminal:

```bash
npm run dev
```

Visit `http://localhost:8000`

### Step 8: Explore the Generated Files
Breeze creates:

- `app/Http/Controllers/Auth/` - Authentication controllers
- `resources/views/auth/` - Login/register views
- `routes/auth.php` - Authentication routes
- `database/migrations/` - User migration

### Step 9: Test Authentication
- Click "Register" and create a new account
- Fill in name, email, and password
- Submit the form
- You should be logged in and redirected to dashboard
- Click on your name (top right) to see logout option

### Step 10: Examine User Model
Look at `app/Models/User.php`:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
```

### Step 11: Check Middleware
Examine `app/Http/Middleware/Authenticate.php` - this middleware protects routes.

### Step 12: Examine Routes
Check `routes/web.php`:

```php
Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

require __DIR__.'/auth.php';
```

The `auth` middleware protects the dashboard route.

### Step 13: Create Protected Routes
Add a protected route in `routes/web.php`:

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', function () {
        return view('profile.show');
    })->name('profile.show');
});
```

### Step 14: Create a Protected View
Create `resources/views/profile/show.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-4">Your Profile</h1>

                    <p><strong>Name:</strong> {{ auth()->user()->name }}</p>
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                    <p><strong>Member since:</strong> {{ auth()->user()->created_at->format('M d, Y') }}</p>

                    <a href="/dashboard" class="mt-4 inline-block px-4 py-2 bg-blue-500 text-white rounded">
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
```

### Step 15: Access Authenticated User
In controllers and views, access the authenticated user:

```php
// In controller
$user = auth()->user();
$user = Auth::user();

// In view
{{ auth()->user()->name }}

// Check if authenticated
@auth
    <p>Logged in as {{ auth()->user()->name }}</p>
@endauth

@guest
    <a href="/login">Login</a>
@endguest
```

### Step 16: Redirect Unauthenticated Users
The `auth` middleware automatically redirects unauthenticated users to login page.

Test by:
1. Logging out
2. Trying to access `/dashboard`
3. You should be redirected to `/login`

### Step 17: Configure Auth Redirects
Edit `app/Http/Middleware/Authenticate.php`:

```php
protected function redirectTo($request)
{
    if (!$request->expectsJson()) {
        return route('login');
    }
}
```

### Step 18: Customize Layout
The default Breeze layout is in `resources/views/layouts/app.blade.php`.

You can customize:
- Navigation bar
- Logo
- Colors
- Typography

### Step 19: Test Registration and Login Flow
1. Clear database: `php artisan migrate:refresh`
2. Go to `/register`
3. Create a new account
4. Verify you're redirected to dashboard
5. Visit `/profile` to see protected page
6. Logout and verify redirect to login
7. Try accessing `/dashboard` without login

### Step 20: Email Verification (Optional)
To require email verification:

Edit `app/Models/User.php`:

```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```

Run migration:

```bash
php artisan migrate
```

## Deliverables
- [ ] Laravel Breeze installed successfully
- [ ] Migrations run without errors
- [ ] User can register new account
- [ ] User can login and see dashboard
- [ ] User can logout
- [ ] Protected routes redirect to login
- [ ] Authenticated user can access profile
- [ ] User dropdown menu showing logout
- [ ] Multiple users can create accounts
- [ ] Password security verified

## Resources
- [Laravel Breeze Documentation](https://laravel.com/docs/11.x/starter-kits#breeze-installation)
- [Authentication](https://laravel.com/docs/11.x/authentication)
- [Authorization](https://laravel.com/docs/11.x/authorization)

## Tips
- Breeze is minimal and customizable
- It uses native Laravel authentication (no external packages)
- All generated code is in your project (not hidden)
- Customize views as needed for your design
- Email verification requires mail configuration
- Remember tokens allow "remember me" functionality
- Test authentication flows thoroughly before deploying
