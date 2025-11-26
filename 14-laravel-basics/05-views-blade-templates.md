# Lesson 05 - Views and Blade Templates

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What are Views?

**Views** are the presentation layer of your application - the HTML users see.

### MVC Review

```
Controller → View → HTML Response
```

**Controller's job:** Prepare data
**View's job:** Display data

**Separation of concerns!**

### Pure PHP Views (Module 02)

```php
<?php
require_once 'header.php';

$posts = /* fetch from database */;
?>

<div class="container">
    <h1>Blog Posts</h1>
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><?= htmlspecialchars($post['title']) ?></h2>
            <p><?= htmlspecialchars($post['content']) ?></p>
            <a href="post.php?id=<?= $post['id'] ?>">Read more</a>
        </article>
    <?php endforeach; ?>
</div>

<?php require_once 'footer.php'; ?>
```

**Problems:**
- Repetitive includes (`header.php`, `footer.php`)
- Manual escaping with `htmlspecialchars()`
- PHP syntax mixed with HTML
- Hard to read and maintain
- No template inheritance

### Laravel Blade Views

```blade
@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Blog Posts</h1>
        @foreach($posts as $post)
            <article>
                <h2>{{ $post->title }}</h2>
                <p>{{ $post->content }}</p>
                <a href="{{ route('posts.show', $post) }}">Read more</a>
            </article>
        @endforeach
    </div>
@endsection
```

**Benefits:**
- Template inheritance (`@extends`)
- Automatic escaping (`{{ }}`)
- Cleaner syntax (`@foreach` vs `<?php foreach`)
- More readable
- Powerful directives

---

## Where Views Live

Views are stored in: `/resources/views/`

```
resources/views/
├── layouts/
│   └── app.blade.php
├── posts/
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
└── welcome.blade.php
```

### File Naming

Views use **`.blade.php`** extension:
- `welcome.blade.php` ✅
- `index.blade.php` ✅
- `show.php` ❌ (won't use Blade features)

**Why `.blade.php`?**
- `.blade` = Use Blade templating engine
- `.php` = Still a PHP file (can use PHP if needed)

---

## Creating Views

### Manual Creation

Create a file in `/resources/views/`:

```bash
touch resources/views/about.blade.php
```

### Using Artisan

There's no Artisan command for views (they're too simple), but you can create them with your editor:

```bash
code resources/views/posts/index.blade.php
```

---

## Returning Views from Controllers

### Basic View

```php
public function index()
{
    return view('posts.index');
}
```

**How it works:**
1. Laravel looks for `/resources/views/posts/index.blade.php`
2. Compiles Blade syntax to PHP
3. Executes the compiled PHP
4. Returns HTML

### View with Data

**Using `compact()`:**
```php
public function index()
{
    $posts = Post::all();
    return view('posts.index', compact('posts'));
}
```

**Using array:**
```php
public function index()
{
    $posts = Post::all();
    return view('posts.index', ['posts' => $posts]);
}
```

**Using `with()`:**
```php
public function index()
{
    $posts = Post::all();
    return view('posts.index')->with('posts', $posts);
}
```

**Chaining multiple:**
```php
return view('posts.show')
    ->with('post', $post)
    ->with('comments', $comments);
```

**All methods work the same!** Choose what feels natural.

---

## Blade Basics

**Blade** is Laravel's templating engine - a simple way to write clean templates.

### Displaying Data

**Escaped output** (safe from XSS):

```blade
<h1>{{ $title }}</h1>
<p>{{ $post->content }}</p>
```

**Pure PHP equivalent:**
```php
<h1><?= htmlspecialchars($title) ?></h1>
<p><?= htmlspecialchars($post->content) ?></p>
```

**Blade automatically escapes!** No XSS attacks.

**Unescaped output** (dangerous - only for trusted HTML):

```blade
<div>{!! $htmlContent !!}</div>
```

**Use carefully!** Only for HTML you trust (from a WYSIWYG editor you control).

### Displaying Variables

```blade
{{ $name }}
{{ $user->name }}
{{ $post['title'] }}
{{ $array[0] }}
```

### Default Values

```blade
{{ $name ?? 'Guest' }}
```

**Pure PHP equivalent:**
```php
<?= htmlspecialchars($name ?? 'Guest') ?>
```

### Blade Comments

```blade
{{-- This is a Blade comment --}}
{{-- It won't appear in rendered HTML --}}
```

**Different from HTML comments:**
```blade
<!-- HTML comment - appears in rendered HTML -->
{{-- Blade comment - removed before rendering --}}
```

---

## Blade Directives

**Directives** are special Blade commands that start with `@`.

### Conditionals

**`@if`, `@elseif`, `@else`, `@endif`:**

