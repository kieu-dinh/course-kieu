# Lesson 10: Best Practices and Performance

**Duration**: 60 minutes
**Prerequisites**: All previous lessons completed

---

## What You'll Learn

- Component organization
- Performance optimization
- Security best practices
- Testing Livewire components
- Debugging techniques
- Common pitfalls to avoid
- Production deployment

---

## Component Organization

### File Structure

Organize components logically:

```
app/Livewire/
├── Admin/
│   ├── Dashboard.php
│   ├── Users/
│   │   ├── Index.php
│   │   ├── Create.php
│   │   └── Edit.php
│   └── Settings.php
├── Auth/
│   ├── Login.php
│   ├── Register.php
│   └── ForgotPassword.php
├── Blog/
│   ├── PostList.php
│   ├── PostShow.php
│   └── CommentForm.php
├── Shop/
│   ├── ProductCatalog.php
│   ├── ProductDetail.php
│   └── Cart.php
└── Shared/
    ├── SearchBar.php
    ├── NotificationBell.php
    └── UserMenu.php
```

### Naming Conventions

**Be descriptive and consistent:**

```bash
# CRUD operations
php artisan make:livewire posts.index
php artisan make:livewire posts.create
php artisan make:livewire posts.edit
php artisan make:livewire posts.show

# Features
php artisan make:livewire search-users
php artisan make:livewire upload-avatar
php artisan make:livewire subscribe-newsletter

# Nested features
php artisan make:livewire admin.users.manage-permissions
```

### Component Responsibilities

**Single Responsibility Principle:**

❌ **Bad** (component does too much):
```php
class UserManagement extends Component
{
    // Lists users
    // Creates users
    // Edits users
    // Deletes users
    // Manages roles
    // Sends emails
    // 500 lines of code...
}
```

✅ **Good** (separate components):
```php
class UserList extends Component { }
class CreateUser extends Component { }
class EditUser extends Component { }
class ManageUserRoles extends Component { }
```

---

## Performance Optimization

### 1. Reduce Re-Renders

Only re-render what changed:

❌ **Bad** (entire component re-renders on every keystroke):
```html
<input wire:model.live="search">
```

✅ **Good** (debounce to reduce requests):
```html
<input wire:model.live.debounce.300ms="search">
```

✅ **Better** (only update on blur):
```html
<input wire:model.blur="search">
```

### 2. Lazy Loading

Don't load component until it's needed:

```html
<!-- Component loads only when scrolled into view -->
<livewire:heavy-component lazy />
```

**Use for:**
- Components below the fold
- Tabs that aren't immediately visible
- Expensive database queries

**Example:**
```php
class Analytics extends Component
{
    public function render()
    {
        // This expensive query only runs when component is visible
        return view('livewire.analytics', [
            'stats' => $this->calculateComplexStats()
        ]);
    }

    private function calculateComplexStats()
    {
        // Expensive calculation...
        sleep(2); // Simulating slow query
        return ['revenue' => 10000, 'orders' => 500];
    }
}
```

```html
<!-- Won't slow down initial page load -->
<livewire:analytics lazy />
```

### 3. Select Only What You Need

❌ **Bad** (loads all columns):
```php
$users = User::all();
```

✅ **Good** (only needed columns):
```php
$users = User::select('id', 'name', 'email')->get();
```

### 4. Eager Loading

Avoid N+1 queries:

❌ **Bad**:
```php
$posts = Post::all(); // 1 query

// In view: $post->author->name
// This triggers N queries (one per post)!
```

✅ **Good**:
```php
$posts = Post::with('author')->get(); // 2 queries total
```

### 5. Computed Properties with Caching

Cache expensive calculations:

```php
public function getUsersProperty()
{
    // Cached for the request
    return once(function () {
        return User::with('roles', 'permissions')
            ->where('active', true)
            ->get();
    });
}
```

Or use Laravel's cache:

```php
public function getStatsProperty()
{
    return Cache::remember('dashboard-stats', 3600, function () {
        return [
            'revenue' => Order::sum('total'),
            'customers' => Customer::count(),
            'products' => Product::count(),
        ];
    });
}
```

