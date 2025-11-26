# 04 - Blog with Eloquent

## Objective
Build a complete blog application using Eloquent ORM with proper relationships, optimization, and advanced features.

## Prerequisites
- Completed all Module 15 exercises
- Understanding of MVC architecture
- Knowledge of Eloquent relationships and optimization

## Instructions

### Step 1: Design Database Schema
Create migrations for:
- `users` (authors)
- `posts` (blog articles)
- `comments` (post comments)
- `tags` (post tags)
- `categories` (post categories)

### Step 2: Create Models and Migrations

```bash
php artisan make:model User -m
php artisan make:model Post -m
php artisan make:model Comment -m
php artisan make:model Tag -m
php artisan make:model Category -m
```

### Step 3: Define Users Migration
Edit users migration:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->text('bio')->nullable();
    $table->string('avatar')->nullable();
    $table->timestamps();
});
```

### Step 4: Define Posts Migration
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->constrained()->onDelete('set null')->nullable();
    $table->boolean('published')->default(false);
    $table->dateTime('published_at')->nullable();
    $table->timestamps();
});
```

### Step 5: Define Comments Migration
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->boolean('approved')->default(true);
    $table->timestamps();
});
```

### Step 6: Define Tags Migration
```php
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('post_tag', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
    $table->primary(['post_id', 'tag_id']);
});
```

### Step 7: Define Category Migration
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->timestamps();
});
```

Run migrations:

```bash
php artisan migrate
```

### Step 8: Define Models with Relationships

In `app/Models/User.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password', 'bio', 'avatar'];
    protected $hidden = ['password'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

In `app/Models/Post.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'user_id',
        'category_id', 'published', 'published_at'
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function approvedComments(): HasMany
    {
        return $this->comments()->where('approved', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeByAuthor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
```

In `app/Models/Comment.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['content', 'user_id', 'post_id', 'approved'];
    protected $casts = ['approved' => 'boolean'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

In `app/Models/Category.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

In `app/Models/Tag.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
```

### Step 9: Create Controllers

Generate controllers:

```bash
php artisan make:controller PostController --resource
php artisan make:controller CommentController --resource
php artisan make:controller CategoryController --resource
php artisan make:controller TagController --resource
```

In `PostController`:

```php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Eager load to avoid N+1
        $posts = Post::published()
            ->with('author', 'category', 'tags')
            ->latest('published_at')
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        if (!$post->published) {
            abort(404);
        }

        // Load relationships and approval status
        $post->load('author', 'category', 'tags', 'approvedComments.author');

        return view('posts.show', compact('post'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array|exists:tags,id',
            'published' => 'boolean',
        ]);

        $post = auth()->user()->posts()->create($validated);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags'));
        }

        return redirect("/posts/{$post->id}")->with('success', 'Post created!');
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $categories = Category::all();
        $tags = Tag::all();

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array|exists:tags,id',
            'published' => 'boolean',
        ]);

        $post->update($validated);

        if ($request->has('tags')) {
            $post->tags()->sync($request->input('tags'));
        }

        return redirect("/posts/{$post->id}")->with('success', 'Post updated!');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);
        $post->delete();

        return redirect('/posts')->with('success', 'Post deleted!');
    }
}
```

In `CommentController`:

```php
public function store(Request $request, Post $post)
{
    $validated = $request->validate([
        'content' => 'required|string|min:1',
    ]);

    $post->comments()->create([
        'content' => $validated['content'],
        'user_id' => auth()->id(),
    ]);

    return redirect("/posts/{$post->id}")->with('success', 'Comment added!');
}

public function destroy(Comment $comment)
{
    $this->authorize('delete', $comment);
    $postId = $comment->post_id;
    $comment->delete();

    return redirect("/posts/{$postId}")->with('success', 'Comment deleted!');
}
```

### Step 10: Create Views

Create `resources/views/posts/index.blade.php`:

```blade
@extends('layout')

@section('title', 'Blog')

