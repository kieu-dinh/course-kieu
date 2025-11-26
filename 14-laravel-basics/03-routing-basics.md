# Lesson 03 - Routing Basics

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What is Routing?

**Routing** is the process of mapping URLs to code that handles them.

### Pure PHP Routing (Modules 04-10)

In pure PHP, **files = routes**:

```
URL: /posts/index.php      → File: /public/posts/index.php
URL: /posts/show.php?id=1  → File: /public/posts/show.php
URL: /about.php            → File: /public/about.php
```

**Problems:**
- **Ugly URLs**: `.php` everywhere, query strings for parameters
- **Inflexible**: Can't change URLs without renaming files
- **No centralization**: Routes scattered across directories
- **No validation**: Any file can be accessed directly

### Laravel Routing

In Laravel, **routes are defined** in `/routes/web.php`:

```php
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/about', [PageController::class, 'about']);
```

**Benefits:**
- **Clean URLs**: `/posts/123` instead of `/posts/show.php?id=123`
- **Flexible**: Change URLs without changing controllers
- **Centralized**: All routes in one file
- **Validated**: Only defined routes are accessible
- **Named routes**: Reference routes by name, not URL
- **Route parameters**: Extract data from URL

---

## Opening Your Routes File

All web routes are defined in: `/routes/web.php`

Open it:
```bash
code /Users/pouget/Projects/cours-kieu/14-laravel-basics/exercises/my-first-app/routes/web.php
```

Default content:
```php
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
```

This defines **one route**:
- **Method**: GET
- **URL**: `/`
- **Action**: Return the `welcome` view

---

## Basic Route Syntax

### Route with Closure

A **closure** is an anonymous function.

```php
Route::get('/hello', function () {
    return 'Hello World!';
});
```

**Try it:**
1. Add this to `web.php`
2. Visit: `http://my-first-app.test/hello`
3. See: "Hello World!"

**Compare to Pure PHP:**
```php
// /public/hello.php
<?php
echo 'Hello World!';
```

### Route with Controller

**Better practice** - keep logic in controllers:

```php
use App\Http\Controllers\HelloController;

Route::get('/hello', [HelloController::class, 'index']);
```

This says:
- **When** someone visits `/hello`
- **Call** the `index` method in `HelloController`

---

## HTTP Methods

HTTP defines **different methods** for different actions:

| Method | Purpose | Example |
|--------|---------|---------|
| GET | Retrieve data | View a page, list items |
| POST | Create data | Submit a form, create user |
| PUT/PATCH | Update data | Edit a post |
| DELETE | Delete data | Remove a post |

### GET Routes

**Retrieve data** (read-only, no side effects):

```php
Route::get('/posts', [PostController::class, 'index']);      // List all
Route::get('/posts/{id}', [PostController::class, 'show']);  // Show one
```

### POST Routes

**Create data** (from form submission):

```php
Route::post('/posts', [PostController::class, 'store']);
```

**Pure PHP (Module 04):**
```php
// create-post.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form
}
```

**Laravel:** The route automatically only matches POST requests!

### PUT/PATCH Routes

**Update data:**

```php
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::patch('/posts/{id}', [PostController::class, 'update']);
```

**Difference:**
- **PUT**: Replace entire resource
- **PATCH**: Partial update

In practice, both often do the same thing.

### DELETE Routes

**Delete data:**

```php
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
```

### Multiple Methods

Match multiple methods:

```php
Route::match(['GET', 'POST'], '/form', [FormController::class, 'handle']);
```

Match all methods:

```php
Route::any('/webhook', [WebhookController::class, 'handle']);
```

---

## Route Parameters

Extract data from URLs dynamically.

### Required Parameters

```php
Route::get('/posts/{id}', function ($id) {
    return "Post ID: $id";
});
```

Visit: `http://my-first-app.test/posts/123`
Output: "Post ID: 123"

**Multiple parameters:**

```php
Route::get('/posts/{post}/comments/{comment}', function ($postId, $commentId) {
    return "Post $postId, Comment $commentId";
});
```

Visit: `/posts/5/comments/10`
Output: "Post 5, Comment 10"

