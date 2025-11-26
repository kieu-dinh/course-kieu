# Lesson 2: Creating Livewire Components

**Duration**: 60 minutes
**Prerequisites**: Lesson 1 completed

---

## What You'll Learn

- Component naming conventions
- Different ways to create components
- Component organization and structure
- Passing data to components
- Inline components
- Full-page components

---

## Creating Components

### The Make Command

```bash
php artisan make:livewire <name>
```

Livewire supports several naming formats:

```bash
# Simple name
php artisan make:livewire Counter
# Creates: app/Livewire/Counter.php
# Creates: resources/views/livewire/counter.blade.php

# Nested (dot notation)
php artisan make:livewire users.index
# Creates: app/Livewire/Users/Index.php
# Creates: resources/views/livewire/users/index.blade.php

# Nested (slash notation)
php artisan make:livewire users/show-profile
# Creates: app/Livewire/Users/ShowProfile.php
# Creates: resources/views/livewire/users/show-profile.blade.php

# Multi-word (kebab-case)
php artisan make:livewire user-profile
# Creates: app/Livewire/UserProfile.php
# Creates: resources/views/livewire/user-profile.blade.php
```

### Naming Conventions

**Best Practices:**

✅ **DO:**
- Use descriptive names: `ShowUserProfile`, not `Profile`
- Use verbs for actions: `CreatePost`, `EditUser`
- Use resource-based names: `PostList`, `CommentCard`
- Group related components: `users/Index`, `users/Create`

❌ **DON'T:**
- Use Laravel reserved words: `Controller`, `Model`, `View`
- Use too generic names: `Component`, `Page`, `Form`
- Mix naming styles inconsistently

**Common Patterns:**

```bash
# CRUD operations
php artisan make:livewire posts.index      # List all posts
php artisan make:livewire posts.create     # Create form
php artisan make:livewire posts.edit       # Edit form
php artisan make:livewire posts.show       # Single post

# Specific features
php artisan make:livewire search-bar
php artisan make:livewire shopping-cart
php artisan make:livewire notification-bell

# Admin sections
php artisan make:livewire admin.users.table
php artisan make:livewire admin.dashboard
```

---

## Component Anatomy

### The PHP Class

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

class ShowPost extends Component
{
    // 1. Properties (component state)
    public $postId;
    public $comment = '';

    // 2. Lifecycle hooks (optional)
    public function mount($postId)
    {
        $this->postId = $postId;
    }

    // 3. Computed properties (optional)
    public function getPostProperty()
    {
        return Post::find($this->postId);
    }

    // 4. Actions (methods called from view)
    public function addComment()
    {
        $this->post->comments()->create([
            'body' => $this->comment
        ]);

        $this->comment = '';
    }

    // 5. Render method (required)
    #[Title('View Post')]
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.show-post');
    }
}
```

### The Blade View

```html
<!-- Single root element (required) -->
<div class="container mx-auto px-4 py-8">
    <!-- Access properties directly -->
    <h1 class="text-3xl font-bold">{{ $post->title }}</h1>

    <div class="prose max-w-none mt-4">
        {!! $post->content !!}
    </div>

    <!-- Comments section -->
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-4">Comments</h2>

        @foreach($post->comments as $comment)
            <div class="bg-gray-100 p-4 rounded mb-2">
                {{ $comment->body }}
            </div>
        @endforeach

        <!-- Add comment form -->
        <form wire:submit="addComment" class="mt-4">
            <textarea
                wire:model="comment"
                class="w-full px-3 py-2 border rounded"
                placeholder="Add a comment..."
            ></textarea>

            <button
                type="submit"
                class="mt-2 bg-blue-500 text-white px-4 py-2 rounded"
            >
                Post Comment
            </button>
        </form>
    </div>
</div>
```

---

## Component Types

### 1. Standard Components

Most common type. Used anywhere in your application.

**Create:**
```bash
php artisan make:livewire SearchBar
```

**Use:**
```html
<!-- In any Blade view -->
<livewire:search-bar />

<!-- Or -->
@livewire('search-bar')
```

### 2. Full-Page Components

Components that act as entire pages (with their own routes).

**Create:**
```bash
php artisan make:livewire posts.index
```

**Route:**
```php
use App\Livewire\Posts\Index;

Route::get('/posts', Index::class);
```

**Component:**
```php
<?php

namespace App\Livewire\Posts;

