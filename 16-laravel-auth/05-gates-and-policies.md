# Lesson 05 - Gates and Policies for Authorization

**Duration**: 90 minutes
**Objectives**: Understand authorization vs authentication, define Gates for simple checks, create Policies for model-based permissions

---

## Authentication vs Authorization

Before we dive in, let's clarify an important distinction:

### Authentication (Who are you?)

**Authentication** answers the question: "Are you logged in?"

- Login/logout
- Session management
- Password verification
- "Remember me"

**This is what we've covered so far in this module!**

### Authorization (What can you do?)

**Authorization** answers the question: "Are you allowed to do this?"

- Can this user edit this post?
- Can this user delete this comment?
- Can this user view the admin panel?
- Can this user approve this order?

**This is what we're covering today!**

### Real-World Example

```php
// Authentication
if (Auth::check()) {
    echo "You're logged in!";
}

// Authorization
if (Auth::user()->can('edit', $post)) {
    echo "You can edit this post!";
}
```

**Everyone can log in (authentication), but only some users can edit posts (authorization).**

---

## Laravel's Authorization System

Laravel provides two main ways to define authorization logic:

1. **Gates** - Simple closures for ability checks
2. **Policies** - Classes that organize authorization logic around models

Think of it this way:
- **Gates** = Simple "Can I do X?" checks
- **Policies** = "Can I do X to this specific Y?" checks

---

## Gates: Simple Authorization Checks

Gates are closures that determine if a user is authorized to perform an action.

### Defining Gates

Gates are defined in `App\Providers\AppServiceProvider` (or a dedicated `AuthServiceProvider` if you prefer):

```php
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Define a simple gate
        Gate::define('view-admin-panel', function (User $user) {
            return $user->is_admin;
        });

        // Define a gate with additional logic
        Gate::define('create-post', function (User $user) {
            return $user->email_verified_at !== null
                && !$user->is_banned;
        });

        // Define a gate without a user (for guests)
        Gate::define('view-homepage', function (?User $user) {
            return true; // Anyone can view the homepage
        });
    }
}
```

### Checking Gates

**In Controllers:**

```php
use Illuminate\Support\Facades\Gate;

class AdminController extends Controller
{
    public function index()
    {
        if (Gate::allows('view-admin-panel')) {
            return view('admin.dashboard');
        }

        abort(403, 'Unauthorized');
    }
}
```

Or use `Gate::denies()`:

```php
if (Gate::denies('view-admin-panel')) {
    abort(403, 'Unauthorized');
}
```

**Using the `authorize` helper:**

```php
public function index()
{
    Gate::authorize('view-admin-panel');

    // If user is not authorized, this throws AuthorizationException
    // Laravel automatically returns a 403 response

    return view('admin.dashboard');
}
```

**In Blade Templates:**

```blade
@can('view-admin-panel')
    <a href="/admin">Admin Panel</a>
@endcan

@cannot('create-post')
    <p>You need to verify your email to create posts.</p>
@endcannot
```

**In Middleware:**

```php
Route::middleware('can:view-admin-panel')->group(function () {
    Route::get('/admin', [AdminController::class, 'index']);
});
```

**On the User object:**

```php
if (auth()->user()->can('view-admin-panel')) {
    // User is authorized
}

if (auth()->user()->cannot('create-post')) {
    // User is NOT authorized
}
```

### Gates with Parameters

Sometimes you need to pass additional data to a gate:

```php
Gate::define('update-post', function (User $user, Post $post) {
    return $user->id === $post->user_id;
});
```

**Checking:**

```php
$post = Post::find(1);

if (Gate::allows('update-post', $post)) {
    // User can update this specific post
}

// Or
if (auth()->user()->can('update-post', $post)) {
    // Same thing
}
```

### Before and After Hooks

You can define checks that run before or after all gates:

```php
// Super admin bypasses all gates
Gate::before(function (User $user, string $ability) {
    if ($user->is_super_admin) {
        return true; // Grant all abilities
    }
});

// Log all authorization checks
Gate::after(function (User $user, string $ability, bool $result, mixed $arguments) {
    Log::info("User {$user->id} attempted {$ability}: " . ($result ? 'allowed' : 'denied'));
});
```

---

## Policies: Model-Based Authorization

While Gates are great for simple checks, **Policies** are better when you have many authorization rules for a specific model (like Post, Comment, Order, etc.).

### Creating a Policy

Let's create a policy for `Post` authorization:

```bash
php artisan make:policy PostPolicy --model=Post
```