**Pure PHP Equivalent:**
```php
// posts/show.php?id=123
$id = $_GET['id'] ?? null;
if (!$id) {
    die('ID required');
}
echo "Post ID: $id";
```

Laravel's approach is **cleaner and safer**.

### Optional Parameters

Parameters with default values:

```php
Route::get('/greet/{name?}', function ($name = 'Guest') {
    return "Hello, $name!";
});
```

Visit: `/greet/John` → "Hello, John!"
Visit: `/greet` → "Hello, Guest!"

**Note the `?`** after `{name}` makes it optional.

### Parameter Constraints

Validate parameters using **regex patterns**:

```php
// Only digits
Route::get('/posts/{id}', function ($id) {
    return "Post ID: $id";
})->where('id', '[0-9]+');

// Only letters
Route::get('/users/{name}', function ($name) {
    return "User: $name";
})->where('name', '[A-Za-z]+');

// Multiple constraints
Route::get('/posts/{post}/comments/{comment}', function ($post, $comment) {
    //
})->where(['post' => '[0-9]+', 'comment' => '[0-9]+']);
```

Now:
- `/posts/123` ✅ Works
- `/posts/abc` ❌ 404 Not Found

**Global constraints** (apply everywhere):

```php
// In App\Providers\RouteServiceProvider
Route::pattern('id', '[0-9]+');

// Now all {id} parameters must be numeric
```

---

## Named Routes

Give routes **names** to reference them in code.

### Why Name Routes?

**Without named routes:**
```blade
<a href="/posts/{{ $post->id }}">View Post</a>
```

**Problem:** If you change URL from `/posts/` to `/blog/`, you must update everywhere!

**With named routes:**
```blade
<a href="{{ route('posts.show', $post->id) }}">View Post</a>
```

Change URL in **one place** (routes file), links update everywhere!

### Naming Routes

```php
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');
```

**Convention:** Use `resource.action` format:
- `posts.index` - List posts
- `posts.show` - Show post
- `posts.create` - Create form
- `posts.store` - Store post
- `posts.edit` - Edit form
- `posts.update` - Update post
- `posts.destroy` - Delete post

### Generating URLs from Named Routes

**In Controllers:**
```php
return redirect()->route('posts.show', ['id' => 123]);
```

**In Blade Views:**
```blade
<a href="{{ route('posts.show', $post->id) }}">View</a>
<a href="{{ route('posts.edit', $post->id) }}">Edit</a>
```

**With multiple parameters:**
```php
Route::get('/posts/{post}/comments/{comment}', [CommentController::class, 'show'])
    ->name('comments.show');

// Generate URL
route('comments.show', ['post' => 5, 'comment' => 10]);
// Result: /posts/5/comments/10
```

**Check if route exists:**
```php
if (Route::has('posts.show')) {
    return route('posts.show', $id);
}
```

---

## Route Groups

Group routes with **shared attributes**.

### Prefix Group

Add prefix to all routes:

```php
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/settings', [AdminController::class, 'settings']);
});
```

URLs:
- `/admin/dashboard`
- `/admin/users`
- `/admin/settings`

**Pure PHP equivalent:**
```
/public/admin/dashboard.php
/public/admin/users.php
/public/admin/settings.php
```

### Middleware Group