use Livewire\Component;
use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Title('All Posts')]
#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.posts.index', [
            'posts' => Post::latest()->get()
        ]);
    }
}
```

**Benefits:**
- Clean routes file
- Component encapsulation
- Easy to find route logic

### 3. Inline Components

For very simple components, create inline (single file).

**Create:**
```bash
php artisan make:livewire counter --inline
```

**Result (app/Livewire/Counter.php):**
```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <h1>{{ $count }}</h1>
            <button wire:click="increment">+</button>
        </div>
        HTML;
    }
}
```

**When to use:**
- Very small components (< 20 lines of HTML)
- Simple utilities
- Quick prototypes

**When NOT to use:**
- Complex HTML
- Need Tailwind CSS with many classes (hard to read)
- Need syntax highlighting

### 4. Nested Components

Components can contain other components!

**Parent Component (PostList):**
```php
class PostList extends Component
{
    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::all()
        ]);
    }
}
```

**Parent View:**
```html
<div>
    <h1>All Posts</h1>

    @foreach($posts as $post)
        <livewire:post-card :post="$post" :key="$post->id" />
    @endforeach
</div>
```

**Child Component (PostCard):**
```php
class PostCard extends Component
{
    public $post;

    public function mount($post)
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.post-card');
    }
}
```

**Important**: Always use `:key` when rendering components in loops!

```html
<!-- ✅ GOOD -->
@foreach($posts as $post)
    <livewire:post-card :post="$post" :key="$post->id" />
@endforeach

<!-- ❌ BAD - Will cause weird behavior -->
@foreach($posts as $post)
    <livewire:post-card :post="$post" />
@endforeach
```

---

## Passing Data to Components

### Method 1: Via Attributes (Recommended)

**In Blade:**
```html
<livewire:show-post :post-id="$postId" />

<!-- Or multiple parameters -->
<livewire:user-profile
    :user="$user"
    :show-email="true"
    status="active"
/>
```

**In Component:**
```php
class UserProfile extends Component
{
    public $user;
    public $showEmail;
    public $status;

    // Livewire automatically assigns these from attributes

    public function render()
    {
        return view('livewire.user-profile');
    }
}
```

**Rules:**
- `:param="$value"` - Pass dynamic PHP value (kebab-case becomes camelCase)
- `param="value"` - Pass static string

### Method 2: Via Mount Method

More control over initialization:

```php
class ShowPost extends Component
{
    public $post;
    public $viewCount = 0;

    public function mount(Post $post)
    {
        // Can use route model binding!
        $this->post = $post;

        // Can do initialization logic
        $this->viewCount = $post->views()->count();

        // Can authorize
        $this->authorize('view', $post);
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

**Usage:**
```html
<livewire:show-post :post="$post" />
```

### Method 3: Via Route Parameters

For full-page components:

**Route:**
```php
Route::get('/posts/{post}', ShowPost::class);
```

**Component:**
```php
class ShowPost extends Component
{
    public Post $post; // Type-hinted = route model binding!

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

Laravel automatically resolves the `Post` model from the route!

---

## Component Organization

As your app grows, organize components logically:

```
app/Livewire/
├── Auth/
│   ├── Login.php
│   ├── Register.php
│   └── ResetPassword.php
├── Posts/
│   ├── Index.php
│   ├── Create.php
│   ├── Edit.php
│   ├── Show.php
│   └── PostCard.php (reusable card)
├── Users/
│   ├── Profile.php
│   └── Settings.php
├── Admin/
│   ├── Dashboard.php
│   └── Users/
│       ├── Index.php
│       └── Edit.php
├── SearchBar.php (global component)
└── NotificationBell.php (global component)
```

**Matching views structure:**
```
resources/views/livewire/
├── auth/
│   ├── login.blade.php
│   ├── register.blade.php
│   └── reset-password.blade.php
├── posts/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── show.blade.php
│   └── post-card.blade.php
└── (etc...)
```

---

## Component Layouts

### Default Layout

Set in `config/livewire.php`:

```php
'layout' => 'layouts.app',
```

All full-page components use this by default.

### Custom Layout per Component

**Using Attributes (Laravel 10+):**

```php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.guest')]
#[Title('Login')]
class Login extends Component
{
    public function render()
    {
        return view('livewire.auth.login');
    }
}
```

**Using Method:**

```php
class Login extends Component
{
    public function render()
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest')
            ->title('Login');
    }
}
```

### Layout Slots

Your layout must have a `$slot`:

```html
<!-- layouts/app.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <!-- Navigation -->
    <nav>...</nav>

    <!-- Main content (component renders here) -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer>...</footer>

    @livewireScripts
</body>
</html>
```

---

## Component Lifecycle

Understanding when methods are called:

```php
class MyComponent extends Component
{
    public $name;

    // 1. Component instantiated
    public function __construct()
    {
        // Rarely used, prefer mount()
    }

    // 2. Component mounted (initial load)
    public function mount($name)
    {
        // Initialize properties
        // Run once per component lifecycle
        $this->name = $name;
    }

