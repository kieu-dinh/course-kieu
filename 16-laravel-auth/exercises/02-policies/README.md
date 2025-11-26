# 02 - Post Policies

## Objective
Implement authorization using Laravel Policies to control who can create, edit, and delete posts based on ownership.

## Prerequisites
- Completed "01-breeze" exercise
- Understanding of authentication
- Knowledge of authorization concepts

## Instructions

### Step 1: Create a Post Model and Migration
```bash
php artisan make:model Post -m
```

Edit migration:

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

Run migration:

```bash
php artisan migrate
```

### Step 2: Define Post Model
In `app/Models/Post.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Step 3: Create a Policy
Generate a policy:

```bash
php artisan make:policy PostPolicy --model=Post
```

This creates `app/Policies/PostPolicy.php` with common methods.

### Step 4: Implement Policy Methods
Edit `app/Policies/PostPolicy.php`:

```php
namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine if the user can view any posts
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can view posts list
    }

    /**
     * Determine if the user can view the post
     */
    public function view(User $user, Post $post): bool
    {
        return true; // Everyone can view individual posts
    }

    /**
     * Determine if the user can create posts
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create
    }

    /**
     * Determine if the user can update the post
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // Only owner can update
    }

    /**
     * Determine if the user can delete the post
     */
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // Only owner can delete
    }

    /**
     * Determine if the user can restore the post
     */
    public function restore(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine if the user can permanently delete the post
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

### Step 5: Register the Policy
Open `app/Providers/AuthServiceProvider.php` and register the policy:

```php
namespace App\Providers;

use App\Models\Post;
use App\Policies\PostPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
```

### Step 6: Create PostController
```bash
php artisan make:controller PostController --resource
```

In `app/Http/Controllers/PostController.php`:

```php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct()
    {
        // Require authentication for all actions
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

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

        auth()->user()->posts()->create($validated);

        return redirect('/posts')->with('success', 'Post created!');
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function edit(Post $post)
    {
        // Check authorization using policy
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        // Check authorization using policy
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($validated);

        return redirect("/posts/{$post->id}")->with('success', 'Post updated!');
    }

    public function destroy(Post $post)
    {
        // Check authorization using policy
        $this->authorize('delete', $post);

        $post->delete();

        return redirect('/posts')->with('success', 'Post deleted!');
    }
}
```

### Step 7: Create Views
Create `resources/views/posts/index.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-4">Blog Posts</h1>

                    @auth
                        <a href="/posts/create" class="mb-4 inline-block px-4 py-2 bg-blue-500 text-white rounded">
                            Create Post
                        </a>
                    @endauth

                    @forelse($posts as $post)
                        <div class="mb-6 p-4 border rounded">
                            <h2 class="text-xl font-bold">
                                <a href="/posts/{{ $post->id }}">{{ $post->title }}</a>
                            </h2>
                            <p class="text-gray-600">
                                By {{ $post->author->name }}
                                on {{ $post->created_at->format('M d, Y') }}
                            </p>
                            <p class="mt-2">{{ Str::limit($post->content, 200) }}</p>

                            <div class="mt-4">
                                <a href="/posts/{{ $post->id }}" class="text-blue-500">Read More</a>

                                {{-- Show edit/delete only if authorized --}}
                                @can('update', $post)
                                    <a href="/posts/{{ $post->id }}/edit" class="ml-4 text-yellow-500">Edit</a>
                                @endcan

                                @can('delete', $post)
                                    <form method="POST" action="/posts/{{ $post->id }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-4 text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p>No posts found</p>
                    @endforelse

                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
```

Create `resources/views/posts/show.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold mb-2">{{ $post->title }}</h1>
                    <p class="text-gray-600 mb-6">
                        By <strong>{{ $post->author->name }}</strong>
                        on {{ $post->created_at->format('M d, Y') }}
                    </p>

                    <div class="prose max-w-none mb-6">
                        {!! nl2br(e($post->content)) !!}
                    </div>

                    <div class="flex gap-4">
                        <a href="/posts" class="px-4 py-2 bg-gray-500 text-white rounded">Back</a>

                        {{-- Show edit only if authorized --}}
                        @can('update', $post)
                            <a href="/posts/{{ $post->id }}/edit" class="px-4 py-2 bg-yellow-500 text-white rounded">
                                Edit
                            </a>
                        @endcan

                        {{-- Show delete only if authorized --}}
                        @can('delete', $post)
                            <form method="POST" action="/posts/{{ $post->id }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded"
                                        onclick="return confirm('Delete this post?')">
                                    Delete
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
```

Create `resources/views/posts/create.blade.php`:

```blade
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-bold mb-4">Create Post</h1>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="/posts">
                        @csrf

                        <div class="mb-4">
                            <label for="title" class="block font-bold mb-2">Title</label>
                            <input type="text" name="title" id="title" class="w-full border rounded p-2"
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="content" class="block font-bold mb-2">Content</label>
                            <textarea name="content" id="content" rows="10" class="w-full border rounded p-2" required>{{ old('content') }}</textarea>
                            @error('content')
                                <p class="text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded">
                            Create Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
```

Create `resources/views/posts/edit.blade.php` (similar to create).

### Step 8: Add Routes
In `routes/web.php`:

```php
Route::resource('posts', PostController::class);
```

### Step 9: Test Authorization
1. Login as User A and create a post
2. Login as User B
3. Try to edit User A's post - should see 403 error
4. Try to delete User A's post - should see 403 error
5. User A should be able to edit and delete their own posts

### Step 10: Advanced Policy Methods
Add conditional logic to policies:

```php
public function update(User $user, Post $post): bool
{
    // Owner can always update
    if ($user->id === $post->user_id) {
        return true;
    }

    // Admins can update any post
    return $user->is_admin;
}
```

### Step 11: Check Authorization in Views
Use `@can` and `@cannot` directives:

```blade
@can('update', $post)
    <a href="/posts/{{ $post->id }}/edit">Edit</a>
@endcan

@cannot('delete', $post)
    <p>You cannot delete this post</p>
@endcannot

@canany(['update', 'delete'], $post)
    <div class="admin-menu">...</div>
@endcanany

@cannot('update', $post)
    <p>You don't have permission</p>
@else
    <p>You can edit this post</p>
@endcannot
```

### Step 12: Test 403 Responses
Update `routes/web.php` to add a response handler:

```php
// In app/Http/Middleware/HandleDatabaseTransactions.php
// or in app/Exceptions/Handler.php
```

## Deliverables
- [ ] PostPolicy created with all methods
- [ ] Policy registered in AuthServiceProvider
- [ ] PostController using authorize() checks
- [ ] All CRUD views created
- [ ] @can directives in views
- [ ] Only post owner can edit/delete
- [ ] 403 error shown for unauthorized access
- [ ] Normal users can't see edit/delete buttons for other's posts
- [ ] Admin can access any post (if applicable)
- [ ] Authorization tested thoroughly

## Resources
- [Authorization](https://laravel.com/docs/11.x/authorization)
- [Policies](https://laravel.com/docs/11.x/authorization#creating-policies)
- [Policy Methods](https://laravel.com/docs/11.x/authorization#policy-methods)

## Tips
- Always use `$this->authorize()` in controllers
- Use `@can` directives in views
- Policies provide centralized authorization logic
- Test with multiple users for comprehensive testing
- 403 Forbidden is the correct HTTP status for denied access
- Consider Admin role for special permissions
