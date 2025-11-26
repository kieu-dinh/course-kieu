# Lesson 04 - Controllers

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What are Controllers?

**Controllers** handle the logic for your routes. They sit between routes and views in the MVC pattern.

### MVC Flow Reminder

```
Request → Route → Controller → Model → Database
                     ↓
                   View → Response
```

**Controller's job:**
1. Receive the request
2. Process business logic (query database, validate, etc.)
3. Return a response (view, redirect, JSON, etc.)

### Pure PHP (Module 05)

In pure PHP, each page was its own file:

```php
// posts/index.php
<?php
require_once '../config/database.php';

$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head><title>Posts</title></head>
<body>
    <h1>All Posts</h1>
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p><?= htmlspecialchars($post['content']) ?></p>
        </article>
    <?php endforeach; ?>
</body>
</html>
```

**Problems:**
- Logic and presentation mixed
- Database connection in every file
- Hard to reuse code
- Hard to test

### Laravel Approach

**Route:**
```php
Route::get('/posts', [PostController::class, 'index']);
```

**Controller:**
```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->get();
        return view('posts.index', compact('posts'));
    }
}
```

**View (posts/index.blade.php):**
```blade
@extends('layouts.app')

@section('content')
    <h1>All Posts</h1>
    @foreach($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->content }}</p>
        </article>
    @endforeach
@endsection
```

**Clean separation!**

---

## Creating Controllers

### Using Artisan

```bash
php artisan make:controller PostController
```

Creates: `/app/Http/Controllers/PostController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    //
}
```

### Resource Controller

Generate with all CRUD methods:

```bash
php artisan make:controller PostController --resource
```

Creates:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
```

**All CRUD methods ready to fill in!**

### Model + Controller

Generate model and controller together:

```bash
php artisan make:model Post -mc
```

Creates:
- `/app/Models/Post.php` (model)
- `/database/migrations/xxxx_create_posts_table.php` (migration)
- `/app/Http/Controllers/PostController.php` (controller)

**Even better:**
```bash
php artisan make:model Post -mcr
```

Creates model, migration, and **resource controller**!

---

## Controller Structure

### Namespace

```php
namespace App\Http\Controllers;
```

**Namespace** organizes classes. `App\Http\Controllers` means:
- Root: `app/`
- Subdirectory: `Http/Controllers/`

### Imports

```php
use Illuminate\Http\Request;
use App\Models\Post;
```

Import classes you'll use.

**Pure PHP (Module 05):**
```php
require_once '../classes/Post.php';
require_once '../includes/functions.php';
```

**Laravel:** Uses PSR-4 autoloading (automatic, no `require`)

### Extending Base Controller

```php
class PostController extends Controller
{
    //
}
```

**Every controller extends `Controller`** (base class with helpful methods).

---

## Basic Controller Methods

### Returning Views

```php
public function index()
{
    return view('posts.index');
}
```

**What `view()` does:**
1. Looks for `/resources/views/posts/index.blade.php`
2. Compiles Blade syntax
3. Returns HTML response

**With data:**
```php
public function index()
{
    $posts = Post::all();
    return view('posts.index', compact('posts'));
}
```

**Alternative syntax:**
```php
return view('posts.index', ['posts' => $posts]);
```

Or:
```php
return view('posts.index')->with('posts', $posts);
```

**All three are equivalent!** Use what feels natural.

### Returning Strings

```php
public function hello()
{
    return 'Hello World!';
}
```

Laravel automatically converts to HTTP response.

### Returning JSON

```php
public function api()
{
    return response()->json([
        'message' => 'Success',
        'data' => Post::all()
    ]);
}
```

**Pure PHP (Module 09 - APIs):**
```php
header('Content-Type: application/json');
echo json_encode([
    'message' => 'Success',
    'data' => $posts
]);
```

Laravel handles headers automatically!

### Redirects

```php
public function store(Request $request)
{
    // Save post...

    return redirect('/posts');
}
```

**Better - with named routes:**
```php
return redirect()->route('posts.index');
```

**With flash message:**
```php
return redirect()
    ->route('posts.index')
    ->with('success', 'Post created successfully!');
