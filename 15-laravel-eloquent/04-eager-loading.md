# Lesson 04 - Eager Loading and the N+1 Problem

## The Hidden Performance Killer

In Module 06 with PDO, you had to manually write JOINs. While tedious, you had full control over queries.

With Eloquent relationships, accessing related models is **so easy** that you might not realize you're creating a performance disaster:

```php
// Looks innocent, right?
$posts = Post::all();

foreach ($posts as $post) {
    echo $post->user->name;  // Danger!
}
```

**If you have 100 posts, this executes 101 database queries!**

This is the **N+1 problem** - one of the most common Laravel performance issues.

---

## What is the N+1 Problem?

**The Problem:**

When you retrieve N records and then access a relationship on each one, you execute:
- **1 query** to get the N records
- **N queries** to get each record's relationship

Total: **N+1 queries**

### Example: The Disaster

```php
// Query 1: Get all posts
$posts = Post::all();  // SELECT * FROM posts

foreach ($posts as $post) {
    // Query 2: SELECT * FROM users WHERE id = 1
    // Query 3: SELECT * FROM users WHERE id = 1
    // Query 4: SELECT * FROM users WHERE id = 2
    // Query 5: SELECT * FROM users WHERE id = 3
    // ... (one query per post)
    echo $post->user->name;
}
```

**If you have 1000 posts, you just executed 1001 queries!**

### The Solution: Eager Loading

```php
// Query 1: Get all posts
// Query 2: Get all users for those posts
$posts = Post::with('user')->get();

foreach ($posts as $post) {
    // No additional queries!
    echo $post->user->name;
}
```

**Now it's only 2 queries, no matter how many posts!**

---

## Detecting N+1 Queries

### Enable Query Logging

In your controller or route:

```php
use Illuminate\Support\Facades\DB;

DB::enableQueryLog();

$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;
}

// View all queries
dd(DB::getQueryLog());
```

### Laravel Debugbar (Recommended)

Install Laravel Debugbar:

```bash
composer require barryvdh/laravel-debugbar --dev
```

It shows all queries in a toolbar at the bottom of your page. **Invaluable for development!**

### Telescope (For Larger Apps)

```bash
composer require laravel/telescope
php artisan telescope:install
php artisan migrate
```

Visit `/telescope` to see all queries, performance metrics, and more.

---

## Eager Loading with with()

### Basic Eager Loading

```php
// Load posts with their users
$posts = Post::with('user')->get();

foreach ($posts as $post) {
    echo $post->user->name;  // No additional query!
}
```

**SQL executed:**
```sql
-- Query 1
SELECT * FROM posts

-- Query 2
SELECT * FROM users WHERE id IN (1, 2, 3, 4, 5)
```

### Multiple Relationships

```php
// Load multiple relationships
$posts = Post::with(['user', 'comments', 'tags'])->get();

foreach ($posts as $post) {
    echo $post->user->name;
    echo $post->comments->count();
    echo $post->tags->pluck('name')->implode(', ');
}
```

**SQL executed:**
```sql
SELECT * FROM posts
SELECT * FROM users WHERE id IN (...)
SELECT * FROM comments WHERE post_id IN (...)
SELECT * FROM tags JOIN post_tag WHERE post_id IN (...)
```

**Only 4 queries regardless of number of posts!**

### Nested Eager Loading

Load relationships of relationships:

```php
// Load posts, with users, and with comments (and each comment's user)
$posts = Post::with(['user', 'comments.user'])->get();

foreach ($posts as $post) {
    echo $post->user->name;

    foreach ($post->comments as $comment) {
        echo $comment->user->name;  // No N+1 here either!
    }
}
```

**Dot notation** (`comments.user`) loads nested relationships.

---

## Constraining Eager Loads

Apply conditions to eager loaded relationships:

### Basic Constraints

```php
// Only load published comments
$posts = Post::with(['comments' => function ($query) {
    $query->where('status', 'approved');
}])->get();

// Only load recent comments
$posts = Post::with(['comments' => function ($query) {
    $query->latest()->take(5);
}])->get();

// Load user with only published posts
$users = User::with(['posts' => function ($query) {
    $query->where('status', 'published')
          ->orderBy('created_at', 'desc');
}])->get();
```

### Multiple Constraints

