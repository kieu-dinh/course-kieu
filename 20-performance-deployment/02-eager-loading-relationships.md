# Eager Loading vs Lazy Loading

**Duration**: 3-4 hours

---

## Introduction

Understanding when and how to load relationships is crucial for building performant Laravel applications. In this lesson, you'll dive deeper into eager loading strategies, learn advanced techniques, and understand when lazy loading is actually the right choice.

**What you'll learn:**
- The difference between eager and lazy loading
- Advanced eager loading techniques
- Nested relationship loading
- Conditional relationship loading
- When to use each approach
- Preventing lazy loading issues

---

## Lazy Loading: The Default Behavior

### What Is Lazy Loading?

Lazy loading means relationships are loaded **only when you access them**. This is Laravel's default behavior.

**Example:**
```php
$post = Post::find(1); // SELECT * FROM posts WHERE id = 1

// Post is loaded, but not the user relationship
echo $post->title; // No additional query

// Now we access the user relationship
echo $post->user->name; // SELECT * FROM users WHERE id = ?
```

The user is loaded "lazily" - only when needed.

### When Lazy Loading Is Good

Lazy loading is efficient when:

1. **You might not need the relationship:**
```php
$post = Post::find(1);

if ($post->is_premium) {
    // Only premium posts need author info
    echo $post->user->name;
}
```

2. **Single model operations:**
```php
$post = Post::find(1);
echo $post->user->name; // Just 2 queries total
```

3. **Admin panels with tabs:**
```php
// On initial load, only show basic info
$user = User::find(1);

// Later, when they click "Posts" tab, load posts
$posts = $user->posts; // Loaded on demand
```

### The Problem: Lazy Loading in Loops

Lazy loading becomes a disaster when iterating over collections:

```php
$posts = Post::all(); // 1 query

foreach ($posts as $post) {
    echo $post->user->name; // 1 query per post!
}
// Total: 1 + N queries (N+1 problem)
```

---

## Eager Loading: The Solution

### What Is Eager Loading?

Eager loading means relationships are loaded **upfront with the initial query**. You explicitly tell Laravel: "I know I'll need these relationships, load them now."

**Example:**
```php
$posts = Post::with('user')->get();
// Query 1: SELECT * FROM posts
// Query 2: SELECT * FROM users WHERE id IN (1, 2, 3, ...)

foreach ($posts as $post) {
    echo $post->user->name; // No additional queries!
}
// Total: 2 queries, regardless of post count
```

### Basic Eager Loading

**Single relationship:**
```php
$posts = Post::with('user')->get();
```

**Multiple relationships:**
```php
$posts = Post::with(['user', 'category', 'tags'])->get();
```

**On a single model:**
```php
$post = Post::with(['user', 'comments'])->find(1);
```

---

## Nested Eager Loading

### Loading Relationships of Relationships

Often you need not just a relationship, but a relationship's relationship.

**Example: Posts with comments and each comment's author**

```php
// ❌ Bad: N+1 on comments, then N+1 on each comment's user
$posts = Post::with('comments')->get();

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->user->name; // Query per comment!
    }
}
```

```php
// ✅ Good: Nested eager loading
$posts = Post::with('comments.user')->get();

foreach ($posts as $post) {
    foreach ($post->comments as $comment) {
        echo $comment->user->name; // No additional queries
    }
}
```

**The dot notation** (`comments.user`) tells Laravel to load comments AND each comment's user.

### Multiple Nested Relationships

```php
$posts = Post::with([
    'user.profile',           // Post author and their profile
    'category.parent',        // Category and parent category
    'comments.user.avatar',   // Comments, their authors, and avatars
    'tags',                   // Post tags
])->get();
```

### Real-World Example: Blog Post Page

```php
// PostController.php
public function show($slug)
{
    $post = Post::where('slug', $slug)
        ->with([
            'user.profile',              // Author info
            'category',                  // Category
            'tags',                      // Tags
            'comments' => function ($query) {
                $query->whereNull('parent_id') // Only root comments
                      ->latest()
                      ->with('user', 'replies.user'); // Comment authors
            },
        ])
        ->firstOrFail();

    return view('posts.show', compact('post'));
}
```

