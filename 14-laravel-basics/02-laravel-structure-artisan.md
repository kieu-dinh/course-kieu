# Lesson 02 - Laravel Structure and Artisan

**Duration**: 45 minutes
**Difficulty**: Beginner

---

## Understanding Laravel's File Structure

In pure PHP projects (Modules 04-10), you probably organized files however felt natural. Laravel has a **standard structure** that every Laravel developer follows.

**Why does structure matter?**
- **Consistency**: Any Laravel developer can navigate your project
- **Scalability**: Structure handles growth
- **Separation**: Different concerns stay separate
- **Automation**: Tools know where to generate files

Let's explore each directory!

---

## The `/app` Directory

This is **YOUR code** - the heart of your application.

```
app/
├── Console/           # Artisan commands
├── Exceptions/        # Exception handler
├── Http/
│   ├── Controllers/   # Your controllers (most used!)
│   ├── Middleware/    # Request filters
│   └── Requests/      # Form request validation
├── Models/            # Your models (most used!)
└── Providers/         # Service providers
```

### `/app/Http/Controllers/`

This is where **your controllers** live.

**Pure PHP Example (Module 05):**
```
/public
  /posts
    create.php     # Show form
    store.php      # Process form
    edit.php       # Show edit form
    update.php     # Process update
```

**Laravel:**
```
/app/Http/Controllers
  PostController.php   # All post-related actions in ONE file
```

**PostController.php:**
```php
class PostController extends Controller
{
    public function create()  // Show form
    public function store()   // Process form
    public function edit()    // Show edit form
    public function update()  // Process update
}
```

Much more organized!

### `/app/Models/`

Your **database models** (Eloquent).

**Pure PHP (Module 06):**
```php
// classes/Post.php
class Post {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function all() {
        $sql = "SELECT * FROM posts";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $sql = "SELECT * FROM posts WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ... more methods
}
```

**Laravel:**
```php
// app/Models/Post.php
class Post extends Model
{
    // That's it! Laravel generates methods automatically
}

// Usage:
$posts = Post::all();
$post = Post::find($id);
```

**Laravel's Eloquent** provides all common database operations automatically!

---

## The `/routes` Directory

Defines **how URLs map to controllers**.

```
routes/
├── web.php        # Browser routes (with sessions, CSRF)
├── api.php        # API routes (stateless, no CSRF)
├── console.php    # CLI commands
└── channels.php   # Broadcasting channels
```

### `/routes/web.php`

**Most important file!** This is where you define your web routes.

**Pure PHP:**
```
URL = File location
/posts/index.php  → Must create this file
/posts/show.php   → Must create this file
/posts/create.php → Must create this file
```

**Laravel:**
```php
// routes/web.php
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/create', [PostController::class, 'create']);
```

Benefits:
- **Clean URLs** (no .php extension)
- **Centralized** (all routes in one place)
- **Dynamic** (parameters in URL)
- **Named routes** (change URL without changing code)

### `/routes/api.php`

For building APIs (Module 09 - APIs).

```php
// routes/api.php
Route::get('/posts', [ApiPostController::class, 'index']);
Route::post('/posts', [ApiPostController::class, 'store']);
```

Routes here are automatically prefixed with `/api`:
- Result: `/api/posts`

We'll cover APIs in Module 19!

---

## The `/resources` Directory

Contains **views and raw assets**.

```
resources/
├── views/         # Blade templates (HTML)
│   ├── layouts/
│   ├── posts/
│   └── auth/
├── css/           # CSS files (compiled with Vite)
├── js/            # JavaScript files
└── lang/          # Translation files
```

### `/resources/views/`

Your **Blade templates** (HTML with Laravel syntax).

**Pure PHP (Module 02):**
```
/templates
  header.php
  footer.php
  post-list.php
```

Each file mixed PHP and HTML:
```php
<?php include 'header.php'; ?>
<h1><?= htmlspecialchars($title) ?></h1>
<?php foreach ($posts as $post): ?>
    <div><?= htmlspecialchars($post['title']) ?></div>
<?php endforeach; ?>
<?php include 'footer.php'; ?>
```