```

**Pure PHP (Module 07):**
```php
header('Location: posts.php');
exit;
```

### Downloads

```php
public function download()
{
    return response()->download(storage_path('app/document.pdf'));
}
```

---

## Resource Controller Actions

Let's implement a complete CRUD controller!

### `index()` - List All

**Show list of all resources.**

```php
public function index()
{
    $posts = Post::orderBy('created_at', 'desc')->get();
    return view('posts.index', compact('posts'));
}
```

**Pure PHP:**
```php
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
foreach ($posts as $post) {
    echo '<div>' . htmlspecialchars($post['title']) . '</div>';
}
include 'footer.php';
```

### `create()` - Show Creation Form

**Display form to create new resource.**

```php
public function create()
{
    return view('posts.create');
}
```

**Pure PHP:**
```php
// create-post.php
include 'header.php';
?>
<form method="POST" action="store-post.php">
    <input type="text" name="title">
    <textarea name="content"></textarea>
    <button type="submit">Create</button>
</form>
<?php
include 'footer.php';
```

### `store()` - Save New Resource

**Process form submission, save to database.**

```php
public function store(Request $request)
{
    // Validate
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);

    // Create
    $post = Post::create($validated);

    // Redirect
    return redirect()
        ->route('posts.show', $post)
        ->with('success', 'Post created!');
}
```

**Pure PHP (Module 06):**
```php
// store-post.php
$errors = [];

// Validate
if (empty($_POST['title'])) {
    $errors[] = "Title required";
}
if (strlen($_POST['title']) > 255) {
    $errors[] = "Title too long";
}
if (empty($_POST['content'])) {
    $errors[] = "Content required";
}

if (empty($errors)) {
    // Sanitize
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']);

    // Insert
    $sql = "INSERT INTO posts (title, content, created_at) VALUES (:title, :content, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'title' => $title,
        'content' => $content
    ]);

    // Redirect
    header('Location: posts.php');
    exit;
} else {
    // Show errors
    $_SESSION['errors'] = $errors;
    header('Location: create-post.php');
    exit;
}
```

**Laravel does validation, sanitization, and redirect in 4 lines!**

### `show()` - Display Single Resource

**Show one resource.**

```php
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
```

**Remember route model binding?** Laravel automatically fetches the post!

**Pure PHP:**
```php
// show-post.php?id=123
$id = $_GET['id'] ?? null;

if (!$id) {
    die('ID required');
}

$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    die('Post not found');
}

include 'header.php';
echo '<h1>' . htmlspecialchars($post['title']) . '</h1>';
echo '<p>' . htmlspecialchars($post['content']) . '</p>';
include 'footer.php';
```

### `edit()` - Show Edit Form

**Display form to edit resource.**

```php
public function edit(Post $post)
{
    return view('posts.edit', compact('post'));
}
```

**Pure PHP:**
```php
// edit-post.php?id=123
// Same query code as show...
$post = /* fetch from DB */;

include 'header.php';
?>
<form method="POST" action="update-post.php?id=<?= $post['id'] ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>">
    <textarea name="content"><?= htmlspecialchars($post['content']) ?></textarea>
    <button type="submit">Update</button>
</form>
<?php
include 'footer.php';
```

### `update()` - Update Resource

**Process edit form, update database.**

```php
public function update(Request $request, Post $post)
{
    // Validate
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);

    // Update
    $post->update($validated);

    // Redirect
    return redirect()
        ->route('posts.show', $post)
        ->with('success', 'Post updated!');
}
```

**Pure PHP:**
```php
// update-post.php?id=123
$id = $_GET['id'] ?? null;

// Validate (same as store)...

// Update
$sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $title,
    'content' => $content,
    'id' => $id
]);

header('Location: show-post.php?id=' . $id);
exit;
```

### `destroy()` - Delete Resource

**Delete resource from database.**

```php
public function destroy(Post $post)
{
    $post->delete();

    return redirect()
        ->route('posts.index')
        ->with('success', 'Post deleted!');
}
```

**Pure PHP:**
```php
// delete-post.php?id=123
$id = $_GET['id'] ?? null;

$sql = "DELETE FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);

header('Location: posts.php');
exit;
```

---

## Complete Example: PostController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $post = Post::create($validated);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $post->update($validated);

        return redirect()
            ->route('posts.show', $post)
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
```

**That's it!** Complete CRUD in ~60 lines.

**Pure PHP equivalent:** 200+ lines across 7 files.

---

## Dependency Injection

Laravel automatically **injects dependencies** into controller methods.

### Request Object

```php
public function store(Request $request)
{
    // $request is automatically injected
    $title = $request->input('title');
}
```

