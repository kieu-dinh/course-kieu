# Lesson 06 - Blade Directives and Layouts

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## Template Inheritance

**Template inheritance** lets you define a master layout that child views extend.

### The Problem: Code Duplication

**Pure PHP approach:**

```php
<!-- posts/index.php -->
<?php include '../templates/header.php'; ?>
<h1>Posts</h1>
<!-- content -->
<?php include '../templates/footer.php'; ?>

<!-- posts/show.php -->
<?php include '../templates/header.php'; ?>
<h1>Single Post</h1>
<!-- content -->
<?php include '../templates/footer.php'; ?>

<!-- about.php -->
<?php include '../templates/header.php'; ?>
<h1>About</h1>
<!-- content -->
<?php include '../templates/footer.php'; ?>
```

**Problems:**
- Repeated includes in every file
- Hard to modify layout (must edit every file)
- No way to define "sections" that children fill in
- Manual management of which layout to use

### The Solution: Blade Layouts

**Create a master layout once, extend it everywhere!**

---

## Creating a Master Layout

**Create `/resources/views/layouts/app.blade.php`:**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Blog')</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    @stack('styles')
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="{{ route('home') }}" class="text-xl font-bold">
                    My Blog
                </a>

                <div class="space-x-4">
                    <a href="{{ route('posts.index') }}" class="text-gray-600 hover:text-gray-900">
                        Posts
                    </a>
                    <a href="{{ route('about') }}" class="text-gray-600 hover:text-gray-900">
                        About
                    </a>

                    @auth
                        <a href="{{ route('posts.create') }}" class="text-blue-600 hover:text-blue-800">
                            New Post
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button class="text-gray-600 hover:text-gray-900">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-6 text-center">
            <p>&copy; {{ date('Y') }} My Blog. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
```

**Key directives:**
- `@yield('content')` - Define sections that children fill
- `@stack('styles')` - Stack for CSS
- `@stack('scripts')` - Stack for JavaScript

---

## Extending Layouts

**Create a child view: `/resources/views/posts/index.blade.php`:**

```blade
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
    <h1 class="text-3xl font-bold mb-6">All Posts</h1>

    @forelse($posts as $post)
        <article class="bg-white rounded-lg shadow p-6 mb-4">
            <h2 class="text-2xl font-semibold">{{ $post->title }}</h2>
            <p class="text-gray-600 mt-2">{{ $post->excerpt }}</p>
        </article>
    @empty
        <p class="text-gray-600">No posts yet.</p>
    @endforelse
@endsection
```

**How it works:**
1. `@extends('layouts.app')` - Use the app layout
2. `@section('content')` - Fill the "content" section
3. Layout wraps around the content

**Result:** Full HTML page with navigation, footer, styles, but you only wrote the content!

---

## `@yield` vs `@section`

### `@yield` - Simple Placeholders

**In layout:**
```blade
<title>@yield('title', 'Default Title')</title>
```

**In child:**
```blade
@section('title', 'Posts')
```

Or:
```blade
@section('title')
    Posts - My Blog
@endsection
```

**Use `@yield` for:**
- Simple text (titles, meta descriptions)
- Single-line content
- Optional with default value

### `@section` - Complex Content

**In layout:**
```blade
@section('sidebar')
    <div class="sidebar">
        Default sidebar content
    </div>
@show
```

**`@show` vs `@endsection`:**
- `@show` - Display default content (can be overridden)
- `@endsection` - Just define the section (no default)

**In child - override completely:**
```blade
@section('sidebar')
    <div class="sidebar">
        Custom sidebar content
    </div>
@endsection
```

**In child - extend (add to default):**
```blade
@section('sidebar')
    @parent

    <div class="additional-content">
        Extra sidebar content
    </div>
@endsection
```

**`@parent` includes the layout's default content!**

---

## Stacks

**Stacks** let you push content from multiple places to one location.

### Push to Stack

**In layout:**
```blade
<head>
    @stack('styles')
</head>

<body>
    @stack('scripts')
</body>
```

**In child view:**
```blade
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="/css/posts.css">
@endpush

@section('content')
    <!-- content -->
@endsection

@push('scripts')
    <script src="/js/posts.js"></script>
@endpush
```

**Result:**
```html
<head>
    <link rel="stylesheet" href="/css/posts.css">
</head>

<body>
    <script src="/js/posts.js"></script>
</body>
```

### `@prepend` - Add to Beginning

```blade
@prepend('scripts')
    <script src="/js/critical.js"></script>
@endprepend
```

This adds to the **beginning** of the stack (before other @push content).

### Use Case: Page-Specific Assets

**Different pages need different JavaScript:**

**posts/index.blade.php:**
```blade
@push('scripts')
    <script src="/js/posts-list.js"></script>