```blade
@if($posts->count() > 0)
    <h2>We have {{ $posts->count() }} posts</h2>
@elseif($posts->count() == 0)
    <h2>No posts yet</h2>
@else
    <h2>Something went wrong</h2>
@endif
```

**Pure PHP:**
```php
<?php if($posts->count() > 0): ?>
    <h2>We have <?= $posts->count() ?> posts</h2>
<?php elseif($posts->count() == 0): ?>
    <h2>No posts yet</h2>
<?php else: ?>
    <h2>Something went wrong</h2>
<?php endif; ?>
```

**`@unless` (opposite of @if):**

```blade
@unless($user->isAdmin())
    <p>You are not an administrator</p>
@endunless
```

Equivalent to:
```blade
@if(!$user->isAdmin())
    <p>You are not an administrator</p>
@endif
```

**`@isset`, `@empty`:**

```blade
@isset($post)
    <h1>{{ $post->title }}</h1>
@endisset

@empty($posts)
    <p>No posts available</p>
@endempty
```

**`@auth`, `@guest`:**

```blade
@auth
    <p>Welcome back, {{ auth()->user()->name }}!</p>
@endauth

@guest
    <p>Please <a href="{{ route('login') }}">login</a></p>
@endguest
```

**Pure PHP equivalent:**
```php
<?php if(isset($_SESSION['user_id'])): ?>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
<?php else: ?>
    <p>Please <a href="login.php">login</a></p>
<?php endif; ?>
```

### Loops

**`@foreach`:**

```blade
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->content }}</p>
    </article>
@endforeach
```

**Pure PHP:**
```php
<?php foreach($posts as $post): ?>
    <article>
        <h2><?= htmlspecialchars($post->title) ?></h2>
        <p><?= htmlspecialchars($post->content) ?></p>
    </article>
<?php endforeach; ?>
```

**`@forelse` (foreach with fallback):**

```blade
@forelse($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
    </article>
@empty
    <p>No posts found</p>
@endforelse
```

**Much cleaner than:**
```php
<?php if(count($posts) > 0): ?>
    <?php foreach($posts as $post): ?>
        <article>
            <h2><?= htmlspecialchars($post->title) ?></h2>
        </article>
    <?php endforeach; ?>
<?php else: ?>
    <p>No posts found</p>
<?php endif; ?>
```

**`@for`, `@while`:**

```blade
@for($i = 0; $i < 10; $i++)
    <p>Number: {{ $i }}</p>
@endfor

@while($condition)
    <p>Still true</p>
@endwhile
```

### Loop Variable

Inside `@foreach`, access the **`$loop` variable:**

```blade
@foreach($posts as $post)
    <div>
        @if($loop->first)
            <strong>First Post!</strong>
        @endif

        <h2>{{ $post->title }}</h2>

        <small>
            Item {{ $loop->iteration }} of {{ $loop->count }}
        </small>

        @if($loop->last)
            <hr>
        @endif
    </div>
@endforeach
```

**`$loop` properties:**
- `$loop->index` - 0-based index (0, 1, 2...)
- `$loop->iteration` - 1-based index (1, 2, 3...)
- `$loop->remaining` - Items remaining
- `$loop->count` - Total items
- `$loop->first` - Is first iteration?
- `$loop->last` - Is last iteration?
- `$loop->even` - Is even iteration?
- `$loop->odd` - Is odd iteration?
- `$loop->depth` - Nesting level
- `$loop->parent` - Parent loop in nested loops

**Example - zebra striping:**
```blade
@foreach($users as $user)
    <div class="{{ $loop->odd ? 'bg-gray-100' : 'bg-white' }}">
        {{ $user->name }}
    </div>
@endforeach
```

---

## Including Sub-Views

Break views into reusable pieces.

### `@include`

**Include another view:**

```blade
@include('partials.header')

<div class="content">
    <h1>My Page</h1>
</div>

@include('partials.footer')
```

**With data:**

```blade
@include('partials.post-card', ['post' => $post])
```

**With conditional:**

```blade
@includeIf('partials.admin-panel')
@includeWhen($user->isAdmin(), 'partials.admin-panel')
@includeUnless($user->isGuest(), 'partials.user-menu')
```

### Example: Post Card Component

**Create `/resources/views/partials/post-card.blade.php`:**

```blade
<article class="border rounded p-4 mb-4">
    <h2 class="text-xl font-bold">{{ $post->title }}</h2>
    <p class="text-gray-600">{{ Str::limit($post->content, 150) }}</p>
    <a href="{{ route('posts.show', $post) }}" class="text-blue-500">
        Read more →
    </a>
</article>
```

**Use in `/resources/views/posts/index.blade.php`:**

```blade
@extends('layouts.app')

@section('content')
    <h1>All Posts</h1>

    @foreach($posts as $post)
        @include('partials.post-card', ['post' => $post])
    @endforeach
@endsection
```