```php
$posts = Post::with([
    'user',
    'comments' => function ($query) {
        $query->where('status', 'approved')
              ->orderBy('created_at', 'desc');
    },
    'comments.user',
    'tags' => function ($query) {
        $query->orderBy('name');
    }
])->get();
```

---

## Lazy Eager Loading

Sometimes you don't know you need a relationship until after you've retrieved models:

```php
$posts = Post::all();

// Later in your code, you realize you need users
// Instead of accessing $post->user in a loop (N+1!)
// Load all users at once:
$posts->load('user');

// Now you can loop without N+1
foreach ($posts as $post) {
    echo $post->user->name;
}
```

**With constraints:**

```php
$posts->load(['comments' => function ($query) {
    $query->where('status', 'approved');
}]);
```

---

## Eager Load Counts

Get relationship counts without loading the relationships:

```php
// Get posts with comment count
$posts = Post::withCount('comments')->get();

foreach ($posts as $post) {
    echo "{$post->title} has {$post->comments_count} comments";
}
```

**SQL executed:**
```sql
SELECT posts.*, (
    SELECT COUNT(*)
    FROM comments
    WHERE post_id = posts.id
) as comments_count
FROM posts
```

**Only 1 query!**

### Multiple Counts

```php
$posts = Post::withCount(['comments', 'likes'])->get();

echo $post->comments_count;
echo $post->likes_count;
```

### Count with Constraints

```php
$posts = Post::withCount([
    'comments',
    'comments as approved_comments_count' => function ($query) {
        $query->where('status', 'approved');
    }
])->get();

echo $post->comments_count;            // All comments
echo $post->approved_comments_count;   // Only approved
```

---

## Eager Load Sums, Averages, etc.

```php
// Sum
$posts = Post::withSum('comments', 'votes')->get();
echo $post->comments_sum_votes;

// Average
$posts = Post::withAvg('comments', 'rating')->get();
echo $post->comments_avg_rating;

// Min / Max
$posts = Post::withMin('comments', 'rating')->get();
$posts = Post::withMax('comments', 'rating')->get();
```

---

## Preventing Lazy Loading in Production

Force yourself to use eager loading by disabling lazy loading:

**In `AppServiceProvider`:**

```php
use Illuminate\Database\Eloquent\Model;

public function boot()
{
    Model::preventLazyLoading(! app()->isProduction());
}
```

Now, accessing relationships without eager loading throws an exception in development:

```php
$post = Post::find(1);
$post->user;  // Exception: "Attempted to lazy load [user] on model [Post]"

// Must do:
$post = Post::with('user')->find(1);
$post->user;  // Works!
```

**This catches N+1 problems during development!**

---

## Comparing PDO and Eloquent Approaches

### PDO Approach

**Option 1: Multiple queries (N+1 problem in PDO too!)**

```php
// Get posts
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll();

foreach ($posts as $post) {
    // Get user for each post (N queries!)
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$post['user_id']]);
    $user = $stmt->fetch();
    echo $user['name'];
}
```

**Option 2: Manual JOIN**

```php
$stmt = $pdo->query("
    SELECT
        p.*,
        u.name as user_name,
        u.email as user_email
    FROM posts p
    JOIN users u ON p.user_id = u.id
");
$posts = $stmt->fetchAll();

foreach ($posts as $post) {
    echo $post['user_name'];  // No N+1, but column conflicts possible
}
```

**Option 3: Multiple JOINs (gets messy)**

```php
// Get posts with users AND tags - very complex!
$stmt = $pdo->query("
    SELECT
        p.*,
        u.name as user_name,
        GROUP_CONCAT(t.name) as tag_names
    FROM posts p
    JOIN users u ON p.user_id = u.id
    LEFT JOIN post_tag pt ON p.id = pt.post_id
    LEFT JOIN tags t ON pt.tag_id = t.id
    GROUP BY p.id
");
```

### Eloquent Approach

```php
$posts = Post::with(['user', 'tags'])->get();

foreach ($posts as $post) {
    echo $post->user->name;
    echo $post->tags->pluck('name')->implode(', ');
}
```

**Much cleaner and automatic!**

---

## Real-World Example: Blog Post List

### Bad (N+1 Queries)

```php
public function index()
{
    $posts = Post::latest()->paginate(10);

    return view('posts.index', compact('posts'));
}
```