This loads everything needed in just a few queries instead of hundreds.

---

## Conditional Eager Loading

### Loading Relationships with Constraints

Sometimes you want to load a relationship, but with filters applied.

**Example: Only approved comments**

```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', true)
          ->orderBy('created_at', 'desc');
}])->get();
```

**Example: Latest 5 comments per post**

```php
$posts = Post::with(['comments' => function ($query) {
    $query->latest()->limit(5);
}])->get();
```

**Example: Comments from the last 7 days**

```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('created_at', '>=', now()->subDays(7));
}])->get();
```

### Conditional Loading Based on User

```php
public function index(Request $request)
{
    $query = Post::query();

    // Admin sees all comments, users see only approved
    if ($request->user()?->isAdmin()) {
        $query->with('comments');
    } else {
        $query->with(['comments' => function ($q) {
            $q->where('approved', true);
        }]);
    }

    $posts = $query->get();

    return view('posts.index', compact('posts'));
}
```

---

## Advanced Eager Loading Techniques

### 1. Lazy Eager Loading

If you've already loaded models and realize you need a relationship:

```php
$posts = Post::all();

// Later, you realize you need the users
$posts->load('user');
```

This is better than:
```php
foreach ($posts as $post) {
    $post->user; // N+1 problem
}
```

### 2. Eager Loading with Counts

Get a count of related models without loading them:

```php
$posts = Post::withCount('comments')->get();

foreach ($posts as $post) {
    echo $post->comments_count; // No loading, just a number
}
```

**Multiple counts:**
```php
$posts = Post::withCount(['comments', 'likes', 'shares'])->get();

echo $post->comments_count;
echo $post->likes_count;
echo $post->shares_count;
```

**Conditional counts:**
```php
$posts = Post::withCount([
    'comments',
    'comments as approved_comments_count' => function ($query) {
        $query->where('approved', true);
    },
])->get();

echo $post->comments_count; // Total comments
echo $post->approved_comments_count; // Only approved
```

### 3. Eager Loading with Aggregates

**Sum:**
```php
$users = User::withSum('orders', 'total')->get();

foreach ($users as $user) {
    echo $user->orders_sum_total; // Sum of all order totals
}
```

**Average:**
```php
$products = Product::withAvg('reviews', 'rating')->get();

foreach ($products as $product) {
    echo $product->reviews_avg_rating; // Average rating
}
```

**Min/Max:**
```php
$users = User::withMin('orders', 'created_at')
    ->withMax('orders', 'created_at')
    ->get();

echo $user->orders_min_created_at; // First order date
echo $user->orders_max_created_at; // Last order date
```

### 4. Exists Checks

Check if a relationship exists without loading it:

```php
$posts = Post::withExists('comments')->get();

if ($post->comments_exists) {
    // Has comments
}
```

### 5. Morph Relationship Eager Loading

For polymorphic relationships:

```php
// Load different types efficiently
$comments = Comment::with('commentable')->get();

// Constrain by type
$comments = Comment::with([
    'commentable' => function ($query) {
        $query->where('status', 'published');
    }
])->get();
```

**Morph To Many:**
```php
$tags = Tag::with('posts', 'videos', 'products')->get();
```

---

## Selecting Specific Columns

### Only Load What You Need

```php
// Loads ALL columns from users table
$posts = Post::with('user')->get();

// Only loads id and name from users
$posts = Post::with('user:id,name')->get();
```

**Important:** Always include the foreign key (the column that links the relationship).

```php
// ❌ Bad: Missing user_id foreign key
$posts = Post::with('user:id,name')->get();

// ✅ Good: Includes user_id
$posts = Post::with('user:id,name')
    ->select('id', 'title', 'user_id') // Include foreign key
    ->get();
```