### 6. Pagination

Always paginate large datasets:

❌ **Bad**:
```php
$products = Product::all(); // Could be 100,000 records!
```

✅ **Good**:
```php
$products = Product::paginate(20);
```

### 7. Avoid Reactive Properties for Large Data

Don't make large arrays public properties:

❌ **Bad**:
```php
public $products; // All product data sent to browser on every request

public function mount()
{
    $this->products = Product::all()->toArray();
}
```

✅ **Good**:
```php
// Render fresh each time (doesn't store in component state)
public function render()
{
    return view('livewire.products', [
        'products' => Product::paginate(20)
    ]);
}
```

### 8. Defer Loading

Show placeholder while loading:

```html
<livewire:analytics lazy>
    <div>
        <!-- Placeholder while loading -->
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2"></div>
        </div>
    </div>
</livewire:analytics>
```

### 9. Optimize Polling

❌ **Bad** (polls every 2s, very expensive):
```html
<div wire:poll.2s>
    {{ $expensiveData }}
</div>
```

✅ **Good** (poll only when needed):
```html
@if($order->status === 'processing')
    <div wire:poll.5s="checkStatus">
        Processing...
    </div>
@else
    <div>Complete!</div>
@endif
```

---

## Security Best Practices

### 1. Validate Everything

Never trust client input:

```php
public function save()
{
    // ALWAYS validate
    $validated = $this->validate([
        'title' => 'required|min:3|max:255',
        'content' => 'required',
    ]);

    Post::create($validated);
}
```

### 2. Authorize Actions

```php
public function delete($postId)
{
    $post = Post::findOrFail($postId);

    // Check authorization
    $this->authorize('delete', $post);

    $post->delete();
}
```

Or use policies in component:

```php
public function mount($postId)
{
    $this->post = Post::findOrFail($postId);
    $this->authorize('view', $this->post);
}
```

### 3. Lock Sensitive Properties

Prevent tampering:

```php
use Livewire\Attributes\Locked;

class Checkout extends Component
{
    #[Locked]
    public $totalPrice; // Can't be modified from frontend

    #[Locked]
    public $userId; // Can't be modified from frontend

    public $shippingAddress; // Can be modified
}
```

**Use for:**
- Prices
- User IDs
- Order totals
- Anything security-sensitive

### 4. Sanitize Output

Prevent XSS attacks:

```html
<!-- ❌ Dangerous (XSS vulnerability) -->
{!! $userInput !!}

<!-- ✅ Safe (escaped) -->
{{ $userInput }}

<!-- ✅ Safe for HTML content -->
{!! Str::markdown($content) !!}
```

### 5. Rate Limiting

Protect against abuse:

```php
use Illuminate\Support\Facades\RateLimiter;

public function sendMessage()
{
    $key = 'send-message:' . auth()->id();

    if (RateLimiter::tooManyAttempts($key, 10)) {
        $this->addError('message', 'Too many messages. Please wait.');
        return;
    }

    RateLimiter::hit($key, 60); // 10 attempts per minute

    Message::create([...]);
}
```

### 6. CSRF Protection

Livewire handles CSRF automatically, but ensure it's enabled:

```php
// In app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    // Don't add Livewire routes here!
];
```

---

## Testing Livewire Components

### Basic Test

```php
<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use Livewire\Livewire;
use App\Livewire\CreatePost;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreatePostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_post()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'My First Post')
            ->set('content', 'This is the content')
            ->call('save')
            ->assertRedirect('/posts');

        $this->assertDatabaseHas('posts', [
            'title' => 'My First Post'
        ]);
    }

    /** @test */
    public function title_is_required()
    {
        Livewire::test(CreatePost::class)
            ->set('title', '')
            ->set('content', 'Content')
            ->call('save')
            ->assertHasErrors(['title' => 'required']);
    }

    /** @test */
    public function title_must_be_at_least_3_characters()
    {
        Livewire::test(CreatePost::class)
            ->set('title', 'ab')
            ->call('save')
            ->assertHasErrors(['title' => 'min']);
    }
}
```

