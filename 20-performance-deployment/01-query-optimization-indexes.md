# Query Optimization and Database Indexes

**Duration**: 3-4 hours

---

## Introduction

Performance starts at the database level. A poorly optimized query can bring your entire application to its knees, no matter how well the rest of your code is written. In this lesson, you'll learn how to identify slow queries, understand the dreaded N+1 problem, and use database indexes effectively.

**What you'll learn:**
- Identifying slow queries in Laravel
- Understanding and solving the N+1 problem
- Using database indexes effectively
- Query optimization techniques
- Using Laravel Debugbar and Telescope

---

## Understanding Query Performance

### Why Query Performance Matters

Imagine your application needs to display a list of 100 blog posts with their authors. If each post requires a separate database query to fetch its author, you're making 101 queries (1 for posts + 100 for authors) instead of just 2. This is the N+1 problem, and it's one of the most common performance killers in web applications.

**Real-world impact:**
- A page that takes 3 seconds to load loses 40% of visitors
- Every 100ms delay costs 1% in sales for e-commerce sites
- Mobile users are even less patient than desktop users

### Measuring Query Performance

Before optimizing, you need to measure. Laravel provides several tools:

**1. Laravel Debugbar** (development only):
```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows queries executed, execution time, and memory usage in a toolbar at the bottom of your page.

**2. Laravel Telescope** (development and staging):
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Provides detailed insights into queries, including slow queries, duplicates, and more.

**3. Query Logging** (manual):
```php
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

// Your code here

$queries = DB::getQueryLog();
dd($queries);
```

---

## The N+1 Problem

### What Is the N+1 Problem?

The N+1 problem occurs when you fetch a collection of N items and then make an additional query for each item to fetch related data.

**Example: Bad Code (N+1 Problem)**

```php
// PostController.php
public function index()
{
    $posts = Post::all(); // 1 query

    return view('posts.index', compact('posts'));
}
```

```blade
{{-- posts/index.blade.php --}}
@foreach($posts as $post)
    <h2>{{ $post->title }}</h2>
    <p>By {{ $post->user->name }}</p> {{-- 1 query per post! --}}
@endforeach
```

If you have 100 posts, this executes 101 queries:
- 1 query to get all posts
- 100 queries to get each post's user

### Detecting the N+1 Problem

**Using Laravel Debugbar:**
Open the "Queries" tab and look for:
- High number of queries (50+, 100+, etc.)
- Repeated similar queries with different IDs
- Queries inside loops

**Example output:**
```
select * from posts
select * from users where id = 1
select * from users where id = 2
select * from users where id = 3
... (97 more times)
```

**Using the N+1 Query Detector Package:**
```bash
composer require beyondcode/laravel-query-detector --dev
```

This package automatically detects N+1 queries and displays warnings in development.

### Solving the N+1 Problem: Eager Loading

**Solution: Use `with()` to eager load relationships**

```php
// PostController.php
public function index()
{
    // Eager load the user relationship
    $posts = Post::with('user')->get(); // 2 queries total!

    return view('posts.index', compact('posts'));
}
```

Now it executes only 2 queries:
```sql
select * from posts
select * from users where id in (1, 2, 3, 4, 5, ...)
```

### Multiple Relationships

```php
// Load multiple relationships
$posts = Post::with(['user', 'category', 'tags'])->get();

// Nested relationships
$posts = Post::with('user.profile')->get();

// Conditional eager loading
$posts = Post::with(['comments' => function ($query) {
    $query->where('approved', true)
          ->orderBy('created_at', 'desc')
          ->limit(5);
}])->get();
```

### Lazy Eager Loading

If you've already loaded models and realize you need a relationship:

```php
$posts = Post::all();

// Later, you realize you need users
$posts->load('user');
```

### Preventing Lazy Loading in Production

Add this to your `AppServiceProvider`:

```php
use Illuminate\Database\Eloquent\Model;