@endpush
```

**posts/create.blade.php:**
```blade
@push('scripts')
    <script src="/js/posts-editor.js"></script>
    <script src="/js/markdown-preview.js"></script>
@endpush
```

Only the necessary scripts load on each page!

---

## Components

**Components** are reusable UI pieces (like alert boxes, buttons, cards).

### Anonymous Components (Blade Files)

**Create `/resources/views/components/alert.blade.php`:**

```blade
<div class="border rounded p-4 mb-4 {{ $type === 'success' ? 'bg-green-100 border-green-400' : 'bg-red-100 border-red-400' }}">
    {{ $slot }}
</div>
```

**Use in any view:**

```blade
<x-alert type="success">
    Post created successfully!
</x-alert>

<x-alert type="error">
    Something went wrong!
</x-alert>
```

**`$slot` is replaced with content between tags!**

### Component with Attributes

**Create `/resources/views/components/button.blade.php`:**

```blade
<button {{ $attributes->merge(['class' => 'px-4 py-2 rounded font-semibold']) }}>
    {{ $slot }}
</button>
```

**Use:**

```blade
<x-button class="bg-blue-500 text-white">
    Save
</x-button>

<x-button class="bg-red-500 text-white" type="submit">
    Delete
</x-button>
```

**Result:**
```html
<button class="px-4 py-2 rounded font-semibold bg-blue-500 text-white">
    Save
</button>

<button class="px-4 py-2 rounded font-semibold bg-red-500 text-white" type="submit">
    Delete
</button>
```

**`$attributes->merge()` combines default and passed attributes!**

### Component with Named Slots

**Create `/resources/views/components/card.blade.php`:**

```blade
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    @isset($header)
        <div class="bg-gray-100 px-6 py-4 border-b">
            {{ $header }}
        </div>
    @endisset

    <div class="px-6 py-4">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="bg-gray-50 px-6 py-4 border-t">
            {{ $footer }}
        </div>
    @endisset
</div>
```

**Use:**

```blade
<x-card>
    <x-slot:header>
        <h2 class="text-xl font-bold">Post Title</h2>
    </x-slot:header>

    <p>This is the post content in the default slot.</p>

    <x-slot:footer>
        <span class="text-gray-500">Posted on Jan 1, 2024</span>
    </x-slot:footer>
</x-card>
```

**Named slots** let you fill multiple sections!

### Class-Based Components

Generate component with PHP class:

```bash
php artisan make:component Alert
```

Creates:
- `/app/View/Components/Alert.php` (logic)
- `/resources/views/components/alert.blade.php` (template)

**Alert.php:**
```php
<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
    public $type;
    public $message;

    public function __construct($type = 'info', $message = '')
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return view('components.alert');
    }
}
```

**alert.blade.php:**
```blade
<div class="alert alert-{{ $type }}">
    {{ $message ?? $slot }}
</div>
```

**Use:**
```blade
<x-alert type="success" message="Post created!" />

<x-alert type="error">
    Custom error message here
</x-alert>
```

**Use class-based when you need:**
- Complex logic
- Database queries
- Multiple methods
- Testable components

---

## Conditional Classes

### `@class` Directive

**Add classes conditionally:**

```blade
<div @class([
    'bg-white',
    'p-4',
    'rounded',
    'border' => $post->published,
    'shadow-lg' => $post->featured,
    'opacity-50' => !$post->published
])>
    {{ $post->title }}
</div>
```

**Result if `$post->published` and `$post->featured` are true:**
```html
<div class="bg-white p-4 rounded border shadow-lg">
    Post Title
</div>
```

**Pure PHP equivalent:**
```php
<div class="bg-white p-4 rounded
    <?= $post->published ? 'border' : '' ?>
    <?= $post->featured ? 'shadow-lg' : '' ?>
    <?= !$post->published ? 'opacity-50' : '' ?>">
    <?= htmlspecialchars($post->title) ?>
</div>
```

Much messier!

---

## Conditional Styles

### `@style` Directive

```blade
<div @style([
    'background-color: blue',
    'color: white' => $highlighted,
    'font-weight: bold' => $important
])>
    Content
</div>
```

---

## More Blade Directives

### `@once`

**Execute code only once**, even if component is rendered multiple times:

```blade
@once
    @push('scripts')
        <script src="/js/calendar.js"></script>
    @endpush
@endonce
```

**Use case:** Include JavaScript library once, even if component appears multiple times on page.

### `@production` / `@env`

**Execute only in specific environments:**

```blade
@production
    <script src="https://analytics.com/tracking.js"></script>