**Laravel:**
```
/resources/views
  /layouts
    app.blade.php      # Master layout
  /posts
    index.blade.php    # Post list
    show.blade.php     # Single post
```

**posts/index.blade.php:**
```blade
@extends('layouts.app')

@section('content')
    <h1>{{ $title }}</h1>
    @foreach($posts as $post)
        <div>{{ $post->title }}</div>
    @endforeach
@endsection
```

Cleaner, more readable!

---

## The `/public` Directory

The **only directory accessible** from the web.

```
public/
├── index.php      # Entry point (all requests go here)
├── .htaccess      # Apache configuration
├── css/           # Compiled CSS
├── js/            # Compiled JavaScript
└── images/        # Public images
```

### `/public/index.php`

**Single entry point** for all requests.

**Pure PHP:**
```
Direct file access:
/posts/show.php    → Executes show.php
/admin/users.php   → Executes users.php
```

Problems:
- **Security risk**: Direct access to all PHP files
- **No centralized logic**: Can't apply middleware to all routes
- **Messy URLs**: `.php` in every URL

**Laravel:**
```
All requests go through public/index.php:
/posts/123         → index.php → Router → PostController@show
/admin/users       → index.php → Router → UserController@index
```

Benefits:
- **Security**: Application code outside public directory
- **Routing**: Full control over URL structure
- **Middleware**: Apply logic to groups of routes

**You never edit `public/index.php`!** Laravel manages it.

---

## The `/config` Directory

**Configuration files** for every aspect of Laravel.

```
config/
├── app.php        # Application settings (name, locale, timezone)
├── database.php   # Database connections
├── mail.php       # Email settings
├── cache.php      # Cache settings
├── session.php    # Session settings
└── ...
```

### Example: `/config/app.php`

```php
return [
    'name' => env('APP_NAME', 'Laravel'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'locale' => 'en',
    // ...
];
```

**Notice `env()` function:**
- Reads from `.env` file
- Falls back to default if not set

**Pure PHP Comparison:**
```php
// config.php
define('APP_NAME', 'MyApp');
define('DEBUG', true);  // Hardcoded - bad!
define('DB_HOST', 'localhost');  // Secret in code - bad!
```

Laravel separates:
- **Config files**: Structure and defaults (committed to Git)
- **`.env` file**: Environment-specific values (NOT committed)

---

## The `/database` Directory

Everything **database-related**.

```
database/
├── migrations/    # Database structure version control
├── seeders/       # Dummy data
└── factories/     # Model instance generators
```

### `/database/migrations/`

**Database version control!**

**Pure PHP (Module 06):**
```sql
-- Run manually in phpMyAdmin or terminal
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP
);
```

Problems:
- **No history**: Can't track changes
- **Manual**: Each developer runs SQL manually
- **No rollback**: Can't undo changes easily
- **Production risk**: Forgetting to run migrations

**Laravel:**
```php
// database/migrations/2024_01_01_000000_create_posts_table.php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
}
```

Run migrations:
```bash
php artisan migrate
```

Benefits:
- **Version control**: Track database changes in Git
- **Automatic**: One command runs all migrations
- **Rollback**: Undo changes easily
- **Team-friendly**: Everyone has same database structure

---

## The `/storage` Directory

Laravel stores **temporary files**.

```
storage/
├── app/           # Application file storage
├── framework/     # Framework-generated files (cache, sessions)
└── logs/          # Application logs
```

### `/storage/logs/`

**Application logs** (errors, debug info).

**Pure PHP:**
```php
// Logging errors manually
error_log("Something went wrong", 3, "/path/to/errors.log");
```

**Laravel:**
```php
// Automatic logging
Log::error('Something went wrong', ['user_id' => $userId]);
```

Logs go to `/storage/logs/laravel.log`:
```
[2024-01-01 10:30:45] local.ERROR: Something went wrong {"user_id":123}
```

---

## The `/vendor` Directory

**All Composer dependencies** (packages).

