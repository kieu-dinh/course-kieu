# Lesson 06 - Authorization Checks Throughout Your Application

**Duration**: 75 minutes
**Objectives**: Learn to implement authorization checks in controllers, views, routes, Form Requests, and APIs. Master best practices for secure applications.

---

## Overview

In Lesson 05, you learned about Gates and Policies. Now let's explore how to use them effectively throughout your entire application.

We'll cover:
1. Authorization in controllers (multiple approaches)
2. Authorization in Blade templates
3. Authorization in routes
4. Authorization in Form Requests
5. Authorization in API controllers
6. Best practices and patterns
7. Common pitfalls

---

## 1. Authorization in Controllers

There are several ways to check authorization in controllers. Let's explore them all.

### Method 1: `authorize()` Method (Recommended)

The cleanest approach is using the `authorize()` method:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index');
    }
}
```

**Benefits:**
- Clean and readable
- Automatically throws `AuthorizationException` (403 response)
- Works with both Gates and Policies

### Method 2: `Gate` Facade

More explicit, useful when you need custom logic:

```php
use Illuminate\Support\Facades\Gate;

public function edit(Post $post)
{
    if (Gate::denies('update', $post)) {
        abort(403, 'You are not authorized to edit this post.');
    }

    return view('posts.edit', compact('post'));
}

// Or with allows()
public function edit(Post $post)
{
    if (Gate::allows('update', $post)) {
        return view('posts.edit', compact('post'));
    }

    return redirect()->route('posts.index')
        ->with('error', 'You cannot edit this post.');
}
```

**Use when:**
- You need custom error messages
- You want to redirect instead of throwing 403
- You have complex conditional logic

### Method 3: `User` Object Methods

Check authorization directly on the user:

```php
public function edit(Post $post)
{
    if (auth()->user()->cannot('update', $post)) {
        abort(403);
    }

    return view('posts.edit', compact('post'));
}

// Or with can()
public function edit(Post $post)
{
    if (auth()->user()->can('update', $post)) {
        return view('posts.edit', compact('post'));
    }

    abort(403);
}
```

### Method 4: `authorizeResource()` (For Resource Controllers)

The most efficient for resource controllers:

```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    // No need to call authorize() in individual methods!

    public function edit(Post $post)
    {
        // Authorization already checked!
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        // Authorization already checked!
        $post->update($request->validated());
        return redirect()->route('posts.show', $post);
    }
}
```

**Automatically maps:**
- `index()` → `viewAny`
- `show()` → `view`
- `create()` → `create`
- `store()` → `create`
- `edit()` → `update`
- `update()` → `update`
- `destroy()` → `delete`

### Method 5: Authorize Multiple Abilities

Sometimes you need to check multiple conditions:

```php
public function publishAndFeature(Post $post)
{
    $this->authorize('publish', $post);
    $this->authorize('feature', $post);

    $post->update([
        'is_published' => true,
        'is_featured' => true,
    ]);

    return redirect()->route('posts.show', $post);
}
```

Or use `any()` for "at least one":

```php
use Illuminate\Support\Facades\Gate;

public function moderate(Post $post)
{
    if (Gate::any(['update', 'delete'], $post)) {
        return view('posts.moderate', compact('post'));
    }

    abort(403);
}
```

Or `check()` for "all of them":

```php
if (Gate::check(['publish', 'feature'], $post)) {
    // User can do both
}
```

### Method 6: Conditional Authorization

Sometimes authorization depends on request data:

```php
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);

    // Additional check: can't change author
    if ($request->has('user_id') && $request->user_id != $post->user_id) {
        if (auth()->user()->cannot('reassign-author', $post)) {
            abort(403, 'You cannot change the post author.');
        }
    }

    $post->update($request->validated());

    return redirect()->route('posts.show', $post);
}
```

---

## 2. Authorization in Blade Templates

Blade provides powerful directives for conditional rendering based on authorization.

### Basic `@can` Directive

```blade
<h1>{{ $post->title }}</h1>

@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}" class="btn btn-primary">
        Edit Post
    </a>
@endcan

@can('delete', $post)
    <form method="POST" action="{{ route('posts.destroy', $post) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete Post</button>
    </form>