public function boot()
{
    // Prevent lazy loading in production
    Model::preventLazyLoading(! app()->isProduction());
}
```

This will throw an exception in development when you accidentally lazy load, helping you catch N+1 problems early.

---

## Database Indexes

### What Are Database Indexes?

Think of a database index like the index at the back of a book. Instead of reading every page to find "Performance Optimization," you look in the index, which tells you exactly which pages to read.

**Without an index:**
```sql
-- Database scans ALL rows (full table scan)
SELECT * FROM posts WHERE user_id = 5;
-- Might scan 1,000,000 rows to find 10 posts
```

**With an index on user_id:**
```sql
-- Database uses index to jump directly to matching rows
SELECT * FROM posts WHERE user_id = 5;
-- Only looks at the 10 matching rows
```

### When to Add Indexes

Add indexes to columns that are:

1. **Foreign keys** (user_id, category_id, etc.)
2. **Frequently searched** (WHERE clauses)
3. **Used for sorting** (ORDER BY clauses)
4. **Used in joins** (JOIN conditions)
5. **Unique identifiers** (email, username, etc.)

**DO NOT index:**
- Small tables (< 1000 rows)
- Columns with low cardinality (status with only 3 values)
- Columns rarely queried
- Every column (indexes slow down INSERT/UPDATE)

### Adding Indexes in Laravel Migrations

**Basic index:**
```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('content');
    $table->enum('status', ['draft', 'published'])->default('draft');
    $table->timestamps();

    // Add indexes
    $table->index('status'); // Often filtered by status
    $table->index('created_at'); // Often sorted by date
});
```

**Composite index** (multiple columns):
```php
// For queries like: WHERE user_id = 5 AND status = 'published'
$table->index(['user_id', 'status']);
```

**Unique index:**
```php
$table->unique('email');
$table->unique('slug');
```

**Adding indexes to existing tables:**
```php
Schema::table('posts', function (Blueprint $table) {
    $table->index('status');
});
```

**Removing indexes:**
```php
Schema::table('posts', function (Blueprint $table) {
    $table->dropIndex(['status']); // Drop by column
    $table->dropIndex('posts_status_index'); // Drop by index name
});
```

### Analyzing Index Usage

**Check if a query uses indexes (MySQL):**
```php
DB::select('EXPLAIN SELECT * FROM posts WHERE user_id = 5');
```

Look for:
- `type: ALL` = bad (full table scan)
- `type: index` or `type: ref` = good (using index)
- `rows: 100000` vs `rows: 10` = how many rows examined

---

## Query Optimization Techniques

### 1. Select Only Needed Columns

**Bad:**
```php
$posts = Post::all(); // Selects ALL columns
```

**Good:**
```php
$posts = Post::select('id', 'title', 'slug', 'created_at')->get();
```

Even better with relationships:
```php
$posts = Post::with('user:id,name')->select('id', 'title', 'user_id')->get();
```

### 2. Use Chunking for Large Datasets

**Bad (loads everything into memory):**
```php
$posts = Post::all(); // 1,000,000 posts = out of memory!

foreach ($posts as $post) {
    // Process
}
```

**Good (processes in chunks):**
```php
Post::chunk(200, function ($posts) {
    foreach ($posts as $post) {
        // Process
    }
});
```

**Even better (lazy collections):**
```php
Post::lazy()->each(function ($post) {
    // Process one at a time, no memory issues
});
```

### 3. Use `exists()` Instead of `count()`

**Bad:**
```php
if (Post::where('user_id', $userId)->count() > 0) {
    // Has posts
}
```

**Good:**
```php
if (Post::where('user_id', $userId)->exists()) {
    // Has posts
}
```

`exists()` stops as soon as it finds one match, while `count()` counts them all.

### 4. Avoid Queries in Loops

**Bad:**
```php
foreach ($userIds as $userId) {
    $user = User::find($userId); // Query per iteration!
    // Process user
}
```

**Good:**
```php
$users = User::whereIn('id', $userIds)->get()->keyBy('id');

foreach ($userIds as $userId) {
    $user = $users[$userId];
    // Process user
}
```

### 5. Use Database Functions

**Bad (PHP processing):**
```php
$posts = Post::all();
$publishedCount = $posts->where('status', 'published')->count();
```

**Good (database processing):**
```php
$publishedCount = Post::where('status', 'published')->count();
```

**Aggregations:**
```php
// Count
$count = Post::where('status', 'published')->count();

// Sum
$totalViews = Post::sum('views');

// Average
$avgRating = Product::avg('rating');

// Min/Max
$cheapest = Product::min('price');
$mostExpensive = Product::max('price');
```

### 6. Use Query Scopes for Reusable Queries

**Instead of repeating queries:**
```php
// In multiple controllers
$posts = Post::where('status', 'published')
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->get();
```

**Create a scope:**
```php
// Post model
public function scopePublished($query)
{
    return $query->where('status', 'published')
        ->where('published_at', '<=', now());
}