Apply middleware to all routes (we'll cover middleware in Lesson 10):

```php
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'index']);
});
```

Only **authenticated users** can access these routes.

### Name Prefix Group

Add prefix to route names:

```php
Route::name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
});
```

Route names:
- `admin.dashboard`
- `admin.users`

### Combined Groups

Combine multiple attributes:

```php
Route::prefix('admin')
    ->middleware('auth')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
    });
```

This creates routes:
- URL: `/admin/dashboard`, Name: `admin.dashboard`, Middleware: `auth`
- URL: `/admin/users`, Name: `admin.users`, Middleware: `auth`

---

## Resource Routes

**Resource routes** generate all standard routes for a resource (CRUD operations).

### What are Resources?

A **resource** is an entity you manage (create, read, update, delete):
- Posts
- Users
- Comments
- Products

### Creating Resource Routes

Instead of defining 7 routes manually:

```php
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/create', [PostController::class, 'create']);
Route::post('/posts', [PostController::class, 'store']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/posts/{id}/edit', [PostController::class, 'edit']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
```

**Use resource route:**

```php
Route::resource('posts', PostController::class);
```

**That's it!** Laravel generates all 7 routes automatically.

### Routes Created by Resource

| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | /posts | index | posts.index |
| GET | /posts/create | create | posts.create |
| POST | /posts | store | posts.store |
| GET | /posts/{post} | show | posts.show |
| GET | /posts/{post}/edit | edit | posts.edit |
| PUT/PATCH | /posts/{post} | update | posts.update |
| DELETE | /posts/{post} | destroy | posts.destroy |

**Verify routes:**
```bash
php artisan route:list --name=posts
```

### Partial Resource Routes

Only create specific routes:

```php
// Only index and show
Route::resource('posts', PostController::class)->only(['index', 'show']);

// All except destroy
Route::resource('posts', PostController::class)->except(['destroy']);
```

### API Resource Routes

For APIs, you don't need `create` and `edit` (form pages):

```php
Route::apiResource('posts', PostController::class);
```

Creates only:
- index, store, show, update, destroy

### Nested Resources

Resources within resources:

```php
Route::resource('posts.comments', CommentController::class);
```

Creates routes like:
- `GET /posts/{post}/comments` - List post's comments
- `POST /posts/{post}/comments` - Create comment for post
- `GET /posts/{post}/comments/{comment}` - Show comment

---

## Viewing Your Routes

### `route:list` Command

See all registered routes:

```bash
php artisan route:list
```

Output:
```
GET|HEAD  /              Closure
GET|HEAD  /hello         Closure
GET|HEAD  /posts         posts.index › PostController@index
GET|HEAD  /posts/create  posts.create › PostController@create
POST      /posts         posts.store › PostController@store
GET|HEAD  /posts/{post}  posts.show › PostController@show
```

### Filter Routes

By name:
```bash
php artisan route:list --name=posts
```

By URI:
```bash
php artisan route:list --path=admin
```

By method:
```bash
php artisan route:list --method=POST
```

---

## Route Model Binding

Automatically fetch models from route parameters.

### Manual Fetching (What You'd Do Without Laravel)

```php
Route::get('/posts/{id}', function ($id) {
    $post = Post::find($id);

    if (!$post) {
        abort(404);
    }

    return view('posts.show', compact('post'));
});
```

**Pure PHP equivalent:**
```php
// posts/show.php?id=123
$id = $_GET['id'] ?? null;

$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    echo '404 Not Found';
    exit;
}
```

### Implicit Binding

**Laravel does it automatically!**

```php
Route::get('/posts/{post}', function (Post $post) {
    return view('posts.show', compact('post'));
});
```

**How it works:**
1. User visits `/posts/123`
2. Laravel sees `{post}` parameter
3. Laravel sees `Post $post` type-hint
4. Laravel runs `Post::find(123)` automatically
5. If not found, returns 404 automatically
6. If found, passes `$post` to closure

**Requirements:**
- Parameter name must match variable name: `{post}` → `$post`
- Type-hint the model: `Post $post`

**In controllers:**
```php
class PostController extends Controller
{
    public function show(Post $post)
    {
        // $post is already fetched!
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        // $post is already fetched!
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        // $post is already fetched!
        $post->update($request->all());
        return redirect()->route('posts.show', $post);
    }
}
```

**Compare to pure PHP (Module 06):**
```php
// edit-post.php
$id = $_GET['id'] ?? null;

// Same query in every file!
$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    die('Post not found');
}
```

Laravel **eliminates this repetitive code!**

### Custom Key Binding

Bind by a different column (e.g., slug):

```php
Route::get('/posts/{post:slug}', function (Post $post) {
    return view('posts.show', compact('post'));
});
```

Now: `/posts/my-first-post` finds post by slug, not ID!

**In the model:**
```php
class Post extends Model
{
    public function getRouteKeyName()
    {
        return 'slug';  // Always use slug
    }
}
```

---

## Fallback Routes

Handle **404s gracefully**.

```php
Route::fallback(function () {
    return view('errors.404');
});
```

This catches **all unmatched routes**.

---

## Route Caching

In production, **cache routes** for better performance.

### Cache Routes

```bash
php artisan route:cache
```

**Benefits:**
- Faster route matching (no file parsing)
- Routes loaded from cache file

**Important:** After caching, adding routes in `web.php` won't work until you clear cache!

### Clear Route Cache

```bash
php artisan route:clear
```

**Development tip:** Don't cache routes during development!

---

## Real-World Example: Blog Routes

Let's define routes for a complete blog:

```php
<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');

// Blog posts (public viewing)
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

// Comments (public)
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->name('comments.store');

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes (admin only)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Post management
    Route::resource('posts', PostController::class)->except(['index', 'show']);

    // Comment moderation
    Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');
});

// Fallback
Route::fallback(function () {
    return view('errors.404');
});
```

**Pure PHP equivalent would require:**
- 20+ separate PHP files
- Manual session checking in each admin file
- Repeated database connection code
- Query string parameters everywhere
- No centralized route view

**Laravel:** Clean, organized, in one file!

---

## Common Patterns

### Homepage

```php
Route::get('/', [HomeController::class, 'index'])->name('home');
```

### About/Contact Pages

```php
Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');
```

For simple views, closures are fine!

### Authentication Routes

Laravel provides a helper:

```php
Auth::routes();
```

This generates all auth routes:
- Login (GET/POST)
- Register (GET/POST)
- Password reset
- Email verification

We'll cover authentication in Module 16!

### API Routes

API routes go in `/routes/api.php`:

```php
Route::apiResource('posts', ApiPostController::class);
```

Automatically prefixed with `/api`:
- `/api/posts` - GET (index)
- `/api/posts` - POST (store)
- `/api/posts/123` - GET (show)
- `/api/posts/123` - PUT/PATCH (update)
- `/api/posts/123` - DELETE (destroy)

---

## Summary

**What You Learned:**
- What routing is and why it's important
- How to define routes with different HTTP methods
- Route parameters (required, optional, constrained)
- Named routes and why they're useful
- Route groups for organization
- Resource routes for CRUD operations
- Route model binding (implicit and explicit)
- How to view and cache routes

**Key Takeaways:**
1. **Routes map URLs to code** - centralized in `/routes/web.php`
2. **Clean URLs** - `/posts/123` instead of `/posts.php?id=123`
3. **Named routes** - reference by name, not URL
4. **Resource routes** - generate 7 CRUD routes with one line
5. **Route model binding** - automatically fetch models from URL
6. **Groups** - organize routes with shared attributes
7. **Type safety** - constraints ensure valid parameters

**Next Lesson:** We'll create controllers to handle the logic for these routes!

---

## Practice Exercise

Create these routes in `web.php`:

```php
// 1. Homepage
Route::get('/', ...)->name('home');

// 2. About page (simple view)
Route::get('/about', ...)->name('about');

// 3. Contact page with form
Route::get('/contact', ...)->name('contact');
Route::post('/contact', ...)->name('contact.send');

// 4. Blog posts (resource)
Route::resource('posts', PostController::class);

// 5. Admin area (group)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', ...)->name('admin.dashboard');
});
```

Then run:
```bash
php artisan route:list
```

See all your routes!

---

## Quick Quiz

**1. What file contains web routes?**
- `/routes/web.php`

**2. What's the syntax for a GET route with a closure?**
```php
Route::get('/path', function () {
    return 'response';
});
```

**3. How do you create all 7 CRUD routes at once?**
```php
Route::resource('posts', PostController::class);
```

**4. What's route model binding?**
- Automatically fetching a model from a route parameter

**5. What command shows all registered routes?**
```bash
php artisan route:list
```

**6. What's the benefit of named routes?**
- Change URLs in one place without updating every link

**7. How do you make a route parameter optional?**
```php
Route::get('/greet/{name?}', function ($name = 'Guest') { ... });
```

---

**Next**: [Lesson 04 - Controllers →](04-controllers.md)