This creates `app/Policies/PostPolicy.php`:

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Anyone can view published posts
        if ($post->is_published) {
            return true;
        }

        // Only the author can view unpublished posts
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only verified users can create posts
        return $user->email_verified_at !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Only the author can update the post
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Author can delete their own post
        // Admins can delete any post
        return $user->id === $post->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->is_admin;
    }
}
```

### Registering Policies

Laravel 11 automatically discovers policies! Just make sure:
- Policy is named `{ModelName}Policy`
- Policy is in `app/Policies/` directory

**Manual registration (if needed):**

In `App\Providers\AppServiceProvider`:

```php
use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Post::class, PostPolicy::class);
}
```

### Using Policies

**In Controllers:**

```php
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

**Using `authorize()` vs manual checks:**

```php
// Option 1: authorize() method (recommended)
$this->authorize('update', $post);

// Option 2: Gate facade
if (Gate::denies('update', $post)) {
    abort(403);
}

// Option 3: User method
if (auth()->user()->cannot('update', $post)) {
    abort(403);
}
```

**In Blade Templates:**

```blade
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
```

**In Routes:**

```php
Route::middleware('auth')->group(function () {
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])
        ->can('update', 'post'); // 'post' refers to route parameter

    Route::put('/posts/{post}', [PostController::class, 'update'])
        ->can('update', 'post');
});
```

---

## Policy Methods Naming Convention

Laravel has standard method names for common actions:

| Method | Purpose | Example |
|--------|---------|---------|
| `viewAny` | Can view a list of models? | Can see all posts |
| `view` | Can view a specific model? | Can see this post |
| `create` | Can create new models? | Can create posts |
| `update` | Can update a model? | Can edit this post |
| `delete` | Can delete a model? | Can delete this post |
| `restore` | Can restore a soft-deleted model? | Can restore this post |
| `forceDelete` | Can permanently delete? | Can permanently delete this post |

**These names are conventions, not requirements.** You can add your own methods:

```php
public function publish(User $user, Post $post): bool
{
    return $user->id === $post->user_id && $user->can_publish;
}

public function pin(User $user, Post $post): bool
{
    return $user->is_admin;
}
```

**Usage:**

```php
$this->authorize('publish', $post);
$this->authorize('pin', $post);
```

---

## Guest Users and Policies

By default, policies require a user. But sometimes you want to allow guest access.

### Making User Optional

```php
public function view(?User $user, Post $post): bool
{
    // Guests can view published posts
    if ($post->is_published) {
        return true;
    }

    // Only authors can view unpublished posts
    return $user && $user->id === $post->user_id;
}
```

Notice the `?User` type hint - this allows `$user` to be `null`.

---

## Authorizing Resource Controllers

Laravel provides a convenient way to authorize all resource controller methods at once:

```php
class PostController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Post::class, 'post');
    }
}
```

This automatically maps controller methods to policy methods:

| Controller Method | Policy Method |
|-------------------|---------------|
| `index` | `viewAny` |
| `show` | `view` |
| `create` | `create` |
| `store` | `create` |
| `edit` | `update` |
| `update` | `update` |
| `destroy` | `delete` |

**No need to call `$this->authorize()` in each method!** It's done automatically.

---

## Policy Responses

Sometimes you want to return a custom error message instead of a generic 403.

```php
use Illuminate\Auth\Access\Response;

public function update(User $user, Post $post): Response
{
    if ($user->id === $post->user_id) {
        return Response::allow();
    }

    return Response::deny('You do not own this post.');
}
```

Or with HTTP status codes:

```php
public function delete(User $user, Post $post): Response
{
    if ($post->is_locked) {
        return Response::deny('This post is locked and cannot be deleted.', 423);
    }

    return $user->id === $post->user_id
        ? Response::allow()
        : Response::deny('You do not own this post.', 403);
}
```

**Displaying in Blade:**

```blade
@cannot('delete', $post)
    <p>{{ $message }}</p>
@endcannot
```

---

## Complex Authorization Logic

Let's look at some real-world examples.

### Example 1: Comment Policy

```php
<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function update(User $user, Comment $comment): bool
    {
        // Author can edit within 15 minutes
        if ($user->id === $comment->user_id) {
            return $comment->created_at->gt(now()->subMinutes(15));
        }

        // Admins can always edit
        return $user->is_admin;
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Author or post owner or admin can delete
        return $user->id === $comment->user_id
            || $user->id === $comment->post->user_id
            || $user->is_admin;
    }
}
```

### Example 2: Team-Based Authorization