@endcan
```

### `@cannot` Directive

```blade
@cannot('update', $post)
    <p class="text-muted">You cannot edit this post.</p>
@endcannot
```

### `@canany` Directive (At Least One)

```blade
@canany(['update', 'delete'], $post)
    <div class="post-actions">
        @can('update', $post)
            <a href="{{ route('posts.edit', $post) }}">Edit</a>
        @endcan

        @can('delete', $post)
            <form method="POST" action="{{ route('posts.destroy', $post) }}">
                @csrf
                @method('DELETE')
                <button type="submit">Delete</button>
            </form>
        @endcan
    </div>
@endcanany
```

### `@else` and `@elsecan`

```blade
@can('update', $post)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@elsecan('view', $post)
    <a href="{{ route('posts.show', $post) }}">View</a>
@else
    <p>No actions available</p>
@endcan
```

### Gates Without Models

For simple ability checks:

```blade
@can('view-admin-panel')
    <a href="{{ route('admin.dashboard') }}">Admin Panel</a>
@endcan

@can('create-post')
    <a href="{{ route('posts.create') }}">Create New Post</a>
@endcan
```

### Combining with Authentication

```blade
@auth
    @can('create-post')
        <a href="{{ route('posts.create') }}">Create Post</a>
    @endcan
@endauth

@guest
    <a href="{{ route('login') }}">Login to Create Posts</a>
@endguest
```

### Showing Different Content Based on Authorization

```blade
<div class="post">
    <h2>{{ $post->title }}</h2>

    @can('view', $post)
        <p>{{ $post->content }}</p>
    @else
        <p>This post is private. <a href="{{ route('login') }}">Login</a> to view.</p>
    @endcan
</div>
```

### Navigation Menus with Authorization

```blade
<nav>
    <a href="{{ route('home') }}">Home</a>

    @auth
        <a href="{{ route('dashboard') }}">Dashboard</a>

        @can('create-post')
            <a href="{{ route('posts.create') }}">Create Post</a>
        @endcan

        @can('view-admin-panel')
            <a href="{{ route('admin.dashboard') }}">Admin</a>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>
    @else
        <a href="{{ route('login') }}">Login</a>
        <a href="{{ route('register') }}">Register</a>
    @endauth
</nav>
```

---

## 3. Authorization in Routes

You can protect routes directly with the `can` middleware.

### Basic Route Protection

```php
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
    ->middleware('can:update,post');

Route::put('/posts/{post}', [PostController::class, 'update'])
    ->middleware('can:update,post');

Route::delete('/posts/{post}', [PostController::class, 'destroy'])
    ->middleware('can:delete,post');
```

**Note:** `'post'` refers to the route parameter name.

### Protecting Route Groups

```php
Route::middleware(['auth', 'can:create-post'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create']);
    Route::post('/posts', [PostController::class, 'store']);
});

Route::middleware(['auth', 'can:view-admin-panel'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/settings', [AdminController::class, 'settings']);
});
```

### Combining with Other Middleware

```php
Route::middleware(['auth', 'verified', 'can:update,post'])->group(function () {
    Route::get('/posts/{post}/edit', [PostController::class, 'edit']);
    Route::put('/posts/{post}', [PostController::class, 'update']);
});
```

### Route Model Binding with Authorization

```php
Route::get('/posts/{post}', [PostController::class, 'show'])
    ->middleware('can:view,post');
```

If the user can't view the post, they get a 403 BEFORE the controller runs.

---

## 4. Authorization in Form Requests

Form Requests can include authorization logic alongside validation.

### Creating a Form Request

```bash
php artisan make:request UpdatePostRequest
```

### Implementing Authorization

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $this->user()->can('update', $post);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
        ];
    }
}
```

### Using in Controller

```php
public function update(UpdatePostRequest $request, Post $post)
{
    // Authorization already checked!
    // Validation already passed!

    $post->update($request->validated());

    return redirect()->route('posts.show', $post);
}
```

### Custom Authorization Error Messages

```php
protected function failedAuthorization()
{
    throw new \Illuminate\Auth\Access\AuthorizationException(
        'You do not have permission to update this post.'
    );
}
```