@endproduction

@env('local')
    <div class="debug-toolbar">Debug Info</div>
@endenv

@env(['staging', 'production'])
    <script src="https://cdn.com/optimized.js"></script>
@endenv
```

### `@verbatim`

**Prevent Blade from parsing** (useful for Vue/Alpine):

```blade
@verbatim
    <div x-data="{ count: 0 }">
        <button @click="count++">
            Count: {{ count }}
        </button>
    </div>
@endverbatim
```

Without `@verbatim`, Blade would try to parse `{{ count }}` as Blade syntax!

### `@aware`

**Access data from parent component:**

```blade
{{-- In parent component --}}
<x-card :user="$user">
    <x-card-body>
        {{-- Child can access $user via @aware --}}
    </x-card-body>
</x-card>
```

---

## Form Macros

### `@checked` and `@selected`

**Conditionally add checked/selected attributes:**

```blade
<input type="checkbox"
       name="published"
       value="1"
       @checked(old('published', $post->published))>

<select name="category">
    <option value="tech" @selected(old('category') == 'tech')>
        Technology
    </option>
    <option value="news" @selected(old('category') == 'news')>
        News
    </option>
</select>
```

**Pure PHP:**
```php
<input type="checkbox"
       name="published"
       value="1"
       <?= old('published', $post->published) ? 'checked' : '' ?>>
```

### `@disabled` and `@readonly`

```blade
<input type="text" name="username" @disabled($user->isBanned())>

<input type="text" name="email" @readonly(!$user->canEditEmail())>
```

### `@required`

```blade
<input type="text" name="title" @required(!$post->exists)>
```

---

## Complete Layout Example

### Main Layout

**layouts/app.blade.php:**

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/app.css'])

    @stack('styles')
</head>
<body class="bg-gray-50">
    @include('partials.nav')

    <main class="container mx-auto px-4 py-8">
        @include('partials.alerts')

        @yield('content')
    </main>

    @include('partials.footer')

    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
```

### Navigation Partial

**partials/nav.blade.php:**

```blade
<nav class="bg-white shadow">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold">
                {{ config('app.name') }}
            </a>

            <div class="flex space-x-6">
                <a href="{{ route('posts.index') }}"
                   @class([
                       'hover:text-blue-600',
                       'text-blue-600 font-semibold' => request()->routeIs('posts.*')
                   ])>
                    Posts
                </a>

                @auth
                    <a href="{{ route('posts.create') }}" class="hover:text-blue-600">
                        New Post
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="hover:text-blue-600">
                            Logout ({{ auth()->user()->name }})
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-600">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-blue-600">Register</a>
                @endauth
            </div>
        </div>
    </div>
</nav>
```

### Alerts Partial

**partials/alerts.blade.php:**

```blade
@if(session('success'))
    <x-alert type="success">
        {{ session('success') }}
    </x-alert>
@endif

@if(session('error'))
    <x-alert type="error">
        {{ session('error') }}
    </x-alert>
@endif

@if($errors->any())
    <x-alert type="error">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
```

### Post View

**posts/show.blade.php:**

```blade
@extends('layouts.app')

@section('title', $post->title)

@push('styles')
    <link rel="stylesheet" href="/css/syntax-highlighting.css">
@endpush

@section('content')
    <article class="max-w-3xl mx-auto">
        <h1 class="text-4xl font-bold mb-4">{{ $post->title }}</h1>

        <div class="text-gray-600 mb-6">
            <span>By {{ $post->author->name }}</span>
            <span>•</span>
            <span>{{ $post->created_at->format('F j, Y') }}</span>
        </div>

        <div class="prose max-w-none">
            {!! $post->content !!}
        </div>

        @auth
            @if(auth()->id() === $post->author_id)
                <div class="mt-8 flex space-x-4">
                    <a href="{{ route('posts.edit', $post) }}"
                       class="text-blue-600 hover:underline">
                        Edit
                    </a>

                    <form method="POST" action="{{ route('posts.destroy', $post) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-red-600 hover:underline"
                                onclick="return confirm('Are you sure?')">
                            Delete
                        </button>
                    </form>
                </div>
            @endif
        @endauth
    </article>
@endsection

@push('scripts')
    <script src="/js/code-highlighter.js"></script>
@endpush
```

---

## Best Practices

### 1. One Master Layout

Create **one main layout** (`layouts/app.blade.php`) for consistency.

Create additional layouts only when necessary:
- `layouts/admin.blade.php` - Admin panel
- `layouts/auth.blade.php` - Login/register pages
- `layouts/guest.blade.php` - Public pages

### 2. Small, Reusable Components

Break UI into components:

```
components/
├── alert.blade.php
├── button.blade.php
├── card.blade.php
├── form/
│   ├── input.blade.php
│   ├── textarea.blade.php
│   └── select.blade.php
└── post-card.blade.php
```

### 3. Partials for Navigation/Footer

```
partials/
├── nav.blade.php
├── footer.blade.php
├── sidebar.blade.php
└── breadcrumbs.blade.php
```

**Include in layout:**
```blade
@include('partials.nav')
@include('partials.sidebar')
```

### 4. Use Stacks for Assets

Don't hardcode scripts in every page:

**Bad:**
```blade
@section('content')
    <!-- content -->
@endsection

<script src="/js/specific-page.js"></script>  <!-- Outside section! -->
```

**Good:**
```blade
@section('content')
    <!-- content -->
@endsection

@push('scripts')
    <script src="/js/specific-page.js"></script>
@endpush
```

### 5. Name Sections Clearly

**Good names:**
- `@yield('content')` - Main content
- `@yield('title')` - Page title
- `@yield('meta')` - Meta tags
- `@stack('styles')` - CSS
- `@stack('scripts')` - JavaScript

**Avoid:**
- `@yield('stuff')`
- `@yield('body')`
- `@yield('section1')`

---

## Pure PHP Comparison

**Pure PHP approach (Modules 02-10):**

```php
<!-- templates/header.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?? 'My Site' ?></title>
</head>
<body>
    <?php include 'nav.php'; ?>

<!-- index.php -->
<?php
$pageTitle = 'Home';
include 'templates/header.php';
?>
<h1>Home Page</h1>
<?php include 'templates/footer.php'; ?>

<!-- about.php -->
<?php
$pageTitle = 'About';
include 'templates/header.php';
?>
<h1>About Page</h1>
<?php include 'templates/footer.php'; ?>
```

**Problems:**
- Must include header/footer in every file
- Variables must be set before including header
- Can't "yield" to multiple sections
- No inheritance (only includes)
- Hard to maintain

**Laravel Blade:**

```blade
<!-- layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'My Site')</title>
</head>
<body>
    @include('partials.nav')
    @yield('content')
</body>
</html>

<!-- index.blade.php -->
@extends('layouts.app')
@section('title', 'Home')
@section('content')
    <h1>Home Page</h1>
@endsection

<!-- about.blade.php -->
@extends('layouts.app')
@section('title', 'About')
@section('content')
    <h1>About Page</h1>
@endsection
```

**Much cleaner and maintainable!**

---

## Summary

**What You Learned:**
- Template inheritance with `@extends`
- Defining sections with `@yield` and `@section`
- Stacks for assets with `@push` and `@stack`
- Creating reusable components
- Named slots in components
- Conditional classes with `@class`
- Blade directives for production, environment, etc.
- Form helpers (`@checked`, `@selected`, `@disabled`)
- Best practices for organizing layouts and components

**Key Takeaways:**
1. **`@extends` creates inheritance** - master layout wraps child views
2. **`@yield` defines sections** that children fill
3. **`@push/@stack` manages assets** per page
4. **Components are reusable** UI elements
5. **Keep layouts clean** - extract to partials and components
6. **Use `@class` for conditional styling** - cleaner than PHP ternaries
7. **One master layout** with feature-specific child layouts

**Next Lesson:** We'll learn about migrations and how to create database tables with version control!

---

## Practice Exercise

**Create a complete layout system:**

1. **Master layout** (`layouts/app.blade.php`)
   - Include nav, alerts, content section, footer
   - Define stacks for styles and scripts

2. **Navigation partial** (`partials/nav.blade.php`)
   - Logo, links, auth state

3. **Alert component** (`components/alert.blade.php`)
   - Success/error styling based on type
   - Closeable button

4. **Card component** (`components/card.blade.php`)
   - Header, body, footer slots

5. **Posts index** using all above
   - Extend layout
   - Use components
   - Push page-specific scripts

---

## Quick Quiz

**1. What's the difference between `@yield` and `@section`?**
- `@yield` defines a placeholder (simple)
- `@section` can have default content with `@show`

**2. What does `@parent` do?**
- Includes the parent layout's section content

**3. How do you push styles to a stack?**
```blade
@push('styles')
    <link rel="stylesheet" href="...">
@endpush
```

**4. What's `$slot` in components?**
- The content between component tags

**5. How do you conditionally add a CSS class?**
```blade
@class(['base-class', 'active' => $isActive])
```

**6. What does `@once` do?**
- Ensures code runs only once, even if included multiple times

---

**Next**: [Lesson 07 - Migrations and Databases →](07-migrations-databases.md)