**Pure PHP:**
```php
$title = $_POST['title'] ?? null;
```

### Route Model Binding

```php
public function show(Post $post)
{
    // $post is automatically fetched and injected
}
```

**Pure PHP:**
```php
$id = $_GET['id'] ?? null;
$post = /* query database */;
```

### Multiple Injections

```php
public function update(Request $request, Post $post)
{
    // Both injected automatically!
    $validated = $request->validate([...]);
    $post->update($validated);
}
```

**Order matters:**
1. Dependencies first (Request, etc.)
2. Route parameters last (Post, etc.)

---

## Organizing Controllers

### Single Action Controllers

For controllers with **one action**, use `__invoke`:

```php
class ShowPostController extends Controller
{
    public function __invoke(Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
```

**Route:**
```php
Route::get('/posts/{post}', ShowPostController::class);
```

No method name needed!

### Subdirectories

Organize controllers in subdirectories:

```
app/Http/Controllers/
├── Admin/
│   ├── PostController.php
│   └── UserController.php
├── Api/
│   └── PostController.php
└── PostController.php
```

**Create with Artisan:**
```bash
php artisan make:controller Admin/PostController
```

**Route:**
```php
use App\Http\Controllers\Admin\PostController;

Route::get('/admin/posts', [PostController::class, 'index']);
```

### Naming Conventions

**Controllers:** Singular, `Controller` suffix
- `PostController` (not `PostsController`)
- `UserController`
- `CommentController`

**Methods:** Match HTTP verbs / actions
- `index()` - List all
- `create()` - Show create form
- `store()` - Save new
- `show()` - Display one
- `edit()` - Show edit form
- `update()` - Save changes
- `destroy()` - Delete

---

## Request Handling

### Accessing Input

```php
public function store(Request $request)
{
    // Single value
    $title = $request->input('title');

    // With default
    $name = $request->input('name', 'Guest');

    // All input
    $all = $request->all();

    // Only specific fields
    $data = $request->only(['title', 'content']);

    // Except specific fields
    $data = $request->except(['_token']);

    // Check if exists
    if ($request->has('title')) {
        //
    }

    // Check if filled
    if ($request->filled('title')) {
        //
    }
}
```

**Pure PHP:**
```php
$title = $_POST['title'] ?? null;
$name = $_POST['name'] ?? 'Guest';
$all = $_POST;
```

### Query Strings

```php
// URL: /posts?page=2&sort=latest
$page = $request->query('page');
$sort = $request->query('sort');
```

**Pure PHP:**
```php
$page = $_GET['page'] ?? null;
$sort = $_GET['sort'] ?? null;
```

### Route Parameters

```php
// Route: /posts/{post}/comments/{comment}
public function show(Request $request, Post $post, Comment $comment)
{
    // Both $post and $comment are available
}
```

### File Uploads

```php
public function store(Request $request)
{
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('images');
    }
}
```

**Pure PHP (Module 06):**
```php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $name = $_FILES['image']['name'];
    move_uploaded_file($tmp_name, "uploads/$name");
}
```

We'll cover file uploads in detail later!

---

## Response Types

### View Response

```php
return view('posts.index', compact('posts'));
```

### JSON Response

```php
return response()->json([
    'success' => true,
    'data' => $posts
]);
```

### Redirect Response

```php
return redirect('/posts');
return redirect()->route('posts.index');
return redirect()->back();
```

### Download Response

```php
return response()->download($pathToFile);
```

### Custom Response

```php
return response('Content', 200)
    ->header('Content-Type', 'text/plain');
```

---

## Validation in Controllers

### Basic Validation

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'published' => 'boolean',
    ]);

    // Only validated data
    Post::create($validated);
}
```

**If validation fails:**
- Automatically redirects back
- Flashes errors to session
- Preserves old input

**Pure PHP (Module 04):**
```php
$errors = [];

if (empty($_POST['title'])) {
    $errors[] = "Title required";
}

if (strlen($_POST['title']) > 255) {
    $errors[] = "Title too long";
}

if (empty($_POST['content'])) {
    $errors[] = "Content required";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $_POST;
    header('Location: create.php');
    exit;
}