**In the view:**
```blade
@foreach ($posts as $post)
    <h2>{{ $post->title }}</h2>
    <p>By {{ $post->user->name }}</p>  <!-- N+1! -->
    <p>{{ $post->comments->count() }} comments</p>  <!-- Another N+1! -->
    <p>Tags: {{ $post->tags->pluck('name')->implode(', ') }}</p>  <!-- Another N+1! -->
@endforeach
```

**Queries executed:**
- 1 to get posts
- 10 to get each post's user
- 10 to get each post's comments
- 10 to get each post's tags

**Total: 31 queries for 10 posts!**

### Good (Eager Loading)

```php
public function index()
{
    $posts = Post::with(['user', 'comments', 'tags'])
        ->latest()
        ->paginate(10);

    return view('posts.index', compact('posts'));
}
```

**Queries executed:**
- 1 to get posts
- 1 to get all users
- 1 to get all comments
- 1 to get all tags

**Total: 4 queries regardless of posts count!**

### Even Better (With Counts)

```php
public function index()
{
    $posts = Post::with(['user', 'tags'])
        ->withCount('comments')
        ->latest()
        ->paginate(10);

    return view('posts.index', compact('posts'));
}
```

**In the view:**
```blade
@foreach ($posts as $post)
    <h2>{{ $post->title }}</h2>
    <p>By {{ $post->user->name }}</p>
    <p>{{ $post->comments_count }} comments</p>  <!-- No loading all comments! -->
    <p>Tags: {{ $post->tags->pluck('name')->implode(', ') }}</p>
@endforeach
```