**Compare to pure PHP:**
```php
<?php include 'header.php'; ?>

<h1>All Posts</h1>

<?php foreach($posts as $post): ?>
    <?php include 'post-card.php'; ?>
<?php endforeach; ?>

<?php include 'footer.php'; ?>
```

Same concept, cleaner syntax!

---

## Raw PHP in Blade

Sometimes you need regular PHP:

```blade
@php
    $fullName = $user->first_name . ' ' . $user->last_name;
    $isAdmin = $user->role === 'admin';
@endphp

<h1>{{ $fullName }}</h1>
```

**Use sparingly!** Most logic should be in controllers.

---

## Displaying JSON

```blade
<script>
    var user = @json($user);
    console.log(user.name);
</script>
```

**Pure PHP equivalent:**
```php
<script>
    var user = <?= json_encode($user) ?>;
    console.log(user.name);
</script>
```

---

## CSRF Protection

Laravel protects against **CSRF attacks** (Module 08 - Security).

**Every form must include CSRF token:**

```blade
<form method="POST" action="{{ route('posts.store') }}">
    @csrf

    <input type="text" name="title">
    <button type="submit">Submit</button>
</form>
```

**`@csrf` generates:**
```html
<input type="hidden" name="_token" value="random-token">
```

**Without `@csrf`, form submission fails!**

**Pure PHP equivalent (Module 08):**
```php
<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <!-- form fields -->
</form>
```

Laravel handles this automatically!

---

## Method Spoofing

HTML forms only support GET and POST, but Laravel needs PUT/DELETE.

### `@method` Directive

```blade
<form method="POST" action="{{ route('posts.update', $post) }}">
    @csrf
    @method('PUT')

    <input type="text" name="title" value="{{ $post->title }}">
    <button type="submit">Update</button>
</form>
```

**`@method('PUT')` generates:**
```html
<input type="hidden" name="_method" value="PUT">
```

Laravel detects this and treats the request as PUT.

**For DELETE:**
```blade
<form method="POST" action="{{ route('posts.destroy', $post) }}">
    @csrf
    @method('DELETE')
    <button type="submit">Delete</button>
</form>
```

**Pure PHP:**
```php
// No native support - you'd manually check $_POST['_method']
```

---

## Displaying Validation Errors

Laravel automatically flashes validation errors to the session.

### Display All Errors

```blade
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

### Display Specific Error

```blade
<input type="text" name="title" value="{{ old('title') }}">

@error('title')
    <p class="text-red-500">{{ $message }}</p>
@enderror
```

### Old Input

Repopulate form after validation failure:

```blade
<input type="text" name="title" value="{{ old('title', $post->title ?? '') }}">

<textarea name="content">{{ old('content', $post->content ?? '') }}</textarea>
```

**Pure PHP (Module 04):**
```php
<input type="text" name="title" value="<?= htmlspecialchars($_SESSION['old_input']['title'] ?? '') ?>">

<?php if (isset($_SESSION['errors']['title'])): ?>
    <p class="error"><?= htmlspecialchars($_SESSION['errors']['title']) ?></p>
<?php endif; ?>
```

---

## Flash Messages

Display one-time messages (success, error, etc.).

### In Controller:

```php
return redirect()
    ->route('posts.index')
    ->with('success', 'Post created successfully!');
```

### In View:

```blade
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
```

**Pure PHP (Module 07):**
```php
// Store message
$_SESSION['success'] = 'Post created!';

// Display and clear
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
```

---

## Asset Management

### Public Assets

Assets in `/public` are directly accessible:

```blade
<img src="/images/logo.png">
<link rel="stylesheet" href="/css/app.css">
<script src="/js/app.js"></script>
```

### Using `asset()` Helper

**Better - generates full URL:**

```blade
<img src="{{ asset('images/logo.png') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
```

Generates: `http://your-app.test/images/logo.png`

### Vite (Laravel's Asset Bundler)

Laravel 9+ uses **Vite** for compiling assets.

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

This compiles and includes your CSS/JS files.

**We'll cover Vite in Module 13 (JavaScript/Alpine)!**

---

## Organizing Views

### Directory Structure

```
resources/views/
├── layouts/           # Master layouts
│   ├── app.blade.php
│   └── admin.blade.php
├── partials/          # Reusable components
│   ├── header.blade.php
│   ├── footer.blade.php
│   └── nav.blade.php
├── posts/             # Post-related views
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── auth/              # Authentication views
│   ├── login.blade.php
│   └── register.blade.php
└── errors/            # Error pages
    ├── 404.blade.php
    └── 500.blade.php
```

### Subdirectory Syntax

**In controller:**
```php
return view('posts.show');      // resources/views/posts/show.blade.php
return view('auth.login');      // resources/views/auth/login.blade.php
return view('errors.404');      // resources/views/errors/404.blade.php
```