@section('content')
    <div class="container">
        <h1>Blog</h1>
        <a href="/posts/create" class="btn">Write Post</a>

        @forelse($posts as $post)
            <article class="post-preview">
                <h2><a href="/posts/{{ $post->id }}">{{ $post->title }}</a></h2>
                <p class="meta">
                    By <strong>{{ $post->author->name }}</strong>
                    @if($post->category)
                        in <strong>{{ $post->category->name }}</strong>
                    @endif
                    on {{ $post->published_at->format('M d, Y') }}
                </p>
                <p>{{ Str::limit($post->content, 300) }}</p>
                <div class="tags">
                    @foreach($post->tags as $tag)
                        <a href="/tags/{{ $tag->id }}">#{{ $tag->name }}</a>
                    @endforeach
                </div>
                <a href="/posts/{{ $post->id }}">Read more...</a>
            </article>
        @empty
            <p>No posts yet</p>
        @endforelse

        {{ $posts->links() }}
    </div>
@endsection
```

Create `resources/views/posts/show.blade.php`:

```blade
@extends('layout')

@section('title', $post->title)

@section('content')
    <article class="post-full">
        <h1>{{ $post->title }}</h1>
        <div class="post-meta">
            By <a href="/users/{{ $post->author->id }}">{{ $post->author->name }}</a>
            on {{ $post->published_at->format('M d, Y') }}
        </div>

        <div class="post-content">
            {!! nl2br(e($post->content)) !!}
        </div>

        @if($post->tags->isNotEmpty())
            <div class="tags">
                <strong>Tags:</strong>
                @foreach($post->tags as $tag)
                    <a href="/tags/{{ $tag->id }}">{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif
    </article>

    <section class="comments">
        <h2>Comments ({{ $post->approvedComments->count() }})</h2>

        @forelse($post->approvedComments as $comment)
            <div class="comment">
                <strong>{{ $comment->author->name }}</strong>
                <small>{{ $comment->created_at->diffForHumans() }}</small>
                <p>{{ $comment->content }}</p>
            </div>
        @empty
            <p>No comments yet</p>
        @endforelse

        @auth
            <form method="POST" action="/posts/{{ $post->id }}/comments">
                @csrf
                <textarea name="content" required></textarea>
                <button type="submit">Post Comment</button>
            </form>
        @else
            <p><a href="/login">Login</a> to comment</p>
        @endauth
    </section>

    <a href="/posts">Back to Blog</a>
@endsection
```

### Step 11: Create Seeder
```bash
php artisan make:seeder BlogSeeder
```

In seeder:

```php
public function run(): void
{
    $users = User::factory()->count(3)->create();
    $categories = Category::factory()->count(5)->create();
    $tags = Tag::factory()->count(10)->create();

    Post::factory()
        ->count(20)
        ->recycle($users)
        ->recycle($categories)
        ->create()
        ->each(function ($post) use ($tags) {
            $post->tags()->attach($tags->random(rand(1, 3)));
        });

    Comment::factory()
        ->count(50)
        ->recycle($users)
        ->create();
}
```

### Step 12: Define Routes
In `routes/web.php`:

```php
use App\Http\Controllers\{PostController, CommentController};

Route::resource('posts', PostController::class);
Route::post('/posts/{post}/comments', [CommentController::class, 'store']);
Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

Route::get('/', [PostController::class, 'index']);
```

### Step 13: Test the Application
```bash
php artisan migrate
php artisan db:seed --class=BlogSeeder
php artisan serve
```

## Deliverables
- [ ] All models created with proper relationships
- [ ] Database migrations created and run
- [ ] Controllers implementing all CRUD operations
- [ ] Views for listing, showing, creating, editing posts
- [ ] Comment system working
- [ ] Tags and categories functional
- [ ] Eager loading implemented (no N+1 queries)
- [ ] Validation working properly
- [ ] Authorization checks in place
- [ ] Seeder populating database
- [ ] All relationships tested

## Resources
- [Eloquent Documentation](https://laravel.com/docs/11.x/eloquent)
- [Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [Query Optimization](https://laravel.com/docs/11.x/eloquent-relationships#eager-loading)

## Tips
- Always eager load relationships in controllers
- Use scopes for common queries
- Implement authorization for sensitive operations
- Test relationships in Tinker
- Use factories and seeders for testing
- Monitor query count during development
