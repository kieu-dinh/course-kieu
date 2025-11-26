# Lesson 01 - Laravel Introduction and Installation

**Duration**: 45 minutes
**Difficulty**: Beginner

---

## Welcome to Laravel!

**Remember all those hours coding authentication systems, database connections, session handling, and routing in Modules 04-10?**

Laravel is about to change everything.

But here's the important part: **You needed to learn pure PHP first.** Why?

- **Understanding vs Magic**: You know what happens behind Laravel's "magic"
- **Debugging**: When things break (and they will), you'll know how to fix them
- **Appreciation**: You'll truly appreciate what Laravel does for you
- **Confidence**: You're not dependent on the framework - you understand the fundamentals

---

## What is Laravel?

Laravel is a **PHP framework** created by Taylor Otwell in 2011. It's now the most popular PHP framework in the world.

### Framework vs Library

**Library**: A collection of functions/classes you call when needed
- Example: `password_hash()`, `PDO`
- You're in control
- You call the library code

**Framework**: A complete structure that calls YOUR code
- Example: Laravel, Symfony
- The framework is in control
- The framework calls your code

Think of it this way:
- **Library**: You're building a house and you buy a hammer (tool)
- **Framework**: You're building a house and someone gives you the foundation, walls, and roof - you just decorate and customize

---

## Why Laravel?

### 1. Developer Happiness

Taylor Otwell's philosophy: **"Make developers happy."**

Laravel makes common tasks easy:

**Pure PHP (Module 07 - Authentication):**
```php
// Login logic - 50+ lines
session_start();

// Validate input
if (empty($_POST['email']) || empty($_POST['password'])) {
    $errors[] = "Email and password required";
}

// Sanitize
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

// Connect to database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=myapp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Query user
$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify password
if ($user && password_verify($_POST['password'], $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    header('Location: dashboard.php');
    exit;
} else {
    $errors[] = "Invalid credentials";
}
```

**Laravel:**
```php
// Login logic - 1 line
if (Auth::attempt(['email' => $email, 'password' => $password])) {
    return redirect()->route('dashboard');
}
```