### Multiple Relationships with Specific Columns

```php
$posts = Post::select('id', 'title', 'user_id', 'category_id')
    ->with([
        'user:id,name,email',
        'category:id,name',
        'tags:name,slug', // Pivot table handles IDs automatically
    ])
    ->get();
```

---

## Preventing Lazy Loading Problems

### Disabling Lazy Loading in Development

Add this to your `AppServiceProvider`:

```php
use Illuminate\Database\Eloquent\Model;

public function boot()
{
    // Throw exception on lazy loading in development
    Model::preventLazyLoading(! app()->isProduction());
}
```

Now, if you forget to eager load:
```php
$post = Post::find(1);
echo $post->user->name; // Exception in development!
```

This helps you catch N+1 problems during development instead of discovering them in production.

### Strict Loading

Even stricter mode that prevents lazy loading AND mass assignment:

```php
Model::preventLazyLoading(! app()->isProduction());
Model::preventAccessingMissingAttributes(! app()->isProduction());
Model::preventSilentlyDiscardingAttributes(! app()->isProduction());
```

---

## Performance Comparison

### Measuring the Impact

**Scenario:** Display 100 blog posts with authors

**Method 1: Lazy Loading (The Problem)**
```php
$posts = Post::limit(100)->get();

foreach ($posts as $post) {
    echo $post->user->name;
}
// Queries: 101 (1 for posts + 100 for users)
// Time: ~500-1000ms
```

**Method 2: Eager Loading (The Solution)**
```php
$posts = Post::with('user')->limit(100)->get();

foreach ($posts as $post) {
    echo $post->user->name;
}
// Queries: 2 (1 for posts + 1 for users)
// Time: ~10-20ms
```

**Result:** 50-100x faster with eager loading!

### Real Application Example

**Before optimization:**
```php
// PostController.php
public function index()
{
    $posts = Post::published()->paginate(20);
    return view('posts.index', compact('posts'));
}
```

```blade
@foreach($posts as $post)
    <h2>{{ $post->title }}</h2>
    <p>By {{ $post->user->name }}</p>
    <p>{{ $post->category->name }}</p>
    <p>{{ $post->comments->count() }} comments</p>
@endforeach
```

**Queries executed:** 1 (posts) + 20 (users) + 20 (categories) + 20 (comments) = **61 queries**

**After optimization:**
```php
// PostController.php
public function index()
{
    $posts = Post::published()
        ->with(['user:id,name', 'category:id,name'])
        ->withCount('comments')
        ->select('id', 'title', 'slug', 'user_id', 'category_id')
        ->paginate(20);

    return view('posts.index', compact('posts'));
}
```

**Queries executed:** 1 (posts) + 1 (users) + 1 (categories) = **3 queries**

**Result:** 95% reduction in queries!

---

## Best Practices

### 1. Eager Load in Controllers, Not Views

**❌ Bad:**
```blade
{{-- View trying to fix N+1 --}}
@foreach(Post::with('user')->get() as $post)
    ...
@endforeach
```

**✅ Good:**
```php
// Controller
public function index()
{
    $posts = Post::with('user')->get();
    return view('posts.index', compact('posts'));
}
```

Views should display data, not load it.

### 2. Use Query Scopes for Common Loads

**Instead of repeating:**
```php
$posts = Post::with(['user', 'category', 'tags'])->get();
```

**Create a scope:**
```php
// Post model
public function scopeWithRelations($query)
{
    return $query->with(['user', 'category', 'tags']);
}

// Usage
$posts = Post::withRelations()->get();
```

### 3. Profile Before Optimizing

Don't guess - measure:
```php
DB::enableQueryLog();

// Your code

dd(DB::getQueryLog());
```

Or use Laravel Debugbar to see all queries.

### 4. Consider the Trade-off

Eager loading isn't always better:

**❌ Bad: Over-eager loading**
```php
// Loading relationships you might not use
$posts = Post::with([
    'user',
    'user.profile',
    'user.posts',
    'user.comments',
    'category',
    'category.posts',
    'tags',
    'tags.posts',
    'comments',
    'comments.user',
    'comments.replies',
])->paginate(10);
```

