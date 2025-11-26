# 04 - Simple Blog MVC

## Objective
Build a complete blog application using MVC architecture, integrating routes, controllers, and Blade templates.

## Prerequisites
- Completed "01-first-app", "02-routes", and "03-blade" exercises
- Understanding of MVC pattern
- Basic database knowledge

## Instructions

### Step 1: Set Up Database
Configure your `.env` file with database credentials:

```
DB_CONNECTION=sqlite
DB_DATABASE=/path/to/database.sqlite
```

Or use MySQL:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog
DB_USERNAME=root
DB_PASSWORD=
```

Run migration:

```bash
php artisan migrate
```

### Step 2: Create Post Model and Migration
Generate model with migration:

```bash
php artisan make:model Post -m
```

Edit the migration file in `database/migrations/`:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('slug')->unique();
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

### Step 3: Create Post Controller
Generate resource controller:

```bash
php artisan make:controller PostController --resource
```

This creates all necessary methods: index, create, store, show, edit, update, destroy.

### Step 4: Implement Index Action
In `app/Http/Controllers/PostController.php`:

```php
public function index()
{
    $posts = Post::latest()->paginate(10);
    return view('posts.index', compact('posts'));
}
```

### Step 5: Create Index View
Create `resources/views/posts/index.blade.php`:

```blade
@extends('layout')

@section('title', 'Blog')

@section('content')
    <div class="container">
        <h1>Blog Posts</h1>

        <a href="/posts/create" class="btn btn-primary">New Post</a>

        @forelse($posts as $post)
            <article class="post-item">
                <h2>{{ $post->title }}</h2>
                <p class="meta">{{ $post->created_at->format('M d, Y') }}</p>
                <p>{{ Str::limit($post->content, 200) }}</p>
                <a href="/posts/{{ $post->id }}">Read more</a>
            </article>
        @empty
            <p>No posts yet. <a href="/posts/create">Create one!</a></p>
        @endforelse

        {{ $posts->links() }}
    </div>
@endsection
```

### Step 6: Implement Create and Store Actions
In `PostController`:

```php
public function create()
{
    return view('posts.create');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    Post::create($validated);

    return redirect('/posts')->with('success', 'Post created!');
}
```

### Step 7: Create Form View
Create `resources/views/posts/create.blade.php`:

```blade
@extends('layout')

@section('title', 'Create Post')

@section('content')
    <div class="container">
        <h1>Create New Post</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/posts">
            @csrf
            <div>
                <label for="title">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required>
                @error('title')<span class="error">{{ $message }}</span>@enderror
            </div>

            <div>
                <label for="content">Content</label>
                <textarea name="content" id="content" rows="10" required>{{ old('content') }}</textarea>
                @error('content')<span class="error">{{ $message }}</span>@enderror
            </div>

            <button type="submit">Create Post</button>
        </form>
    </div>
@endsection
```

### Step 8: Implement Show Action
In `PostController`:

```php
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
```

Create `resources/views/posts/show.blade.php`:

```blade
@extends('layout')

@section('title', $post->title)

@section('content')
    <div class="container">
        <article>
            <h1>{{ $post->title }}</h1>
            <p class="meta">{{ $post->created_at->format('M d, Y') }}</p>
            <div class="content">
                {!! nl2br(e($post->content)) !!}
            </div>
        </article>

        <div class="actions">
            <a href="/posts">Back to Posts</a>
            <a href="/posts/{{ $post->id }}/edit">Edit</a>
            <form method="POST" action="/posts/{{ $post->id }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Sure?')">Delete</button>
            </form>
        </div>
    </div>
@endsection
```

### Step 9: Implement Edit and Update Actions
In `PostController`:

```php
public function edit(Post $post)
{
    return view('posts.edit', compact('post'));
}

public function update(Request $request, Post $post)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);

    $post->update($validated);

    return redirect("/posts/{$post->id}")->with('success', 'Post updated!');
}
```

Create `resources/views/posts/edit.blade.php` (similar to create but with existing data).

### Step 10: Implement Delete Action
In `PostController`:

```php
public function destroy(Post $post)
{
    $post->delete();
    return redirect('/posts')->with('success', 'Post deleted!');
}
```

### Step 11: Define Routes
In `routes/web.php`:

```php
use App\Http\Controllers\PostController;

Route::resource('posts', PostController::class);
Route::get('/', [PostController::class, 'index']);
```

### Step 12: Create Layout
Create `resources/views/layout.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - Blog</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .post-item { border: 1px solid #ddd; padding: 15px; margin: 15px 0; }
        .meta { color: #666; font-size: 0.9em; }
        form div { margin: 15px 0; }
        input, textarea { width: 100%; padding: 8px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="/">Home</a> | <a href="/posts">Blog</a>
        </nav>
    </header>

    <main class="container">
        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    <footer>
        <p>&copy; 2024 My Blog</p>
    </footer>
</body>
</html>
```

### Step 13: Seed Sample Data (Optional)
Create seeder:

```bash
php artisan make:seeder PostSeeder
```

Add to seeder:

```php
public function run(): void
{
    Post::create([
        'title' => 'Welcome to our blog',
        'content' => 'This is our first post...',
    ]);
}
```

Run:

```bash
php artisan db:seed --class=PostSeeder
```

## Deliverables
- [ ] Database migration created and run
- [ ] Post model created
- [ ] PostController with all CRUD methods
- [ ] All views created (index, create, show, edit)
- [ ] Routes properly configured
- [ ] Full CRUD functionality working
- [ ] Form validation implemented
- [ ] Error messages displayed
- [ ] Layout template properly structured
- [ ] Pagination working (if list is long)

## Resources
- [Eloquent ORM](https://laravel.com/docs/11.x/eloquent)
- [Controllers](https://laravel.com/docs/11.x/controllers)
- [Blade Templates](https://laravel.com/docs/11.x/blade)
- [Validation](https://laravel.com/docs/11.x/validation)

## Tips
- Always use `{{ }}` for data output to prevent XSS
- Use `@csrf` in all forms
- Use `@method('DELETE')` or `@method('PUT')` for non-GET forms
- Use `old()` helper to repopulate form fields on validation error
- Use `@error()` to display validation errors
- Test each CRUD operation thoroughly
- Keep views simple and use components for complex UI