    // 3. Before rendering
    public function hydrate()
    {
        // Runs before every request (even AJAX updates)
        // Good for setting up listeners, etc.
    }

    // 4. Before specific property updates
    public function updatingName($value)
    {
        // Runs before $name is updated
        // Good for formatting/validation
    }

    // 5. After specific property updates
    public function updatedName($value)
    {
        // Runs after $name is updated
        // Good for derived updates
    }

    // 6. Render
    public function render()
    {
        // Runs on every request
        return view('livewire.my-component');
    }

    // 7. After rendering
    public function dehydrate()
    {
        // Runs after render
        // Clean up, log, etc.
    }
}
```

**Common hooks:**

**mount()** - Initialize component state
```php
public function mount($userId)
{
    $this->user = User::findOrFail($userId);
}
```

**updated()** - React to ANY property change
```php
public function updated($property)
{
    $this->validateOnly($property);
}
```

**updatedPropertyName()** - React to specific property
```php
public function updatedSearch()
{
    $this->resetPage(); // Reset pagination when search changes
}
```

---

## Component Rendering Options

### 1. Return View with Data

```php
public function render()
{
    return view('livewire.posts.index', [
        'posts' => Post::latest()->paginate(10),
        'categories' => Category::all()
    ]);
}
```

### 2. Query in View (if simple)

```php
public function render()
{
    return view('livewire.posts.index');
}
```

```html
<!-- In view -->
@foreach(App\Models\Post::latest()->get() as $post)
    ...
@endforeach
```

**Use option 1** if:
- Complex queries
- Need to reuse query
- Query has logic/conditions

**Use option 2** if:
- Very simple query
- One-off usage
- Rapid prototyping

### 3. Computed Properties

For values used multiple times:

```php
class ShowPost extends Component
{
    public $postId;

    // Computed property (cached per request)
    public function getPostProperty()
    {
        return Post::with('comments', 'author')
            ->findOrFail($this->postId);
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

**In view, access as property (no parentheses):**
```html
<h1>{{ $this->post->title }}</h1>

@foreach($this->post->comments as $comment)
    ...
@endforeach
```

**Benefits:**
- Cached per request (not re-queried)
- Cleaner views
- Lazy-loaded (only when accessed)

---

## Quick Quiz

**Question 1**: What's wrong with this component view?

```html
<h1>Title</h1>
<div>Content</div>
```

<details>
<summary>Show Answer</summary>

Missing single root element. Should be:
```html
<div>
    <h1>Title</h1>
    <div>Content</div>
</div>
```

</details>

**Question 2**: How do you create a nested component?

```bash
# Create: app/Livewire/Admin/Users/Index.php
```

<details>
<summary>Show Answer</summary>

```bash
php artisan make:livewire admin.users.index
# Or
php artisan make:livewire admin/users/index
```

</details>

**Question 3**: What's the difference?

```html
<livewire:my-component param="value" />
<livewire:my-component :param="$value" />
```

<details>
<summary>Show Answer</summary>

- `param="value"` - Passes the string "value"
- `:param="$value"` - Passes the PHP variable $value (dynamic)

</details>

---

## Practice Exercise

Create a **UserCard** component that displays user information:

**Requirements:**
1. Create `UserCard` component
2. Accept a `User` model
3. Display: name, email, join date
4. Add "View Profile" button
5. Use in a loop to display multiple users

**Steps:**
1. Create migration and model if needed
2. `php artisan make:livewire UserCard`
3. Implement component
4. Create test page showing multiple cards

Try it yourself!

<details>
<summary>Show Solution</summary>

**Component (app/Livewire/UserCard.php):**
```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;

class UserCard extends Component
{
    public User $user;

    public function render()
    {
        return view('livewire.user-card');
    }
}
```

**View (resources/views/livewire/user-card.blade.php):**
```html
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex items-center space-x-4">
        <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                {{ substr($user->name, 0, 1) }}
            </div>
        </div>
        <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
            <p class="text-xs text-gray-400">Joined {{ $user->created_at->format('M d, Y') }}</p>
        </div>
    </div>
    <div class="mt-4">
        <a
            href="{{ route('users.show', $user) }}"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
            View Profile →
        </a>
    </div>
</div>
```

**Usage (in any view):**
```html
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($users as $user)
        <livewire:user-card :user="$user" :key="$user->id" />
    @endforeach
</div>
```

</details>

---

## Summary

You learned:

- ✅ Different ways to create components
- ✅ Naming conventions and organization
- ✅ Component types (standard, full-page, inline, nested)
- ✅ Passing data to components
- ✅ Component lifecycle hooks
- ✅ Layouts and rendering options

**Next Lesson**: Properties & Data Binding - master reactive properties and two-way binding!

---

**Your component toolkit is growing! Let's make them reactive next...**