### Testing Authentication

```php
/** @test */
public function only_authenticated_users_can_create_posts()
{
    Livewire::test(CreatePost::class)
        ->call('save')
        ->assertForbidden();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(CreatePost::class)
        ->set('title', 'My Post')
        ->set('content', 'Content')
        ->call('save')
        ->assertRedirect();
}
```

### Testing Events

```php
/** @test */
public function dispatches_event_after_creating_post()
{
    Livewire::test(CreatePost::class)
        ->set('title', 'My Post')
        ->set('content', 'Content')
        ->call('save')
        ->assertDispatched('post-created');
}
```

### Testing File Uploads

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** @test */
public function can_upload_image()
{
    Storage::fake('public');

    $file = UploadedFile::fake()->image('photo.jpg');

    Livewire::test(CreatePost::class)
        ->set('image', $file)
        ->call('save');

    Storage::disk('public')->assertExists('posts/' . $file->hashName());
}
```

---

## Debugging Techniques

### 1. dd() and dump()

```php
public function save()
{
    dd($this->all()); // Dump all properties and die

    dump($this->title); // Dump without stopping
}
```

### 2. Livewire DevTools

Install browser extension:
- [Chrome](https://chrome.google.com/webstore/detail/livewire-devtools)
- [Firefox](https://addons.mozilla.org/en-US/firefox/addon/livewire-devtools/)

Shows:
- Component tree
- Properties
- Requests
- Events

### 3. Log Requests

```php
public function updated($property, $value)
{
    \Log::info("Property updated: $property = $value");
}
```

### 4. Browser Console

Check for JavaScript errors:
- Open DevTools (F12)
- Check Console tab
- Look for Livewire errors

### 5. Debug Bar

Install Laravel Debugbar:

```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows:
- Queries
- Request time
- Memory usage
- Livewire updates

---

## Common Pitfalls

### 1. Public Properties in Loops

❌ **Bad** (missing :key):
```html
@foreach($posts as $post)
    <livewire:post-card :post="$post" />
@endforeach
```

✅ **Good**:
```html
@foreach($posts as $post)
    <livewire:post-card :post="$post" :key="$post->id" />
@endforeach
```

### 2. Storing Models as Properties

❌ **Bad** (entire model serialized):
```php
public $user; // Entire User model sent to browser!
```

✅ **Good** (only ID):
```php
public $userId;

public function getUserProperty()
{
    return User::find($this->userId);
}
```

### 3. Forgetting wire:key in Loops

Always use `:key` when rendering components in loops!

### 4. Using $this in Alpine

❌ **Bad**:
```html
<div x-data="{ count: $this.count }">
```

✅ **Good**:
```html
<div x-data="{ count: $wire.count }">
```

### 5. Calling Methods in View

❌ **Bad** (method called on every render):
```html
{{ $this->expensiveCalculation() }}
```

✅ **Good** (use computed property):
```php
public function getResultProperty()
{
    return $this->expensiveCalculation();
}
```

```html
{{ $this->result }}
```

---

## Production Deployment

### 1. Optimize Autoloader

```bash
composer install --optimize-autoloader --no-dev
```

### 2. Cache Configuration

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Queue Time-Consuming Tasks

Don't block Livewire requests:

```php
public function sendEmail()
{
    // ❌ Bad (blocks request)
    Mail::to($user)->send(new Welcome());

    // ✅ Good (queued)
    Mail::to($user)->queue(new Welcome());
}
```

### 4. Use CDN for Assets

In production, serve Livewire assets from CDN:

```html
<!-- Instead of -->
@livewireScripts

<!-- Use -->
<script src="https://cdn.jsdelivr.net/npm/livewire@3.x.x/dist/livewire.min.js"></script>
```

### 5. Enable Asset Versioning

```php
// config/livewire.php
'asset_url' => env('APP_URL'),
'manifest_path' => null,
```

### 6. Monitor Performance

Use tools like:
- Laravel Telescope (development)
- New Relic (production)
- Blackfire (profiling)

---

## Performance Checklist

Before deploying:

- [ ] Paginate large datasets
- [ ] Eager load relationships
- [ ] Select only needed columns
- [ ] Cache expensive queries
- [ ] Use lazy loading for below-fold components
- [ ] Debounce live searches
- [ ] Avoid storing large arrays in properties
- [ ] Use computed properties for derived data
- [ ] Enable query caching
- [ ] Optimize images
- [ ] Use asset versioning
- [ ] Enable OPcache
- [ ] Queue background jobs

---

## Security Checklist

Before deploying:

- [ ] Validate all inputs
- [ ] Authorize all actions
- [ ] Lock sensitive properties
- [ ] Sanitize output
- [ ] Rate limit public endpoints
- [ ] Use HTTPS
- [ ] Keep Livewire updated
- [ ] Review error messages (don't leak sensitive info)
- [ ] Test file upload limits
- [ ] Implement CORS if needed

---

## Code Quality Checklist

- [ ] Follow PSR-12 coding standards
- [ ] Write tests for critical paths
- [ ] Document complex logic
- [ ] Use type hints
- [ ] Keep components focused (SRP)
- [ ] Consistent naming conventions
- [ ] Remove dead code
- [ ] Handle errors gracefully
- [ ] Add loading states
- [ ] Provide user feedback

---

## Quick Reference Card

### Common Commands

```bash
# Create component
php artisan make:livewire ComponentName

# Create inline component
php artisan make:livewire ComponentName --inline

# Create form object
php artisan make:form FormName

# Clear Livewire temp files
php artisan livewire:delete-uploads

# Publish config
php artisan livewire:publish --config
```

### Common Patterns

```php
// Validation
$this->validate([...]);
$this->validateOnly('field');

// Reset
$this->reset();
$this->reset('field');
$this->resetExcept('field');

// Pagination
$this->resetPage();
$this->nextPage();
$this->setPage(5);

// Events
$this->dispatch('event-name');
$this->dispatch('event')->to(Component::class);
$this->dispatch('event')->toJs();

// Redirect
return redirect()->route('posts.index');
```

---

## Resources

### Official Documentation
- [Livewire Docs](https://livewire.laravel.com)
- [Laravel Docs](https://laravel.com/docs)
- [Alpine.js Docs](https://alpinejs.dev)
- [Tailwind CSS Docs](https://tailwindcss.com)

### Learning Resources
- [Laracasts Livewire Series](https://laracasts.com/topics/livewire)
- [Livewire Screencasts](https://laravel-livewire.com/screencasts)
- [TALL Stack Tutorial](https://tallstack.dev)

### Community
- [Livewire Discord](https://discord.gg/livewire)
- [Laravel Discord](https://discord.gg/laravel)
- [Laracasts Forum](https://laracasts.com/discuss)

---

## Final Project Ideas

Now that you've mastered Livewire, build something real!

**Beginner:**
1. Todo App with categories and priorities
2. Contact Manager with search and filters
3. Blog with comments and likes

**Intermediate:**
4. E-commerce store (catalog, cart, checkout)
5. Project Management Tool (tasks, teams, deadlines)
6. Social Media Feed (posts, comments, reactions)

**Advanced:**
7. Real-time Chat Application
8. Collaborative Document Editor
9. Live Analytics Dashboard
10. Multi-tenant SaaS Application

---

## Summary

You mastered:

- ✅ Component organization and structure
- ✅ Performance optimization techniques
- ✅ Security best practices
- ✅ Testing Livewire components
- ✅ Debugging techniques
- ✅ Common pitfalls to avoid
- ✅ Production deployment

---

## Congratulations!

**You've completed the Laravel Livewire module!**

You now know how to build:
- Reactive components
- Real-time features
- Complex forms
- File uploads
- Pagination
- Live search
- Shopping carts
- Chat applications
- And much more!

**The TALL Stack is Complete:**
- ✅ Tailwind CSS (Module 02)
- ✅ Alpine.js (Module 13)
- ✅ Laravel (Modules 14-16)
- ✅ Livewire (Module 17)

**Next:** Module 18 - APIs & Testing

You're ready to build modern, professional web applications!

**Keep building, keep learning!**
