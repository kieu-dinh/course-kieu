# 03 - Blade Templates

## Objective
Master Laravel's Blade templating engine to create dynamic views with clean, readable syntax.

## Prerequisites
- Completed "02-routes" exercise
- Basic HTML knowledge
- Understanding of template engines

## Instructions

### Step 1: Create Your First View
Create a view file at `resources/views/posts/index.blade.php`:

```blade
<h1>Posts</h1>
<p>Welcome to our blog</p>
```

### Step 2: Return View from Controller
Update `PostController::index()`:

```php
public function index()
{
    return view('posts.index');
}
```

### Step 3: Pass Data to Views
Pass data from controller:

```php
public function index()
{
    $posts = [
        ['id' => 1, 'title' => 'First Post', 'author' => 'John'],
        ['id' => 2, 'title' => 'Second Post', 'author' => 'Jane'],
    ];

    return view('posts.index', ['posts' => $posts]);
    // Or use compact: return view('posts.index', compact('posts'));
}
```

### Step 4: Display Data in Views
Update `resources/views/posts/index.blade.php`:

```blade
<h1>Posts</h1>

@foreach($posts as $post)
    <div>
        <h2>{{ $post['title'] }}</h2>
        <p>By {{ $post['author'] }}</p>
    </div>
@endforeach
```

### Step 5: Use Control Structures
Add conditional logic:

```blade
@if(count($posts) > 0)
    <p>{{ count($posts) }} posts found</p>
@else
    <p>No posts yet</p>
@endif

@unless($posts->isEmpty())
    <ul>
    @foreach($posts as $post)
        <li>{{ $post['title'] }}</li>
    @endforeach
    </ul>
@endunless
```

### Step 6: Create a Layout
Create `resources/views/layout.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - Blog</title>
</head>
<body>
    <nav>
        <ul>
            <li><a href="/posts">Posts</a></li>
            <li><a href="/about">About</a></li>
        </ul>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer>
        <p>&copy; 2024 My Blog</p>
    </footer>
</body>
</html>
```

### Step 7: Extend Layout
Update `resources/views/posts/index.blade.php`:

```blade
@extends('layout')

@section('title', 'All Posts')

@section('content')
    <h1>Posts</h1>

    @forelse($posts as $post)
        <article>
            <h2>{{ $post['title'] }}</h2>
            <p>By {{ $post['author'] }}</p>
        </article>
    @empty
        <p>No posts available</p>
    @endforelse
@endsection
```

### Step 8: Create Show View
Create `resources/views/posts/show.blade.php`:

```blade
@extends('layout')

@section('title', $post['title'])

@section('content')
    <article>
        <h1>{{ $post['title'] }}</h1>
        <p><strong>Author:</strong> {{ $post['author'] }}</p>
        <p>{{ $post['content'] }}</p>
    </article>

    <a href="/posts">Back to Posts</a>
@endsection
```

Update `PostController::show()`:

```php
public function show($id)
{
    $post = [
        'id' => $id,
        'title' => 'Post Title',
        'author' => 'John Doe',
        'content' => 'This is the post content...'
    ];

    return view('posts.show', compact('post'));
}
```

### Step 9: Use Components (Optional Enhancement)
Create a reusable post card component at `resources/views/components/post-card.blade.php`:

```blade
<div class="post-card">
    <h3>{{ $title }}</h3>
    <p>{{ $excerpt }}</p>
    <a href="/posts/{{ $id }}">Read more</a>
</div>
```

Use in your index view:

```blade
@foreach($posts as $post)
    <x-post-card
        :id="$post['id']"
        :title="$post['title']"
        :excerpt="$post['excerpt']"
    />
@endforeach
```

## Deliverables
- [ ] Multiple views created (index, show, layout)
- [ ] Data successfully passed and displayed
- [ ] Layout template with sections working
- [ ] All control structures (if, foreach, etc.) implemented
- [ ] Components used for reusable UI elements
- [ ] No PHP code visible in HTML output
- [ ] All pages render correctly in browser

## Resources
- [Blade Template Syntax](https://laravel.com/docs/11.x/blade)
- [Blade Control Structures](https://laravel.com/docs/11.x/blade#control-structures)
- [Blade Components](https://laravel.com/docs/11.x/blade#components)

## Tips
- Use `{{ }}` for escaping output
- Use `{!! !!}` to output unescaped HTML (be careful!)
- Use `@` to start Blade directives
- Blade compiles to PHP and is cached automatically
- Use `php artisan view:clear` to clear view cache if needed
- Components help reduce code duplication