**⚠️ NEVER edit files in `/vendor`!**
- Contains 80+ packages
- Regenerated when you run `composer install`
- Not committed to Git (too large)

Think of it like `node_modules` in JavaScript.

---

## Other Important Files

### `/artisan`

Laravel's **command-line tool** (we'll explore next).

```bash
php artisan list
```

### `/composer.json`

**PHP dependencies** (packages your app needs).

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0"
    }
}
```

Compare to pure PHP:
- **Pure PHP**: Download libraries manually, include files
- **Laravel**: Define in `composer.json`, run `composer install`

### `/.env`

**Environment configuration** (secrets, database credentials).

```env
DB_DATABASE=my_laravel_app
DB_USERNAME=root
DB_PASSWORD=secret
```

**NEVER commit to Git!**

### `/.env.example`

**Template** for `.env` (committed to Git).

When cloning a Laravel project:
```bash
cp .env.example .env
php artisan key:generate
```

---

## Introducing Artisan

**Artisan** is Laravel's command-line interface (CLI).

Think of it as your **development assistant**.

### What is Artisan?

**Pure PHP Development:**
```bash
# Create a controller manually
touch public/posts/controller.php
# Edit it in VS Code
code public/posts/controller.php
# Write boilerplate code...
```

**Laravel with Artisan:**
```bash
# Generate a controller with boilerplate
php artisan make:controller PostController
```

**Artisan generates code for you!**

---

## Basic Artisan Commands

### Viewing Available Commands

```bash
php artisan list
```

This shows **all available commands**. Let's explore the most useful!

### `php artisan --version`

Check Laravel version:
```bash
php artisan --version
```

Output:
```
Laravel Framework 11.31.0
```

### `php artisan about`

View application information:
```bash
php artisan about
```

Shows:
- Laravel version
- PHP version
- Environment (local, production)
- Database connection
- Drivers (cache, session, queue)

**Useful for debugging!**

---

## Code Generation Commands

These commands **generate files** for you.

### `make:controller`

Create a controller:

```bash
php artisan make:controller PostController
```

Creates: `/app/Http/Controllers/PostController.php`

```php
<?php

namespace App\Http\Controllers;

class PostController extends Controller
{
    //
}
```

**With resource methods:**
```bash
php artisan make:controller PostController --resource
```

Creates controller with common methods:
```php
class PostController extends Controller
{
    public function index()    // List all
    public function create()   // Show create form
    public function store()    // Save new
    public function show($id)  // Show one
    public function edit($id)  // Show edit form
    public function update($id) // Update
    public function destroy($id) // Delete
}
```

**Compare to pure PHP:**
- Pure PHP: Copy-paste from old project, manually edit
- Laravel: One command, consistent structure

### `make:model`

Create a model:

```bash
php artisan make:model Post
```

Creates: `/app/Models/Post.php`

**With migration:**
```bash
php artisan make:model Post -m
```

Creates:
- `/app/Models/Post.php` (model)
- `/database/migrations/2024_01_01_000000_create_posts_table.php` (migration)

**Shortcut for everything:**
```bash
php artisan make:model Post -mcr
```

Creates:
- **m**: migration
- **c**: controller
- **r**: resource controller (with methods)

**One command sets up your entire feature!**

### `make:migration`

Create a database migration:

```bash
php artisan make:migration create_posts_table
```

Creates: `/database/migrations/2024_01_01_000000_create_posts_table.php`

**Artisan is smart!** It knows you're creating a table from the name pattern:
- `create_XXX_table` → Creates table
- `add_XXX_to_YYY_table` → Adds columns
- `remove_XXX_from_YYY_table` → Removes columns

---

## Database Commands

### `migrate`

Run all pending migrations:

```bash
php artisan migrate
```

**First time:**
```
Migration table created successfully.
Migrating: 2024_01_01_000000_create_posts_table
Migrated:  2024_01_01_000000_create_posts_table (0.5ms)
```

**Already up to date:**
```
Nothing to migrate.
```

### `migrate:rollback`

Undo the last batch of migrations:

```bash
php artisan migrate:rollback
```

### `migrate:fresh`

Drop all tables and re-run migrations:

```bash
php artisan migrate:fresh
```

**⚠️ Warning**: Deletes all data! Only use in development.

### `migrate:status`

Check which migrations have run:

```bash
php artisan migrate:status
```

Output:
```
Migration name ................................. Batch / Status
2024_01_01_000000_create_users_table ........... [1] Ran
2024_01_01_000001_create_posts_table ........... [1] Ran
```

---

## Application Commands

### `serve`

Start development server:

```bash
php artisan serve
```

Starts server at `http://localhost:8000`