```php
public function view(User $user, Project $project): bool
{
    // Check if user belongs to the project's team
    return $project->team->users->contains($user);
}

public function update(User $user, Project $project): bool
{
    // Only team admins can update
    return $project->team->users()
        ->wherePivot('role', 'admin')
        ->wherePivot('user_id', $user->id)
        ->exists();
}
```

### Example 3: Subscription-Based Authorization

```php
public function create(User $user): bool
{
    // Free users can create up to 5 posts
    if (!$user->is_premium) {
        return $user->posts()->count() < 5;
    }

    // Premium users have unlimited posts
    return true;
}
```

---

## Comparing to Module 07

In Module 07, you probably had manual checks scattered throughout your code:

```php
// Module 07 approach
$post = getPostById($pdo, $postId);

if ($_SESSION['user_id'] !== $post['user_id']) {
    header('Location: unauthorized.php');
    exit;
}

// Show edit form
```

**Problems:**
- Logic is duplicated everywhere
- Hard to maintain
- Easy to forget checks
- No centralized authorization rules

**Laravel approach:**

```php
// Define once in PostPolicy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}

// Use everywhere
$this->authorize('update', $post);
```

**Benefits:**
- Single source of truth
- Reusable
- Consistent
- Easy to test

---

## Hands-On: Building a Complete Authorization System

Let's build a complete blog with authorization.

### Step 1: Create Post Model and Migration

```bash
php artisan make:model Post -m
```

**Migration:**

```php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->text('content');
        $table->boolean('is_published')->default(false);
        $table->timestamps();
    });
}
```

```bash
php artisan migrate
```

**Model:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Step 2: Create PostPolicy

```bash
php artisan make:policy PostPolicy --model=Post
```

**Implementation:**

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can see list of published posts
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
        return $user->email_verified_at !== null;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->is_admin;
    }

    public function publish(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

### Step 3: Create PostController

```bash
php artisan make:controller PostController --resource
```

**Implementation:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

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
            ->with('user')
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $post = auth()->user()->posts()->create($validated);

        return redirect()->route('posts.show', $post);
    }

    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $post->update($validated);

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

### Step 4: Create Routes

```php
Route::resource('posts', PostController::class);
Route::post('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
```

### Step 5: Create Views

**posts/show.blade.php:**

```blade
<x-app-layout>
    <h1>{{ $post->title }}</h1>
    <p>By {{ $post->user->name }}</p>
    <p>{{ $post->content }}</p>

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

    @can('publish', $post)
        @if(!$post->is_published)
            <form method="POST" action="{{ route('posts.publish', $post) }}">
                @csrf
                <button type="submit">Publish</button>
            </form>
        @endif
    @endcan
</x-app-layout>
```

---

## Quick Quiz

Test your understanding:

1. **What's the difference between authentication and authorization?**
   - Authentication = Who are you? (Login)
   - Authorization = What can you do? (Permissions)

2. **What are Gates used for?**
   - Simple ability checks not tied to a specific model
   - Example: Can user view admin panel?

3. **What are Policies used for?**
   - Authorization logic for specific models
   - Example: Can user update THIS post?

4. **How do you create a Policy?**
   - `php artisan make:policy PostPolicy --model=Post`

5. **How do you check authorization in a controller?**
   - `$this->authorize('update', $post)`
   - `Gate::allows('update', $post)`
   - `auth()->user()->can('update', $post)`

6. **How do you check authorization in a Blade template?**
   - `@can('update', $post)`

---

## What's Next?

In **Lesson 06 - Authorization Checks**, we'll explore:
- Authorization in different parts of your app
- Form request authorization
- API authorization
- Testing authorization
- Best practices and patterns

---

## Key Takeaways

1. **Authorization is separate from authentication:**
   - Authentication = Are you logged in?
   - Authorization = Can you do this?

2. **Gates are for simple checks:**
   - Not tied to a specific model
   - Defined in `AppServiceProvider`
   - Example: `Gate::define('view-admin', ...)`

3. **Policies are for model-based checks:**
   - Organized by model
   - Standard methods: `view`, `create`, `update`, `delete`
   - Automatically discovered by Laravel

4. **Multiple ways to check authorization:**
   - `$this->authorize()`
   - `Gate::allows()`
   - `auth()->user()->can()`
   - `@can()` in Blade

5. **Use `authorizeResource()` for resource controllers:**
   - Automatically authorizes all controller methods
   - Maps to policy methods

6. **Policies can return custom messages:**
   - `Response::allow()`
   - `Response::deny('message')`

You now understand Laravel's complete authorization system! Next, we'll practice using it throughout your application.