**That's it.** Laravel handles:
- Input validation (we'll add)
- Database connection
- Query preparation
- Password verification
- Session creation
- CSRF protection
- Redirect

### 2. Beautiful Syntax

Laravel code reads like English:

```php
// Get all published posts by author, newest first
$posts = Post::where('published', true)
    ->where('author_id', $authorId)
    ->orderBy('created_at', 'desc')
    ->get();

// Compare to pure PHP SQL:
$sql = "SELECT * FROM posts
        WHERE published = 1
        AND author_id = ?
        ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$authorId]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

### 3. Batteries Included

Laravel includes everything you need:
- **Routing**: Clean URL handling
- **Authentication**: Complete auth system out-of-the-box
- **Database**: Query builder and ORM (Eloquent)
- **Templates**: Blade templating engine
- **Testing**: PHPUnit integration
- **Email**: Easy email sending
- **File Storage**: Local and cloud storage
- **Queues**: Background job processing
- **Caching**: Multiple cache backends
- **API**: API development tools

### 4. Massive Ecosystem

- **Laravel Herd**: Local development (you're already using it!)
- **Forge**: Server management
- **Envoyer**: Zero-downtime deployment
- **Nova**: Admin panel
- **Livewire**: Full-stack framework without JavaScript
- **Inertia**: Modern SPA with Laravel backend

### 5. Great Documentation

Laravel has the best documentation of any PHP framework:
- Clear examples
- Well organized
- Regular updates
- Video tutorials (Laracasts)

---

## Laravel vs Other Frameworks

### Laravel vs Symfony

**Symfony**: More enterprise-focused, steeper learning curve
**Laravel**: More developer-friendly, easier to start

Laravel actually uses many Symfony components under the hood!

### Laravel vs CodeIgniter

**CodeIgniter**: Lighter, older, less features
**Laravel**: Modern, feature-rich, active development

### Laravel vs WordPress

**WordPress**: CMS (Content Management System) for blogs/websites
**Laravel**: Application framework for custom web applications

Different tools for different jobs!

---

## MVC Architecture

Laravel follows the **MVC pattern** (Model-View-Controller) you learned about in Module 05.

Let's see how it works in Laravel:

```
User requests: /posts/123
       ↓
    ROUTES (web.php)
       ↓
  CONTROLLER (PostController)
       ↓
    MODEL (Post.php)
       ↓
  DATABASE (MySQL)
       ↓
    MODEL (Post.php)
       ↓
  CONTROLLER (PostController)
       ↓
     VIEW (show.blade.php)
       ↓
    HTML Response
```

### Real Example

**Pure PHP Structure (Module 06):**
```
/public
  /posts
    index.php       # List posts
    show.php        # Show one post
    create.php      # Create form
    store.php       # Process creation
    edit.php        # Edit form
    update.php      # Process update
    delete.php      # Delete post
```

**Laravel Structure:**
```
/routes
  web.php           # All routes defined here

/app/Http/Controllers
  PostController.php  # All post logic here

/app/Models
  Post.php          # Post model

/resources/views/posts
  index.blade.php   # List posts
  show.blade.php    # Show one post
  create.blade.php  # Create form
  edit.blade.php    # Edit form
```

Much more organized!

---

## Installation Prerequisites

Before installing Laravel, make sure you have:

### 1. PHP 8.2+

Check your PHP version:
```bash
php -v
```

You should see something like:
```
PHP 8.3.13 (cli) (built: Nov  7 2024 19:12:12) (NTS)
```

**You're using Laravel Herd, so you already have PHP 8.3!**

### 2. Composer

Composer is PHP's package manager (like npm for JavaScript).

Check if Composer is installed:
```bash
composer --version
```

You should see:
```
Composer version 2.8.4 2024-11-13 17:11:37
```

**Herd includes Composer, so you're good!**

### 3. Database

Laravel supports:
- MySQL
- PostgreSQL
- SQLite
- SQL Server

**Herd includes MySQL, so you're ready!**

---

## Creating Your First Laravel Project

### Method 1: Laravel Installer (Recommended with Herd)

First, install the Laravel installer globally:

```bash
composer global require laravel/installer
```

Create a new project:

```bash
# Navigate to your course directory
cd /Users/pouget/Projects/cours-kieu/14-laravel-basics/exercises

# Create new Laravel project
laravel new my-first-app
```

### Method 2: Via Composer Create-Project

```bash
cd /Users/pouget/Projects/cours-kieu/14-laravel-basics/exercises

composer create-project laravel/laravel my-first-app
```

Both methods create the same result!

### What Happens During Installation?

When you run the command, Composer:
1. Downloads Laravel and all its dependencies (~80 packages)
2. Creates the folder structure
3. Generates application key (.env)
4. Creates configuration files
5. Sets up basic structure

This takes **2-3 minutes**.

---

## Starting Your Laravel App

### With Laravel Herd

Since you're using Herd, it's incredibly easy:

1. **Herd automatically serves all projects** in your Herd directory
2. Your app is available at: `http://my-first-app.test`

That's it! No configuration needed.

### Without Herd (For Reference)

If you weren't using Herd, you'd run:

```bash
php artisan serve
```

This starts a development server at `http://localhost:8000`

---

## Your First Laravel View

Let's verify everything works:

1. Open your browser
2. Visit: `http://my-first-app.test`
3. You should see the **Laravel welcome page**

**Congratulations! Laravel is installed!**

---

## Understanding What You Just Created

Let's explore the structure:

```
my-first-app/
├── app/                # Your application code
│   ├── Http/
│   │   └── Controllers/   # Controllers go here
│   ├── Models/         # Models go here
│   └── Providers/      # Service providers
├── bootstrap/          # Framework bootstrapping
├── config/             # Configuration files
├── database/           # Migrations, seeds, factories
│   ├── migrations/
│   └── seeders/
├── public/             # Public files (index.php, CSS, JS, images)
├── resources/          # Views, raw assets
│   ├── views/          # Blade templates
│   ├── css/
│   └── js/
├── routes/             # Route definitions
│   ├── web.php         # Web routes
│   └── api.php         # API routes
├── storage/            # Logs, cache, uploads
├── tests/              # Tests
├── vendor/             # Composer dependencies (don't edit!)
├── .env                # Environment configuration
├── artisan             # Laravel CLI
├── composer.json       # PHP dependencies
└── package.json        # JavaScript dependencies
```

### Key Files You'll Use Most

**`.env`** - Environment configuration (database, mail, etc.)
**`routes/web.php`** - Define your web routes
**`app/Http/Controllers/`** - Your controllers
**`app/Models/`** - Your models
**`resources/views/`** - Your views
**`database/migrations/`** - Database structure

---

## The .env File

Open `.env` in your project:

```bash
code my-first-app/.env
```

This file contains **environment-specific configuration**:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:randomkeyhere
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

**Important:**
- **Never commit `.env` to Git!** (It's in `.gitignore`)
- Contains sensitive information (passwords, API keys)
- Each environment (local, staging, production) has its own `.env`

**Compare to Pure PHP:**

In Modules 06-10, you probably had:

```php
// config.php - committed to Git (dangerous!)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'myapp');
```

Laravel's approach is much better:
- `.env` is not committed
- `.env.example` (template) is committed
- Each developer/server has their own `.env`

---

## Comparison: Pure PHP vs Laravel Project Structure

### Pure PHP Project (Modules 04-10)

```
my-php-app/
├── public/
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── dashboard.php
│   └── posts/
│       ├── index.php
│       ├── show.php
│       ├── create.php
│       └── edit.php
├── includes/
│   ├── config.php
│   ├── db.php
│   ├── functions.php
│   └── header.php
├── classes/
│   ├── User.php
│   └── Post.php
└── templates/
    ├── header.php
    └── footer.php
```

**Problems:**
- No clear structure
- Mixing concerns (HTML + PHP + SQL)
- Each page creates DB connection
- No routing - files = URLs
- Repetitive code
- Hard to test

### Laravel Project

```
my-laravel-app/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   └── PostController.php
│   └── Models/
│       ├── User.php
│       └── Post.php
├── routes/
│   └── web.php
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   └── posts/
│       ├── index.blade.php
│       ├── show.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
└── .env
```

**Benefits:**
- Clear structure (MVC)
- Separation of concerns
- Single entry point (`public/index.php`)
- Route-based URLs
- Reusable code
- Easy to test

---

## Quick Wins: What You Can Do Immediately

### 1. Clean URLs (No .php!)

**Pure PHP:**
```
http://myapp.com/posts/show.php?id=123
```

**Laravel:**
```
http://myapp.com/posts/123
```

### 2. Database Queries

**Pure PHP:**
```php
$sql = "SELECT * FROM posts WHERE published = 1";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**Laravel:**
```php
$posts = Post::where('published', true)->get();
```

### 3. Templates

**Pure PHP:**
```php
<?php include 'header.php'; ?>
<h1><?= htmlspecialchars($title) ?></h1>
<?php include 'footer.php'; ?>
```

**Laravel Blade:**
```blade
@extends('layouts.app')

@section('content')
    <h1>{{ $title }}</h1>
@endsection
```

### 4. Form Validation

**Pure PHP (Module 04):**
```php
$errors = [];

if (empty($_POST['email'])) {
    $errors[] = "Email required";
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email";
}

if (strlen($_POST['password']) < 8) {
    $errors[] = "Password must be 8+ characters";
}

// ... more validation
```

**Laravel:**
```php
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

**Automatic error messages, automatic redirection back with errors!**

---

## Common Laravel Terminology

Before moving forward, let's define key terms:

**Artisan**: Laravel's command-line tool (we'll cover next lesson)
**Blade**: Laravel's templating engine
**Eloquent**: Laravel's ORM (Object-Relational Mapping)
**Migration**: Database version control
**Seeder**: Database dummy data
**Factory**: Model instance generator for testing
**Route**: URL endpoint definition
**Middleware**: Request filters (like authentication checks)
**Service Provider**: Class that bootstraps application services

Don't worry if these don't make sense yet - we'll cover each in detail!

---

## Your Learning Path

Here's what we'll cover in Module 14:

**Lesson 01** (this lesson): Introduction and installation ✓
**Lesson 02**: Laravel structure and Artisan CLI
**Lesson 03**: Routing basics
**Lesson 04**: Controllers
**Lesson 05**: Views and Blade templates
**Lesson 06**: Blade directives and layouts
**Lesson 07**: Migrations and databases
**Lesson 08**: Models introduction
**Lesson 09**: Request and Response
**Lesson 10**: Middleware basics

By the end, you'll rebuild features from Modules 04-10 in Laravel and see the difference!

---

## Practice: Explore Your Laravel Installation

### Task 1: Count the Files

```bash
cd my-first-app
find . -name "*.php" | wc -l
```

**How many PHP files?** Probably around 200+!

Don't worry - you won't write most of these. They're framework code.

### Task 2: Check Dependencies

```bash
cat composer.json
```

Look at the `require` section. Laravel depends on many packages!

### Task 3: View the Welcome Page

Visit `http://my-first-app.test` (or your URL)

Open Chrome DevTools (Cmd+Option+I):
- Check the Network tab
- Refresh the page
- Look at the response time

**Laravel is fast!**

---

## Summary

**What You Learned:**
- Why Laravel exists and its philosophy
- Benefits of using a framework
- How Laravel compares to pure PHP
- How to install Laravel
- Basic Laravel project structure
- The role of `.env` file
- MVC architecture in Laravel

**Key Takeaways:**
1. **Frameworks save time** but you need fundamentals first (you have them!)
2. **Laravel handles common tasks** so you focus on business logic
3. **MVC separates concerns** making code maintainable
4. **`.env` keeps secrets safe** and environment-specific config
5. **Laravel Herd makes development easy** - automatic HTTPS domains!

**Next Lesson:** We'll explore the Laravel file structure in detail and learn about Artisan, Laravel's powerful CLI tool.

---

## Quick Quiz

Test your understanding:

**1. What is the main difference between a library and a framework?**
- Library: You call it
- Framework: It calls you

**2. What does MVC stand for?**
- Model-View-Controller

**3. Where should database passwords go in Laravel?**
- `.env` file (never committed to Git)

**4. What command creates a new Laravel project?**
- `laravel new project-name` or
- `composer create-project laravel/laravel project-name`

**5. True or False: Laravel invented PHP's password hashing.**
- False! Laravel uses PHP's built-in `password_hash()` and `password_verify()` that you learned in Module 07.

---

## Resources

**Official Documentation**: https://laravel.com/docs
**Laracasts**: https://laracasts.com (video tutorials)
**Laravel News**: https://laravel-news.com
**Laravel Daily**: https://laraveldaily.com

**Next**: [Lesson 02 - Laravel Structure and Artisan →](02-laravel-structure-artisan.md)