**Total: 3 queries (don't need to load comments, just count them)**

---

## When to Use Eager Loading

### Always Use When:

1. **Looping through results and accessing relationships**
   ```php
   foreach ($posts as $post) {
       echo $post->user->name;  // Eager load 'user'!
   }
   ```

2. **Displaying lists with related data**
   ```php
   // Blog post index
   Post::with('user')->paginate(10);
   ```

3. **API responses with relationships**
   ```php
   return Post::with(['user', 'comments'])->get();
   ```

### Don't Need When:

1. **Not accessing relationships**
   ```php
   $posts = Post::all();
   // Just using post data, not relationships
   ```

2. **Single record where you know you'll access relationship**
   ```php
   $post = Post::find(1);
   echo $post->user->name;  // Only 2 queries total, acceptable
   ```

3. **Very few records**
   ```php
   // Only 3 posts, not worth optimizing
   $posts = Post::take(3)->get();
   ```

---

## Advanced: Eager Loading Conditionally

Load relationships only when needed:

```php
// Load 'user' only if not already loaded
$posts = Post::all();

if (!$posts->first()->relationLoaded('user')) {
    $posts->load('user');
}

// Or use when()
$posts = Post::when($needUser, function ($query) {
    return $query->with('user');
})->get();
```

---

## Relationship Caching

Once loaded, relationships are cached on the model instance:

```php
$post = Post::with('user')->find(1);

// First access - uses eager loaded data
$user1 = $post->user;

// Second access - uses cached data, no query
$user2 = $post->user;

// Refresh relationship
$post->load('user');

// Or refresh entire model
$post->refresh();
```

---

## Common Patterns

### Pattern 1: Global Eager Loading

If you **always** need a relationship, eager load it automatically:

```php
class Post extends Model
{
    protected $with = ['user'];  // Always eager load user
}

// Now this automatically includes user
$posts = Post::all();
```

**Disable for specific query:**
```php
$posts = Post::without('user')->get();
```

### Pattern 2: Conditional Eager Loading

```php
$query = Post::query();

if (request('include_user')) {
    $query->with('user');
}

if (request('include_comments')) {
    $query->with('comments');
}

$posts = $query->get();
```

### Pattern 3: Controller-Level Optimization

```php
class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user:id,name', 'tags:id,name'])
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        $post->load(['comments.user', 'tags']);

        return view('posts.show', compact('post'));
    }
}
```

---

## Performance Comparison

Let's measure the difference:

### Without Eager Loading (N+1)

```php
$startTime = microtime(true);

$posts = Post::take(100)->get();

foreach ($posts as $post) {
    $post->user->name;
    $post->comments->count();
}

$endTime = microtime(true);
echo "Time: " . ($endTime - $startTime) . " seconds";
// Result: ~1.5 seconds, 201 queries
```

### With Eager Loading

```php
$startTime = microtime(true);

$posts = Post::with(['user', 'comments'])->take(100)->get();

foreach ($posts as $post) {
    $post->user->name;
    $post->comments->count();
}

$endTime = microtime(true);
echo "Time: " . ($endTime - $startTime) . " seconds";
// Result: ~0.05 seconds, 3 queries
```

**30x faster!**

---

## Lazy Loading vs Eager Loading

| Aspect | Lazy Loading | Eager Loading |
|--------|--------------|---------------|
| **Syntax** | `$post->user` | `Post::with('user')` |
| **Queries** | N+1 (one per access) | 2 (one for posts, one for users) |
| **When to Use** | Single record | Multiple records |
| **Memory** | Lower (loads as needed) | Higher (loads everything) |
| **Performance** | Slower for lists | Faster for lists |

---

## Practice Exercises

### Exercise 1: Identify and Fix N+1

Given this code, identify N+1 problems and fix them:

```php
// List all users with their post count
$users = User::all();

foreach ($users as $user) {
    echo "{$user->name} has {$user->posts->count()} posts";
}

// Show post with comments
$post = Post::find(1);

foreach ($post->comments as $comment) {
    echo "{$comment->user->name}: {$comment->content}";
}

// Dashboard
$user = auth()->user();
$postCount = $user->posts->count();
$commentCount = $user->comments->count();
```

### Exercise 2: Optimize Blog Controller

Optimize this controller to minimize queries:

```php
public function index()
{
    $posts = Post::latest()->paginate(10);
    return view('posts.index', compact('posts'));
}
```

View shows: author name, comment count, tag names, published date

### Exercise 3: Complex Eager Loading

Load posts with:
- Author (only id and name)
- Approved comments (only last 5, with commenter name)
- Tags (alphabetically sorted)
- Comment count
- Average rating

---

## Common Mistakes

### 1. Forgetting to Eager Load

```php
// Bad
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // N+1!
}

// Good
$posts = Post::with('user')->get();
```

### 2. Eager Loading Everything

```php
// Bad - loads unnecessary data
$post = Post::with(['user', 'comments', 'tags', 'likes'])->find(1);
echo $post->title;  // Only needed title!

// Good - load only what you need
$post = Post::find(1);
```

### 3. Using Collection Methods Instead of Query

```php
// Bad - loads all posts into memory, then filters
$posts = Post::with('user')->get()->where('status', 'published');

// Good - filters in database
$posts = Post::with('user')->where('status', 'published')->get();
```

### 4. Not Using withCount

```php
// Bad - loads all comments just to count
$posts = Post::with('comments')->get();
echo $posts->first()->comments->count();

// Good - counts in database
$posts = Post::withCount('comments')->get();
echo $posts->first()->comments_count;
```

---

## Quick Reference

```php
// Eager load one
Post::with('user')->get();

// Eager load multiple
Post::with(['user', 'comments', 'tags'])->get();

// Nested eager load
Post::with('comments.user')->get();

// Constrained eager load
Post::with(['comments' => fn($q) => $q->where('status', 'approved')])->get();

// Lazy eager load
$posts->load('user');

// Count
Post::withCount('comments')->get();

// Count with constraint
Post::withCount(['comments as approved' => fn($q) => $q->where('approved', true)])->get();

// Sum/Avg/Min/Max
Post::withSum('comments', 'votes')->get();
Post::withAvg('reviews', 'rating')->get();

// Prevent lazy loading
Model::preventLazyLoading(! app()->isProduction());
```

---

## What's Next?

You now know how to write efficient Eloquent queries! But there's more to learn about organizing and reusing query logic.

**In the next lesson**, you'll learn about **Query Scopes**:
- Local scopes for reusable query logic
- Global scopes for automatic filtering
- Dynamic scopes with parameters
- Organizing complex queries
- Making your models cleaner and more maintainable

Query scopes are like functions for your queries - let's learn how to use them!

---

## Key Takeaways

1. **N+1 problem = 1 + N queries** - Happens when accessing relationships in loops
2. **Use with() for eager loading** - Loads all related data in 2 queries
3. **Use withCount() for counts** - Don't load data just to count it
4. **Nest with dot notation** - `comments.user` loads nested relationships
5. **Enable query logging** - Use Debugbar to detect N+1
6. **Prevent lazy loading in dev** - Catch N+1 problems early
7. **Eloquent makes it easy** - PDO required complex JOINs
8. **30x+ performance improvement** - Eager loading is crucial for production

---

**Next Lesson:** [05 - Query Scopes](./05-query-scopes.md)
