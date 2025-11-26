# Lesson 01 - Introduction to Eloquent ORM

## Remember PDO?

In Module 06, every database operation required writing SQL:

```php
// Get all posts
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

// Get one post
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([1]);
$post = $stmt->fetch();

// Create a post
$stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
$stmt->execute(['My Title', 'Content here', 1]);

// Update a post
$stmt = $pdo->prepare("UPDATE posts SET title = ? WHERE id = ?");
$stmt->execute(['New Title', 1]);
```

**It works, but:**
- You write a lot of repetitive SQL
- You work with arrays, not objects
- Relationships require manual JOINs
- Validation happens separately
- Timestamps must be managed manually

**With Eloquent, all of this becomes elegant and simple.**

---

## What is Eloquent?

**Eloquent** is Laravel's ORM (Object-Relational Mapping) - it lets you interact with your database using **objects and methods** instead of writing SQL.

Think of it as a **translator** between your PHP objects and database tables:

```
PHP Object        Eloquent         Database
   Post      <------------>      posts table
  $post->title                   title column
  $post->save()                  INSERT/UPDATE
```

### The Active Record Pattern

Eloquent uses the **Active Record pattern**: each model class represents a database table, and each model instance represents a row.

```php
// PDO - Arrays and SQL
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([1]);
$post = $stmt->fetch();
echo $post['title'];

// Eloquent - Objects and methods
$post = Post::find(1);
echo $post->title;
```

**Same result, much cleaner code!**

---

## Your First Eloquent Model

### Creating a Model

```bash
php artisan make:model Post
```

This creates `app/Models/Post.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // That's it! Eloquent handles everything else
}
```

**By convention, Eloquent assumes:**
- Model name: `Post` (singular, capitalized)
- Table name: `posts` (plural, lowercase)
- Primary key: `id`
- Timestamps: `created_at` and `updated_at` columns exist

### If Your Table Doesn't Follow Conventions

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'blog_posts';           // Custom table name
    protected $primaryKey = 'post_id';         // Custom primary key
    public $timestamps = false;                 // No timestamps

    // Or custom timestamp columns
    const CREATED_AT = 'creation_date';
    const UPDATED_AT = 'last_update';
}
```

---

## Basic CRUD with Eloquent

### Create (INSERT)

**PDO way:**
```php
$stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
$stmt->execute(['My Title', 'Content', 1]);
$id = $pdo->lastInsertId();
```

**Eloquent way:**
```php
$post = new Post();
$post->title = 'My Title';
$post->content = 'Content';
$post->user_id = 1;
$post->save();

echo $post->id;  // Automatically available
```

**Or even shorter:**
```php
$post = Post::create([
    'title' => 'My Title',
    'content' => 'Content',
    'user_id' => 1
]);
```

### Read (SELECT)

**PDO way:**
```php
// Get all
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll();

// Get one by ID
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([1]);
$post = $stmt->fetch();

// Get with where clause
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ?");
$stmt->execute([1]);
$posts = $stmt->fetchAll();
```

**Eloquent way:**
```php
// Get all
$posts = Post::all();

// Get one by ID
$post = Post::find(1);

// Get with where clause
$posts = Post::where('user_id', 1)->get();

// Get first match or fail
$post = Post::where('slug', 'my-post')->firstOrFail();
```

### Update (UPDATE)

**PDO way:**
```php
$stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
$stmt->execute(['New Title', 'New Content', 1]);
```

**Eloquent way:**
```php
$post = Post::find(1);
$post->title = 'New Title';
$post->content = 'New Content';
$post->save();
```

**Or update multiple records:**
```php
Post::where('user_id', 1)->update(['status' => 'published']);
```

### Delete (DELETE)

**PDO way:**
```php
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([1]);
```

**Eloquent way:**
```php
$post = Post::find(1);
$post->delete();

// Or delete by ID directly
Post::destroy(1);

// Or delete multiple
Post::destroy([1, 2, 3]);

// Or delete with where
Post::where('user_id', 1)->delete();
```

---

## Query Builder Methods

Eloquent provides a fluent interface for building queries:

### Where Clauses

```php
// Simple where
Post::where('status', 'published')->get();

// Multiple conditions
Post::where('status', 'published')
    ->where('user_id', 1)
    ->get();