### Conditional Authorization

```php
public function authorize(): bool
{
    $post = $this->route('post');

    // Check basic update permission
    if (!$this->user()->can('update', $post)) {
        return false;
    }

    // Additional check: can't publish if not verified
    if ($this->boolean('is_published') && !$this->user()->email_verified_at) {
        return false;
    }

    return true;
}
```

---

## 5. Authorization in API Controllers

API authorization works similarly, but with JSON responses.

### API Resource Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Post $post)
    {
        $this->authorize('view', $post);

        return response()->json($post);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $post->update($request->validated());

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }
}
```

### Custom JSON Error Response

By default, unauthorized API requests get a JSON response:

```json
{
    "message": "This action is unauthorized."
}
```

**Customize in `App\Exceptions\Handler`:**

```php
use Illuminate\Auth\Access\AuthorizationException;

public function render($request, Throwable $exception)
{
    if ($exception instanceof AuthorizationException && $request->expectsJson()) {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $exception->getMessage(),
        ], 403);
    }

    return parent::render($request, $exception);
}
```

### Policy Responses for APIs

```php
use Illuminate\Auth\Access\Response;

public function update(User $user, Post $post): Response
{
    if ($user->id !== $post->user_id) {
        return Response::deny('You do not own this post.', 403);
    }

    if ($post->is_locked) {
        return Response::deny('This post is locked.', 423);
    }

    return Response::allow();
}
```

---

## 6. Best Practices and Patterns

### Practice 1: Always Authorize at the Controller Level

**Don't rely on hiding UI elements!**

```blade
<!-- This is NOT enough! -->
@can('delete', $post)
    <button>Delete</button>
@endcan
```

A malicious user can still send a DELETE request directly!

**Always authorize in the controller:**

```php
public function destroy(Post $post)
{
    $this->authorize('delete', $post);

    $post->delete();

    return redirect()->route('posts.index');
}
```

### Practice 2: Use Policies for Model-Based Authorization

**Bad:**

```php
public function update(Request $request, Post $post)
{
    if (auth()->user()->id !== $post->user_id) {
        abort(403);
    }

    // ...
}
```

**Good:**

```php
// In PostPolicy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}

// In Controller
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);

    // ...
}
```

### Practice 3: Use Form Requests for Complex Authorization

```php
class PublishPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $this->user()->can('publish', $post)
            && !$post->is_published
            && $this->user()->email_verified_at;
    }

    public function rules(): array
    {
        return [
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
```

### Practice 4: Use Gates for Simple, Global Checks

```php
// AppServiceProvider
Gate::define('view-admin-panel', function (User $user) {
    return $user->is_admin;
});

Gate::define('bypass-rate-limits', function (User $user) {
    return $user->is_premium;
});
```

### Practice 5: Don't Repeat Authorization Logic

**Bad:**

```php
// In multiple controllers
if (auth()->user()->id === $post->user_id || auth()->user()->is_admin) {
    // ...
}
```

**Good:**

```php
// In PostPolicy (once)
public function delete(User $user, Post $post): bool
{
    return $user->id === $post->user_id || $user->is_admin;
}

// Use everywhere
$this->authorize('delete', $post);
```

### Practice 6: Use `authorizeResource()` for Resource Controllers

```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }

    // All methods automatically authorized!
}
```

### Practice 7: Test Your Authorization

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;

class PostAuthorizationTest extends TestCase
{
    public function test_user_can_update_own_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('update', $post));
    }

    public function test_user_cannot_update_others_post()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('update', $post));
    }

    public function test_admin_can_delete_any_post()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $post = Post::factory()->create();

        $this->assertTrue($admin->can('delete', $post));
    }
}
```

---

## 7. Common Pitfalls

### Pitfall 1: Checking Authorization Only in Views

```blade
<!-- Insecure! User can bypass by sending direct request -->
@if(auth()->user()->id === $post->user_id)
    <a href="{{ route('posts.edit', $post) }}">Edit</a>
@endif
```

**Fix:** Always authorize in the controller!

### Pitfall 2: Forgetting to Check Authorization