public function scopeLatest($query)
{
    return $query->orderBy('published_at', 'desc');
}

// Usage
$posts = Post::published()->latest()->get();
```

---

## Practical Example: Optimizing a Blog

### Before Optimization

```php
// PostController.php
public function index()
{
    $posts = Post::where('status', 'published')->get();

    return view('posts.index', compact('posts'));
}
```

```blade
{{-- posts/index.blade.php --}}
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>By {{ $post->user->name }}</p> {{-- N+1 --}}
        <p>Category: {{ $post->category->name }}</p> {{-- N+1 --}}
        <p>{{ $post->comments->count() }} comments</p> {{-- N+1 --}}
        <p>{{ $post->tags->pluck('name')->join(', ') }}</p> {{-- N+1 --}}
    </article>
@endforeach
```

**Problems:**
- N+1 on user
- N+1 on category
- N+1 on comments
- N+1 on tags
- Loads all columns
- No pagination

### After Optimization

```php
// Post model - add scope
public function scopePublished($query)
{
    return $query->where('status', 'published')
        ->where('published_at', '<=', now());
}

// PostController.php
public function index()
{
    $posts = Post::published()
        ->with(['user:id,name', 'category:id,name'])
        ->withCount('comments')
        ->with('tags:name')
        ->select('id', 'title', 'slug', 'excerpt', 'user_id', 'category_id', 'published_at')
        ->latest('published_at')
        ->paginate(20);

    return view('posts.index', compact('posts'));
}
```

```blade
{{-- posts/index.blade.php --}}
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>By {{ $post->user->name }}</p>
        <p>Category: {{ $post->category->name }}</p>
        <p>{{ $post->comments_count }} comments</p>
        <p>{{ $post->tags->pluck('name')->join(', ') }}</p>
    </article>
@endforeach

{{ $posts->links() }}
```

```php
// Migration - add indexes
Schema::table('posts', function (Blueprint $table) {
    $table->index(['status', 'published_at']);
});
```

**Improvements:**
- Fixed all N+1 queries (101 queries → 5 queries)
- Only loads needed columns
- Added pagination
- Added composite index for common query
- Used `withCount()` for comment count

---

## Monitoring and Tools

### Laravel Debugbar (Development)

Install:
```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows:
- All executed queries
- Query execution time
- Memory usage
- Route information
- Views rendered

### Laravel Telescope (Development/Staging)

Install:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Features:
- Query monitoring with slow query detection
- Request monitoring
- Exception tracking
- Job monitoring
- Cache hit/miss rates

Access at: `http://your-app.test/telescope`

### Clockwork (Chrome Extension)

Alternative to Debugbar, works with browser DevTools:
```bash
composer require itsgoingd/clockwork
```

---

## Quick Reference

### Common Optimizations

```php
// ❌ Bad
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name; // N+1
}

// ✅ Good
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;
}

// ❌ Bad
$count = Post::all()->count();

// ✅ Good
$count = Post::count();

// ❌ Bad
$posts = Post::all();

// ✅ Good
$posts = Post::select('id', 'title')->get();

// ❌ Bad
Post::where('user_id', $id)->count() > 0

// ✅ Good
Post::where('user_id', $id)->exists()
```

### Index Guidelines

```php
// Add index for foreign keys
$table->foreignId('user_id')->constrained()->index();

// Add index for frequent WHERE clauses
$table->index('status');
$table->index('email');

// Add composite index for combined WHERE clauses
$table->index(['user_id', 'status']);

// Add unique index for unique columns
$table->unique('email');
$table->unique('slug');
```

---

## Practice Questions

Before moving on, make sure you can answer:

1. **What is the N+1 problem?** Can you explain it to someone who's never heard of it?

2. **When should you use eager loading?** Can you give three examples from real applications?

3. **What's the difference between an index and a unique index?**

4. **Why shouldn't you index every column?** What are the trade-offs?

5. **How do you measure query performance in Laravel?** Name at least two tools.

6. **What's the difference between `count()` and `exists()`?** When would you use each?

---

## Next Steps

Now that you understand query optimization, you're ready to learn about caching strategies in the next lesson. You'll learn how to avoid running queries at all by storing frequently accessed data in memory.

**Coming up:**
- Caching strategies (Redis, file cache, database cache)
- Cache invalidation
- Query result caching
- Full-page caching

Remember: **The fastest query is the one you don't have to run!**