// Or conditions
Post::where('status', 'published')
    ->orWhere('status', 'draft')
    ->get();

// Comparison operators
Post::where('views', '>', 1000)->get();
Post::where('title', 'like', '%Laravel%')->get();

// Where in array
Post::whereIn('status', ['published', 'featured'])->get();

// Where between
Post::whereBetween('created_at', ['2024-01-01', '2024-12-31'])->get();

// Where null
Post::whereNull('deleted_at')->get();
Post::whereNotNull('published_at')->get();
```

### Ordering

```php
// Order by column
Post::orderBy('created_at', 'desc')->get();

// Multiple order by
Post::orderBy('status', 'asc')
    ->orderBy('created_at', 'desc')
    ->get();

// Latest and oldest shortcuts
Post::latest()->get();          // orderBy('created_at', 'desc')
Post::oldest()->get();          // orderBy('created_at', 'asc')
```

### Limiting

```php
// Take first N records
Post::take(10)->get();

// Skip and take (pagination)
Post::skip(10)->take(10)->get();

// Limit (alias for take)
Post::limit(5)->get();
```

### Selecting Columns

```php
// Select specific columns
Post::select('id', 'title', 'created_at')->get();

// Add columns to selection
Post::select('id', 'title')->addSelect('content')->get();
```

### Aggregates

```php
// Count
$count = Post::count();
$publishedCount = Post::where('status', 'published')->count();

// Sum, avg, min, max
$totalViews = Post::sum('views');
$avgViews = Post::avg('views');
$maxViews = Post::max('views');
$minViews = Post::min('views');
```

---

## Retrieving Results

### Get All Records

```php
$posts = Post::all();
// Returns: Illuminate\Database\Eloquent\Collection
```

### Get Filtered Records

```php
$posts = Post::where('status', 'published')->get();
```

### Get One Record

```php
// By primary key
$post = Post::find(1);
// Returns: Post instance or null

// First match
$post = Post::where('slug', 'my-post')->first();
// Returns: Post instance or null

// First or fail (throws 404 if not found)
$post = Post::findOrFail(1);
$post = Post::where('slug', 'my-post')->firstOrFail();

// First or create
$post = Post::firstOrCreate(
    ['slug' => 'my-post'],
    ['title' => 'My Post', 'content' => 'Content']
);
```

### Chunking Large Results

When working with thousands of records:

**PDO way:**
```php
// Memory intensive - loads everything
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll();
```

**Eloquent way:**
```php
// Process in chunks of 100
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process each post
        echo $post->title . "\n";
    }
});
```

### Cursor (Lazy Loading)

For even better memory management:

```php
foreach (Post::cursor() as $post) {
    // Only one record in memory at a time
    echo $post->title;
}
```

---

## Collections vs Arrays

### PDO Returns Arrays

```php
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll();  // Plain PHP array

// Limited array functions
$titles = array_map(fn($post) => $post['title'], $posts);
$published = array_filter($posts, fn($post) => $post['status'] === 'published');
```

### Eloquent Returns Collections

```php
$posts = Post::all();  // Illuminate\Database\Eloquent\Collection

// Powerful collection methods
$titles = $posts->pluck('title');
$published = $posts->where('status', 'published');
$groups = $posts->groupBy('user_id');
$sorted = $posts->sortBy('title');
```

Collections provide 100+ helpful methods:

```php
$posts = Post::all();

// Get titles only
$titles = $posts->pluck('title');
// ['Post 1', 'Post 2', 'Post 3']

// Filter
$published = $posts->filter(function ($post) {
    return $post->status === 'published';
});

// Map
$titles = $posts->map(function ($post) {
    return strtoupper($post->title);
});

// Group by user
$byUser = $posts->groupBy('user_id');

// Check if empty
if ($posts->isEmpty()) {
    echo "No posts";
}

// Count
echo $posts->count();

// First and last
$first = $posts->first();
$last = $posts->last();

// Convert to array
$array = $posts->toArray();
```

---

## Automatic Timestamps

### PDO Way

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

You had to manage timestamps in SQL or manually in PHP:

```php
$stmt = $pdo->prepare("INSERT INTO posts (title, created_at, updated_at) VALUES (?, NOW(), NOW())");
$stmt->execute(['My Title']);