**With Herd, you don't need this!** Herd automatically serves all projects.

### `tinker`

Interactive PHP shell:

```bash
php artisan tinker
```

Test code interactively:
```php
>>> $user = User::first()
>>> $user->name
=> "John Doe"

>>> Post::count()
=> 5
```

**Like running PHP in the browser console!**

Very useful for:
- Testing queries
- Debugging
- Quick data manipulation

Exit with: `exit` or `Ctrl+D`

### `key:generate`

Generate application key:

```bash
php artisan key:generate
```

Updates `APP_KEY` in `.env`

**When to use:**
- After copying `.env.example` to `.env`
- If `APP_KEY` is missing

**Pure PHP comparison:**
```php
// Manual session security
$secret_key = bin2hex(random_bytes(32));  // You had to do this yourself!
```

Laravel handles encryption keys automatically.

---

## Cache Commands

### `cache:clear`

Clear application cache:

```bash
php artisan cache:clear
```

### `config:clear`

Clear configuration cache:

```bash
php artisan config:clear
```

### `route:clear`

Clear route cache:

```bash
php artisan route:clear
```

### `view:clear`

Clear compiled views:

```bash
php artisan view:clear
```

**Pro tip:** If something is behaving weirdly, try clearing caches!

---

## Route Commands

### `route:list`

View all registered routes:

```bash
php artisan route:list
```

Output:
```
Method    URI                  Name           Controller
GET       /                    home           HomeController@index
GET       /posts               posts.index    PostController@index
GET       /posts/{id}          posts.show     PostController@show
POST      /posts               posts.store    PostController@store
```

**Super useful!** Shows all your routes at a glance.

Compare to pure PHP:
- Pure PHP: Look through all files to find routes
- Laravel: One command shows everything

---

## Creating Custom Artisan Commands

You can create **your own Artisan commands**!

```bash
php artisan make:command SendEmails
```

Creates: `/app/Console/Commands/SendEmails.php`

We'll cover this in advanced modules.

---

## Common Workflow

Here's a typical development workflow:

### 1. Create a New Feature

```bash
# Create model, migration, and resource controller
php artisan make:model Post -mcr
```

### 2. Define Database Structure

Edit the migration:
```php
// database/migrations/xxxx_create_posts_table.php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
}
```

### 3. Run Migration

```bash
php artisan migrate
```

### 4. Check Routes

```bash
php artisan route:list
```

### 5. Test in Tinker

```bash
php artisan tinker
>>> Post::create(['title' => 'Test', 'content' => 'Hello'])
>>> Post::all()
```

---

## Pure PHP vs Laravel: Typical Tasks

### Creating a New Page

**Pure PHP:**
1. Create `/public/about.php`
2. Copy header/footer includes
3. Write HTML
4. Add to navigation manually

**Laravel:**
1. Add route: `Route::get('/about', [PageController::class, 'about']);`
2. Add method: `public function about() { return view('about'); }`
3. Create view: `about.blade.php`

### Adding a Database Table

**Pure PHP:**
1. Write SQL: `CREATE TABLE posts (...)`
2. Run in phpMyAdmin or terminal
3. Tell team members to run it
4. Hope everyone has the same structure

**Laravel:**
1. `php artisan make:migration create_posts_table`
2. Edit migration file
3. `php artisan migrate`
4. Commit migration to Git
5. Team runs `php artisan migrate` (automatic)

### Checking Database Structure