```php
// Dangerous! Anyone can delete any post!
public function destroy(Post $post)
{
    $post->delete();
    return redirect()->route('posts.index');
}
```

**Fix:**

```php
public function destroy(Post $post)
{
    $this->authorize('delete', $post);

    $post->delete();
    return redirect()->route('posts.index');
}
```

### Pitfall 3: Inconsistent Authorization Logic

```php
// PostController
if ($user->id === $post->user_id) { ... }

// CommentController
if ($post->user_id === $user->id) { ... } // Order is different!

// ApiPostController
if ($user->id == $post->user_id) { ... } // Loose comparison!
```

**Fix:** Use policies for consistency!

### Pitfall 4: Not Handling Guest Users

```php
// Throws error if user is not logged in!
public function view(?User $user, Post $post): bool
{
    return $user->id === $post->user_id; // Error if $user is null!
}
```

**Fix:**

```php
public function view(?User $user, Post $post): bool
{
    if ($post->is_published) {
        return true;
    }

    return $user && $user->id === $post->user_id;
}
```

---

## Real-World Example: Complete Blog Authorization

Let's put it all together with a complete example.

### PostPolicy

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super_admin) {
            return true; // Super admin can do everything
        }

        return null; // Continue to normal checks
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Post $post): bool
    {
        if ($post->is_published) {
            return true;
        }

        return $user && $user->id === $post->user_id;
    }

    public function create(User $user): bool
    {
        return $user->email_verified_at !== null
            && !$user->is_banned
            && $user->posts()->count() < $user->post_limit;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id && !$post->is_locked;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id
            && !$post->is_published
            && $user->can_publish;
    }
}
```

### PostController

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
        $this->authorizeResource(Post::class, 'post');
    }

    public function index()
    {
        $posts = Post::where('is_published', true)
            ->latest()
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function create()
    {
        return view('posts.create');
    }

    public function store(StorePostRequest $request)
    {
        $post = auth()->user()->posts()->create($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return redirect()->route('posts.show', $post);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index');
    }

    public function publish(Post $post)
    {
        $this->authorize('publish', $post);

        $post->update(['is_published' => true]);

        return redirect()->route('posts.show', $post);
    }
}
```

### Blade Template

```blade
<x-app-layout>
    <h1>{{ $post->title }}</h1>

    @can('view', $post)
        <div class="post-content">
            {{ $post->content }}
        </div>

        <div class="post-actions">
            @canany(['update', 'delete', 'publish'], $post)
                <div class="action-buttons">
                    @can('update', $post)
                        <a href="{{ route('posts.edit', $post) }}">Edit</a>
                    @endcan

                    @can('publish', $post)
                        <form method="POST" action="{{ route('posts.publish', $post) }}">
                            @csrf
                            <button type="submit">Publish</button>
                        </form>
                    @endcan

                    @can('delete', $post)
                        <form method="POST" action="{{ route('posts.destroy', $post) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Delete</button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>
    @else
        <p>This post is private.</p>
    @endcan
</x-app-layout>
```

---

## Quick Quiz

1. **Where should you ALWAYS check authorization?**
   - In the controller (never rely on hiding UI)

2. **What's the cleanest way to authorize resource controllers?**
   - `$this->authorizeResource(Post::class, 'post')`

3. **How do you check authorization in Blade?**
   - `@can('update', $post)`

4. **How do you protect routes with authorization?**
   - `->middleware('can:update,post')`

5. **Can Form Requests include authorization?**
   - Yes! Override the `authorize()` method

---

## What's Next?

In **Lesson 07 - Role-Based Access Control**, we'll build:
- Multi-role systems (Admin, Moderator, User)
- Permission management
- Role assignment
- Complex role hierarchies

---

## Key Takeaways

1. **Always authorize in controllers, not just views**
2. **Use `$this->authorize()` for simplicity**
3. **Use `authorizeResource()` for resource controllers**
4. **Check authorization in Blade with `@can`**
5. **Protect routes with `can` middleware**
6. **Form Requests can include authorization**
7. **Test your authorization logic**

You now know how to implement authorization throughout your entire Laravel application!