$stmt = $pdo->prepare("UPDATE posts SET title = ?, updated_at = NOW() WHERE id = ?");
$stmt->execute(['New Title', 1]);
```

### Eloquent Way

Eloquent automatically manages `created_at` and `updated_at`:

```php
$post = new Post();
$post->title = 'My Title';
$post->save();
// created_at and updated_at automatically set!

$post->title = 'New Title';
$post->save();
// updated_at automatically updated!
```

**Disable timestamps if you don't need them:**

```php
class Post extends Model
{
    public $timestamps = false;
}
```

---

## Model Events

Eloquent fires events during the model lifecycle:

```php
class Post extends Model
{
    protected static function boot()
    {
        parent::boot();

        // Before creating
        static::creating(function ($post) {
            $post->slug = Str::slug($post->title);
        });

        // After creating
        static::created(function ($post) {
            // Send notification, log, etc.
        });

        // Before updating
        static::updating(function ($post) {
            $post->slug = Str::slug($post->title);
        });

        // Before deleting
        static::deleting(function ($post) {
            // Delete related records
            $post->comments()->delete();
        });
    }
}
```

**Available events:**
- `creating`, `created`
- `updating`, `updated`
- `saving`, `saved` (fires for both create and update)
- `deleting`, `deleted`
- `restoring`, `restored` (soft deletes)

---

## Comparing PDO and Eloquent

| Operation | PDO | Eloquent |
|-----------|-----|----------|
| **Get all** | `$pdo->query("SELECT * FROM posts")->fetchAll()` | `Post::all()` |
| **Get by ID** | `$pdo->prepare("SELECT * FROM posts WHERE id = ?")->execute([1])` | `Post::find(1)` |
| **Where clause** | `$pdo->prepare("SELECT * FROM posts WHERE status = ?")->execute(['published'])` | `Post::where('status', 'published')->get()` |
| **Insert** | `$pdo->prepare("INSERT INTO posts (title) VALUES (?)")->execute(['Title'])` | `Post::create(['title' => 'Title'])` |
| **Update** | `$pdo->prepare("UPDATE posts SET title = ? WHERE id = ?")->execute(['New', 1])` | `$post->update(['title' => 'New'])` |
| **Delete** | `$pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([1])` | `Post::destroy(1)` |
| **Count** | `$pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn()` | `Post::count()` |
| **Relationships** | Manual JOINs | `$post->user`, `$post->comments` |
| **Timestamps** | Manual | Automatic |

---

## Creating the Migration

Before using a model, you need a table. Create a migration:

```bash
php artisan make:migration create_posts_table
```

Edit `database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

---

## A Complete Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // Allow mass assignment for these fields
    protected $fillable = ['title', 'content', 'status', 'user_id'];

    // Or protect specific fields from mass assignment
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Cast attributes to native types
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    // Default values
    protected $attributes = [
        'status' => 'draft',
    ];
}
```

Using the model:

```php
// Create
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is amazing!',
    'user_id' => auth()->id(),
]);

// Read
$posts = Post::where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->take(10)
    ->get();

$post = Post::findOrFail(1);

// Update
$post->update(['status' => 'published']);

// Delete
$post->delete();

// Count
$count = Post::where('status', 'draft')->count();
```

---

## The Benefits of Eloquent

### 1. Readable Code

**PDO:**
```php
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = ? AND user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->execute(['published', $userId]);
$posts = $stmt->fetchAll();
```

**Eloquent:**
```php
$posts = Post::where('status', 'published')
    ->where('user_id', $userId)
    ->latest()
    ->take(10)
    ->get();