**Pure PHP:**
1. Open phpMyAdmin
2. Click on database
3. Look at tables

**Laravel:**
```bash
php artisan migrate:status
```

### Debugging

**Pure PHP:**
```php
var_dump($user);
die();
```

**Laravel:**
```php
dd($user);  // Dump and die (prettier output)
```

Or use Tinker:
```bash
php artisan tinker
>>> User::find(1)
```

---

## Directory Structure Best Practices

### Keep Controllers Thin

**Bad:**
```php
class PostController extends Controller
{
    public function index()
    {
        // 100 lines of business logic here
        $posts = Post::where('published', true)
            ->where('created_at', '>', now()->subDays(30))
            ->orderBy('views', 'desc')
            ->get();

        // Transform data
        foreach ($posts as $post) {
            // Complex logic...
        }

        return view('posts.index', compact('posts'));
    }
}
```

**Good:**
```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::recentAndPopular();
        return view('posts.index', compact('posts'));
    }
}

// Logic in model:
class Post extends Model
{
    public static function recentAndPopular()
    {
        return static::where('published', true)
            ->where('created_at', '>', now()->subDays(30))
            ->orderBy('views', 'desc')
            ->get();
    }
}
```

### Organize Views by Feature

```
resources/views/
├── layouts/
│   └── app.blade.php
├── posts/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── users/
│   ├── profile.blade.php
│   └── settings.blade.php
└── auth/
    ├── login.blade.php
    └── register.blade.php
```

### Group Routes Logically

```php
// routes/web.php

// Public pages
Route::get('/', [HomeController::class, 'index']);
Route::get('/about', [PageController::class, 'about']);

// Auth routes
Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'authenticate']);

// Protected routes (we'll learn middleware soon)
Route::middleware('auth')->group(function () {
    Route::resource('posts', PostController::class);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

---

## Summary

**What You Learned:**
- Laravel's directory structure and purpose of each folder
- The role of `/app`, `/routes`, `/resources`, `/database`, `/public`
- How Laravel's structure compares to pure PHP projects
- What Artisan is and why it's powerful
- Common Artisan commands for code generation
- Database commands (migrate, rollback, etc.)
- How to view routes and debug with Tinker

**Key Takeaways:**
1. **Laravel's structure is consistent** across all projects
2. **`/app`** is where YOUR code lives (controllers, models)
3. **`/routes/web.php`** defines all web URLs
4. **`/resources/views`** contains Blade templates
5. **Artisan generates code** saving you time
6. **Migrations are database version control** (like Git for databases)
7. **Single entry point** (`/public/index.php`) improves security

**Next Lesson:** We'll dive into routing - how to define URLs and map them to controllers.

---

## Practice Tasks

### Task 1: Explore Your Laravel Structure

```bash
cd my-first-app
tree -L 2 -I vendor
```

Identify:
- Where controllers go
- Where models go
- Where views go
- Where routes are defined

### Task 2: Generate Your First Controller

```bash
php artisan make:controller HelloController
```

Open `/app/Http/Controllers/HelloController.php` and look at the structure.

### Task 3: View All Artisan Commands

```bash
php artisan list
```

Count how many `make:` commands exist. That's how many things Artisan can generate!

### Task 4: Check Laravel Information

```bash
php artisan about
```

What's your Laravel version? PHP version? Database driver?

---

## Quick Quiz

**1. What directory contains your controllers?**
- `/app/Http/Controllers`

**2. What file defines your web routes?**
- `/routes/web.php`

**3. What command creates a model with migration and resource controller?**
- `php artisan make:model Post -mcr`

**4. What command runs database migrations?**
- `php artisan migrate`

**5. What's the difference between `/config` and `/.env`?**
- `/config`: Configuration structure (committed to Git)
- `/.env`: Environment-specific values (NOT committed)

**6. What directory should never be edited?**
- `/vendor` (Composer dependencies)

**7. What command shows all registered routes?**
- `php artisan route:list`

---

**Next**: [Lesson 03 - Routing Basics →](03-routing-basics.md)