// Continue with insert...
```

### Custom Error Messages

```php
$request->validate([
    'title' => 'required|max:255',
], [
    'title.required' => 'Please enter a post title.',
    'title.max' => 'Title cannot exceed 255 characters.',
]);
```

### Available Validation Rules

```php
'title' => 'required|max:255|min:3',
'email' => 'required|email|unique:users',
'age' => 'required|integer|between:18,100',
'website' => 'url',
'image' => 'required|image|max:2048',
'published_at' => 'date|after:today',
```

**We'll cover validation in depth in a later lesson!**

---

## Controller Middleware

Apply middleware directly in controller:

```php
class PostController extends Controller
{
    public function __construct()
    {
        // Apply to all methods
        $this->middleware('auth');

        // Apply to specific methods
        $this->middleware('auth')->only(['create', 'store', 'edit', 'update', 'destroy']);

        // Apply to all except
        $this->middleware('auth')->except(['index', 'show']);
    }
}
```

**We'll cover middleware in Lesson 10!**

---

## Best Practices

### 1. Keep Controllers Thin

**Bad:**
```php
public function store(Request $request)
{
    // 100 lines of business logic
    // Complex calculations
    // External API calls
    // Email sending
    // etc.
}
```

**Good:**
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    $post = $this->postService->create($validated);

    return redirect()->route('posts.show', $post);
}
```

Move complex logic to **services** or **models**.

### 2. Use Form Requests for Complex Validation

Instead of:
```php
public function store(Request $request)
{
    $validated = $request->validate([
        // 20 validation rules...
    ]);
}
```

Create a Form Request:
```bash
php artisan make:request StorePostRequest
```

```php
public function store(StorePostRequest $request)
{
    // Automatically validated!
    $validated = $request->validated();
}
```

### 3. Use Resource Controllers

**Bad:**
```php
Route::get('/posts', [PostController::class, 'list']);
Route::get('/posts/new', [PostController::class, 'new']);
Route::post('/posts/create', [PostController::class, 'create']);
```

**Good:**
```php
Route::resource('posts', PostController::class);
```

Standard naming = easier to understand.

### 4. Use Route Model Binding

**Bad:**
```php
public function show($id)
{
    $post = Post::findOrFail($id);
    return view('posts.show', compact('post'));
}
```

**Good:**
```php
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
```

### 5. Return Early

**Bad:**
```php
public function show(Post $post)
{
    if ($post->published) {
        return view('posts.show', compact('post'));
    } else {
        return redirect('/posts')->with('error', 'Post not found');
    }
}
```

**Good:**
```php
public function show(Post $post)
{
    if (!$post->published) {
        return redirect('/posts')->with('error', 'Post not found');
    }

    return view('posts.show', compact('post'));
}
```

---

## Summary

**What You Learned:**
- What controllers are and their role in MVC
- How to create controllers with Artisan
- Resource controller methods (CRUD)
- Handling requests and responses
- Dependency injection in controllers
- Validation in controllers
- Organizing controllers
- Best practices

**Key Takeaways:**
1. **Controllers handle logic** between routes and views
2. **Artisan generates controllers** with boilerplate code
3. **Resource controllers** follow standard CRUD pattern
4. **Route model binding** automatically fetches models
5. **Request validation** is built-in and easy
6. **Keep controllers thin** - delegate to services/models
7. **Use type hints** for dependency injection

**Next Lesson:** We'll learn about Views and Blade templates - the presentation layer!

---

## Practice Exercise

Create a complete CRUD controller for a "Product" resource:

```bash
php artisan make:model Product -mcr
```

Implement all methods:
- `index()` - List all products
- `create()` - Show create form
- `store()` - Save new product (validate: name, price, description)
- `show()` - Display single product
- `edit()` - Show edit form
- `update()` - Save changes
- `destroy()` - Delete product

Add validation:
```php
'name' => 'required|max:255',
'price' => 'required|numeric|min:0',
'description' => 'nullable',
```

---

## Quick Quiz

**1. What command creates a resource controller?**
```bash
php artisan make:controller PostController --resource
```

**2. What are the 7 resource controller methods?**
- index, create, store, show, edit, update, destroy

**3. How does route model binding work?**
- Laravel automatically fetches a model based on route parameter and type hint

**4. What's the difference between `compact('posts')` and `['posts' => $posts]`?**
- Same result, different syntax

**5. How do you validate input in a controller?**
```php
$validated = $request->validate([...]);
```

**6. How do you redirect with a success message?**
```php
return redirect()->route('posts.index')->with('success', 'Saved!');
```

---

**Next**: [Lesson 05 - Views and Blade Templates →](05-views-blade-templates.md)