```

### 2. Type Safety

**PDO:**
```php
$post = $stmt->fetch();
echo $post['title'];  // Might not exist, no autocomplete
```

**Eloquent:**
```php
$post = Post::find(1);
echo $post->title;    // IDE knows this property exists
```

### 3. Automatic Timestamps

No need to manually manage `created_at` and `updated_at`.

### 4. Relationships Made Easy

**PDO:**
```php
$stmt = $pdo->prepare("SELECT u.* FROM users u JOIN posts p ON u.id = p.user_id WHERE p.id = ?");
$stmt->execute([1]);
$user = $stmt->fetch();
```

**Eloquent:**
```php
$user = Post::find(1)->user;
```

### 5. Built-in Validation & Events

Hook into model lifecycle for automatic slug generation, notifications, etc.

### 6. Collections

Powerful methods for manipulating result sets.

---

## When to Use Raw SQL

Eloquent is amazing, but sometimes raw SQL is better:

### Complex Queries

```php
$posts = DB::select('
    SELECT p.*, COUNT(c.id) as comment_count
    FROM posts p
    LEFT JOIN comments c ON p.id = c.post_id
    GROUP BY p.id
    HAVING comment_count > 10
');
```

### Performance-Critical Operations

```php
// Instead of
Post::where('user_id', 1)->update(['status' => 'published']);

// For millions of records, raw might be faster
DB::update('UPDATE posts SET status = ? WHERE user_id = ?', ['published', 1]);
```

**But 95% of the time, Eloquent is the right choice!**

---

## Practice Exercises

### Exercise 1: Your First Model

1. Create a `Task` model and migration
2. Add columns: `title`, `description`, `is_completed`, `user_id`
3. Create tasks using Eloquent
4. List all tasks
5. Mark tasks as completed
6. Delete completed tasks

### Exercise 2: Query Builder Practice

1. Get all published posts
2. Get posts created in the last 7 days
3. Get posts with more than 100 views
4. Get the 5 most recent posts
5. Count draft posts
6. Find post by slug or fail

### Exercise 3: Collections

1. Get all posts
2. Get only titles using `pluck()`
3. Group posts by status
4. Filter posts with title length > 20
5. Get the first and last post
6. Check if any post is featured

---

## Common Mistakes

### 1. Forgetting to Call get()

```php
// Wrong - returns query builder, not results
$posts = Post::where('status', 'published');

// Right - returns collection of posts
$posts = Post::where('status', 'published')->get();
```

### 2. Using find() Without Checking Null

```php
// Might be null
$post = Post::find(1);
echo $post->title;  // Error if null

// Better - throws 404 if not found
$post = Post::findOrFail(1);
```

### 3. Not Understanding Mass Assignment

```php
// Error: title is not fillable
$post = Post::create(['title' => 'My Post']);

// Fix: Add to $fillable
protected $fillable = ['title', 'content'];
```

### 4. Inefficient Queries (N+1 Problem)

We'll cover this in depth in Lesson 04, but be aware:

```php
// Bad - queries database for each post's user
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // N+1 queries!
}

// Good - eager load users
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;  // Only 2 queries total!
}
```

---

## Quick Reference

```php
// Create
$post = Post::create(['title' => 'Title']);
$post = new Post(['title' => 'Title']);
$post->save();

// Read
Post::all();
Post::find(1);
Post::findOrFail(1);
Post::where('status', 'published')->get();
Post::first();

// Update
$post->update(['title' => 'New Title']);
$post->title = 'New Title';
$post->save();

// Delete
$post->delete();
Post::destroy(1);
Post::where('status', 'draft')->delete();

// Query
Post::where('status', 'published')->get();
Post::orderBy('created_at', 'desc')->get();
Post::take(10)->get();
Post::count();

// Collections
$posts->pluck('title');
$posts->where('status', 'published');
$posts->first();
$posts->count();
```

---

## What's Next?

You've learned the basics of Eloquent! But we've only scratched the surface.

**In the next lesson**, you'll dive deep into **CRUD operations** with Eloquent, learning:
- Mass assignment protection
- Different ways to create records
- Advanced querying
- Updating multiple records at once
- Soft deletes (deleting without actually deleting!)

You'll also build a complete blog CRUD system using Eloquent.

---

## Key Takeaways

1. **Eloquent = ORM** - Maps database tables to PHP objects
2. **Active Record pattern** - Each model represents a table, each instance represents a row
3. **Convention over configuration** - Model `Post` maps to table `posts` automatically
4. **Query builder is fluent** - Chain methods for readable queries
5. **Collections are powerful** - Much better than arrays
6. **Timestamps are automatic** - No manual NOW() needed
7. **Eloquent handles 95% of queries** - Only use raw SQL when necessary
8. **compare with PDO** - Appreciate how much simpler Eloquent is!

---

**Next Lesson:** [02 - CRUD Operations with Eloquent](./02-crud-eloquent.md)