**Dot notation** represents directories!

---

## Checking if View Exists

```php
if (view()->exists('posts.show')) {
    return view('posts.show', compact('post'));
}
```

---

## View Composers

Share data with multiple views automatically.

**In `App\Providers\AppServiceProvider.php`:**

```php
use Illuminate\Support\Facades\View;

public function boot()
{
    // Share with all views
    View::share('siteName', 'My Blog');

    // Share with specific views
    View::composer('partials.sidebar', function ($view) {
        $view->with('recentPosts', Post::latest()->take(5)->get());
    });
}
```

**Now in any view:**
```blade
<h1>{{ $siteName }}</h1>
```

**We'll cover service providers in advanced modules!**

---

## Complete Example: Posts Index

**Controller:**
```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }
}
```

**View (`resources/views/posts/index.blade.php`):**
```blade
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Blog Posts</h1>
            <a href="{{ route('posts.create') }}"
               class="bg-blue-500 text-white px-4 py-2 rounded">
                New Post
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @forelse($posts as $post)
            <article class="bg-white shadow rounded-lg p-6 mb-4">
                <h2 class="text-2xl font-semibold mb-2">
                    <a href="{{ route('posts.show', $post) }}" class="text-blue-600 hover:underline">
                        {{ $post->title }}
                    </a>
                </h2>

                <div class="text-gray-600 text-sm mb-3">
                    Posted on {{ $post->created_at->format('F j, Y') }}
                </div>

                <p class="text-gray-700 mb-4">
                    {{ Str::limit($post->content, 200) }}
                </p>

                <div class="flex space-x-3">
                    <a href="{{ route('posts.show', $post) }}"
                       class="text-blue-500 hover:underline">
                        Read more
                    </a>

                    @auth
                        <a href="{{ route('posts.edit', $post) }}"
                           class="text-yellow-500 hover:underline">
                            Edit
                        </a>

                        <form method="POST" action="{{ route('posts.destroy', $post) }}"
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-red-500 hover:underline"
                                    onclick="return confirm('Are you sure?')">
                                Delete
                            </button>
                        </form>
                    @endauth
                </div>
            </article>
        @empty
            <div class="bg-gray-100 p-8 rounded text-center">
                <p class="text-gray-600">No posts yet. Be the first to create one!</p>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
```

**Compare to pure PHP - would be 2-3x longer with manual escaping, session handling, pagination links!**

---

## Summary

**What You Learned:**
- What views are and their role in MVC
- Where views are stored (`/resources/views/`)
- Blade templating syntax and directives
- Displaying data with `{{ }}` (escaped) and `{!! !!}` (unescaped)
- Control structures (@if, @foreach, @forelse)
- Including sub-views (@include)
- CSRF protection (@csrf)
- Method spoofing (@method)
- Displaying validation errors
- Flash messages
- Organizing views

**Key Takeaways:**
1. **Blade is cleaner** than pure PHP templates
2. **`{{ }}` auto-escapes** preventing XSS attacks
3. **Directives** (`@if`, `@foreach`) are more readable than PHP tags
4. **`@csrf` is required** for all POST/PUT/DELETE forms
5. **`@method('PUT')` enables** RESTful routes
6. **Validation errors** are automatically available in views
7. **Keep logic in controllers** - views are for display only

**Next Lesson:** We'll dive deeper into Blade with layouts, components, and advanced directives!

---

## Practice Exercise

Create these views:

**1. Layout (`layouts/app.blade.php`)**
- Include navigation
- Define content section
- Include footer

**2. Posts Index (`posts/index.blade.php`)**
- List all posts
- Show success message if present
- Use @forelse for empty state
- Add pagination

**3. Post Card Partial (`partials/post-card.blade.php`)**
- Display post title, excerpt, date
- Link to full post
- Reusable component

**4. Create Form (`posts/create.blade.php`)**
- Include @csrf
- Show validation errors
- Repopulate with old input

---

## Quick Quiz

**1. What extension do Blade files use?**
- `.blade.php`

**2. What's the difference between `{{ }}` and `{!! !!}`?**
- `{{ }}` escapes HTML (safe)
- `{!! !!}` doesn't escape (dangerous, only for trusted HTML)

**3. What directive includes CSRF protection in forms?**
- `@csrf`

**4. How do you check if a variable is set in Blade?**
```blade
@isset($variable)
    <!-- content -->
@endisset
```

**5. What's the @forelse directive for?**
- Foreach with an empty state fallback

**6. How do you access the loop iteration number?**
- `$loop->iteration` (1-based) or `$loop->index` (0-based)

**7. How do you display a validation error for a specific field?**
```blade
@error('fieldname')
    {{ $message }}
@enderror
```

---

**Next**: [Lesson 06 - Blade Directives and Layouts →](06-blade-directives-layouts.md)