This loads thousands of records you might not need!

**✅ Good: Load only what you use**
```php
$posts = Post::with(['user', 'category', 'tags'])
    ->withCount('comments')
    ->paginate(10);
```

---

## Common Patterns

### Pattern 1: List Pages

```php
// Lists need: basic info + counts
public function index()
{
    $posts = Post::select('id', 'title', 'slug', 'excerpt', 'user_id', 'created_at')
        ->with('user:id,name')
        ->withCount('comments')
        ->latest()
        ->paginate(20);

    return view('posts.index', compact('posts'));
}
```

### Pattern 2: Detail Pages

```php
// Details need: full info + relationships
public function show($slug)
{
    $post = Post::where('slug', $slug)
        ->with([
            'user.profile',
            'category',
            'tags',
            'comments.user',
        ])
        ->firstOrFail();

    return view('posts.show', compact('post'));
}
```

### Pattern 3: API Resources

```php
// APIs need: specific fields + nested data
public function index()
{
    return PostResource::collection(
        Post::with(['user:id,name', 'category:id,name'])
            ->select('id', 'title', 'slug', 'user_id', 'category_id')
            ->paginate(20)
    );
}
```

### Pattern 4: Admin Panels

```php
// Admin needs: more data + audit info
public function index()
{
    $posts = Post::with([
            'user:id,name,email',
            'category:id,name',
        ])
        ->withCount(['comments', 'likes'])
        ->withExists('featuredImage')
        ->latest()
        ->paginate(50);

    return view('admin.posts.index', compact('posts'));
}
```

---

## Debugging Tips

### Finding N+1 Problems

**1. Install Query Detector:**
```bash
composer require beyondcode/laravel-query-detector --dev
```

Automatically warns you about N+1 queries in development.

**2. Count Queries:**
```php
DB::listen(function ($query) {
    // Log all queries
    logger($query->sql, $query->bindings);
});
```

**3. Check Debugbar:**
Look for:
- High query counts (50+, 100+)
- Repeated similar queries
- Queries in loops

### Testing Eager Loading

```php
// Test that only 2 queries are executed
public function test_index_avoids_n_plus_one()
{
    User::factory()->count(10)->create();
    Post::factory()->count(20)->create();

    DB::enableQueryLog();

    $this->get('/posts')
        ->assertSuccessful();

    $queries = DB::getQueryLog();

    // Should be only a few queries, not 20+
    $this->assertCount(3, $queries);
}
```

---

## Quick Reference

```php
// Basic eager loading
Post::with('user')->get();

// Multiple relationships
Post::with(['user', 'category', 'tags'])->get();

// Nested relationships
Post::with('comments.user')->get();

// Conditional loading
Post::with(['comments' => fn($q) => $q->where('approved', true)])->get();

// Lazy eager loading
$posts->load('user');

// Counts
Post::withCount('comments')->get();

// Aggregates
User::withSum('orders', 'total')->get();

// Exists
Post::withExists('comments')->get();

// Specific columns
Post::with('user:id,name')->get();

// Prevent lazy loading
Model::preventLazyLoading(! app()->isProduction());
```

---

## Practice Questions

1. **What's the difference between eager loading and lazy loading?** When would you use each?

2. **How do you load nested relationships?** Give an example.

3. **What does `withCount()` do?** How is it different from loading the relationship?

4. **Why should you prevent lazy loading in development?** How do you do it?

5. **What columns must you include when selecting specific columns with relationships?**

6. **How can you conditionally load relationships?** Give an example.

---

## Next Steps

Now you understand relationship loading! Next, you'll learn about caching - how to avoid running queries altogether by storing results in memory.

**Coming up in Lesson 03:**
- Cache strategies (Redis, file, database)
- Query result caching
- Cache invalidation patterns
- Cache tags and invalidation strategies
