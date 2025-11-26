# Lesson 08 - Models Introduction

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What are Models?

**Models** represent database tables and handle all database interactions.

### MVC Review

```
Controller → Model → Database
         ←         ←
```

**Model's job:**
- Interact with database
- Define relationships
- Contain business logic related to data

### Pure PHP Approach (Module 06)

**Every time you needed data:**

```php
// Get all posts
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get one post
$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// Create post
$sql = "INSERT INTO posts (title, content, created_at) VALUES (:title, :content, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $title,
    'content' => $content
]);

// Update post
$sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $title,
    'content' => $content,
    'id' => $id
]);

// Delete post
$sql = "DELETE FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
```

**Repetitive, error-prone, tedious!**

### Laravel Eloquent ORM

**Eloquent** is Laravel's ORM (Object-Relational Mapping) - it maps database tables to PHP objects.

```php
// Get all posts
$posts = Post::all();

// Get one post
$post = Post::find($id);

// Create post
$post = Post::create([
    'title' => $title,
    'content' => $content
]);

// Update post
$post->update([
    'title' => $title,
    'content' => $content
]);

// Delete post
$post->delete();
```

**Simple, readable, powerful!**

---

## Creating Models

### Using Artisan

```bash
php artisan make:model Post
```

Creates: `/app/Models/Post.php`

**With migration:**
```bash
php artisan make:model Post -m
```

**With migration and controller:**
```bash
php artisan make:model Post -mc
```

**With everything (migration, controller, factory, seeder):**
```bash
php artisan make:model Post -a
```

### Model Structure

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
}
```

**That's it!** A basic model is just a class extending `Model`.

---

## Model Conventions

**Laravel follows conventions** to minimize configuration.

### Table Name

**Convention:** Plural, snake_case of model name

| Model | Table |
|-------|-------|
| Post | posts |
| User | users |
| BlogPost | blog_posts |
| Category | categories |

**Override if needed:**
```php
class Post extends Model
{
    protected $table = 'my_posts';
}
```

### Primary Key

**Convention:** Column named `id`

**Override if needed:**
```php
protected $primaryKey = 'post_id';
```

**Non-incrementing primary key (UUID):**
```php
public $incrementing = false;
protected $keyType = 'string';
```

### Timestamps

**Convention:** `created_at` and `updated_at` columns

**Laravel automatically manages these!**

**Disable if not using:**
```php
public $timestamps = false;
```

**Custom timestamp columns:**
```php
const CREATED_AT = 'creation_date';
const UPDATED_AT = 'updated_date';
```

---

## Basic Eloquent Operations

### Retrieving Records

**All records:**
```php
$posts = Post::all();

foreach ($posts as $post) {
    echo $post->title;
}
```

**Pure PHP equivalent:**
```php
$sql = "SELECT * FROM posts";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post) {
    echo $post['title'];
}
```

**Find by primary key:**
```php
$post = Post::find(1);  // Returns model or null

echo $post->title;
echo $post->content;
```

**Find or fail (throws 404 if not found):**
```php
$post = Post::findOrFail(1);  // Returns model or 404 error
```

**Find multiple by primary keys:**
```php
$posts = Post::find([1, 2, 3]);
```

**First record:**
```php
$post = Post::first();
```

**First or fail:**
```php
$post = Post::firstOrFail();
```

### Querying Records

**Where clauses:**
```php
// Simple where
$posts = Post::where('published', true)->get();

// Multiple conditions
$posts = Post::where('published', true)
    ->where('views', '>', 100)
    ->get();

// Or where
$posts = Post::where('status', 'published')
    ->orWhere('featured', true)
    ->get();
```

**Pure PHP equivalent:**
```php
$sql = "SELECT * FROM posts WHERE published = 1 AND views > 100";
$stmt = $pdo->query($sql);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**Order by:**
```php
$posts = Post::orderBy('created_at', 'desc')->get();
$posts = Post::latest()->get();  // Shortcut for orderBy('created_at', 'desc')
$posts = Post::oldest()->get();  // Shortcut for orderBy('created_at', 'asc')
```

**Limit:**
```php
$posts = Post::take(5)->get();  // First 5
$posts = Post::limit(5)->get();  // Same thing
```

**Pagination:**
```php
$posts = Post::paginate(10);  // 10 per page
```

**In view:**
```blade
@foreach($posts as $post)
    <h2>{{ $post->title }}</h2>
@endforeach

{{ $posts->links() }}  <!-- Pagination links -->
```

**Count:**
```php
$count = Post::count();
$publishedCount = Post::where('published', true)->count();
```

**Exists:**
```php
if (Post::where('slug', $slug)->exists()) {
    // Slug already taken
}
```

### Creating Records

**Method 1: Create and save:**
```php
$post = new Post();
$post->title = 'My Post';
$post->content = 'Post content';
$post->save();
```

**Method 2: Mass assignment:**
```php
$post = Post::create([
    'title' => 'My Post',
    'content' => 'Post content',
]);
```

**Method 3: First or create:**
```php
$post = Post::firstOrCreate(
    ['slug' => 'my-post'],  // Search conditions
    ['title' => 'My Post', 'content' => 'Content']  // Additional data if creating
);
```

**Method 4: Update or create:**
```php
$post = Post::updateOrCreate(
    ['slug' => 'my-post'],  // Search conditions
    ['title' => 'Updated Title', 'content' => 'Updated content']
);
```

**Pure PHP equivalent:**
```php
$sql = "INSERT INTO posts (title, content, created_at, updated_at)
        VALUES (:title, :content, NOW(), NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $title,
    'content' => $content
]);
$postId = $pdo->lastInsertId();
```

### Updating Records

**Method 1: Find and update:**
```php
$post = Post::find(1);
$post->title = 'Updated Title';
$post->save();
```

**Method 2: Mass update:**
```php
$post = Post::find(1);
$post->update([
    'title' => 'Updated Title',
    'content' => 'Updated content',
]);
```

**Method 3: Update without retrieving:**
```php
Post::where('views', '<', 10)->update(['published' => false]);
```

**Pure PHP equivalent:**
```php
$sql = "UPDATE posts SET title = :title, updated_at = NOW() WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $title,
    'id' => $id
]);
```

### Deleting Records

**Method 1: Find and delete:**
```php
$post = Post::find(1);
$post->delete();
```

**Method 2: Delete by primary key:**
```php
Post::destroy(1);
Post::destroy([1, 2, 3]);  // Multiple
```

**Method 3: Delete with query:**
```php
Post::where('views', '<', 10)->delete();
```

**Pure PHP equivalent:**
```php
$sql = "DELETE FROM posts WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id]);
```

---

## Mass Assignment

### The Problem

```php
// Dangerous - accepts ALL user input
$post = Post::create($request->all());
```

**What if user sends:**
```
title=My Post
content=Great content
user_id=999    // Impersonate another user!
is_admin=1     // Make themselves admin!
```

### The Solution: Fillable or Guarded

**Option 1: Fillable (whitelist):**
```php
class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'published',
    ];
}
```

**Only these fields can be mass-assigned.**

**Option 2: Guarded (blacklist):**
```php
class Post extends Model
{
    protected $guarded = [
        'id',
        'user_id',
    ];
}
```

**All fields EXCEPT these can be mass-assigned.**

**Option 3: Guard nothing (dangerous!):**
```php
protected $guarded = [];
```

**Use only if you control the input!**

### Usage

```php
// Safe - only fillable fields are used
$post = Post::create($request->all());

// Also safe
$post = Post::create([
    'title' => $request->title,
    'content' => $request->content,
    'user_id' => auth()->id(),  // Controlled by you
]);
```

**Pure PHP equivalent:**
```php
// Had to manually whitelist fields
$sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'title' => $_POST['title'],
    'content' => $_POST['content']
    // Can't accidentally include user_id or is_admin
]);
```

---

## Accessors and Mutators

**Transform attributes when getting or setting.**

### Accessors (Getters)

**Modify data when retrieving:**

```php
class User extends Model
{
    // Get full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Uppercase email
    public function getEmailAttribute($value)
    {
        return strtoupper($value);
    }
}
```

**Usage:**
```php
$user = User::find(1);
echo $user->full_name;  // "John Doe"
echo $user->email;      // "JOHN@EXAMPLE.COM"
```

**Pattern:** `get{AttributeName}Attribute`

### Mutators (Setters)

**Modify data when setting:**

```php
class User extends Model
{
    // Automatically hash passwords
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    // Lowercase email
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}
```

**Usage:**
```php
$user = User::create([
    'email' => 'JOHN@EXAMPLE.COM',  // Stored as: john@example.com
    'password' => 'secret123',       // Stored as: $2y$10$...
]);
```

**Pattern:** `set{AttributeName}Attribute`

### Modern Syntax (Laravel 9+)

**Using attributes:**

```php
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Model
{
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->first_name . ' ' . $this->last_name,
        );
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => bcrypt($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
            set: fn ($value) => strtolower($value),
        );
    }
}
```

---

## Casting Attributes

**Automatically convert database values to PHP types.**

```php
class Post extends Model
{
    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'views' => 'integer',
        'options' => 'array',  // JSON to array
        'metadata' => 'object',  // JSON to object
    ];
}
```

**Usage:**
```php
$post = Post::find(1);

// Boolean (not 1 or 0)
if ($post->published) {
    echo 'Published!';
}

// DateTime object (not string)
echo $post->published_at->format('F j, Y');
echo $post->published_at->diffForHumans();  // "2 days ago"

// Array (JSON decoded)
$post->options['color'] = 'blue';
$post->save();  // Automatically JSON encoded
```

**Pure PHP equivalent:**
```php
$published = (bool) $row['published'];
$publishedAt = new DateTime($row['published_at']);
$options = json_decode($row['options'], true);
```

---

## Query Scopes

**Reusable query constraints.**

### Local Scopes

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopePopular($query, $threshold = 100)
    {
        return $query->where('views', '>', $threshold);
    }
}
```

**Usage:**
```php
// Get published posts
$posts = Post::published()->get();

// Chain scopes
$posts = Post::published()->featured()->get();

// With parameter
$posts = Post::popular(500)->get();

// Combine with other queries
$posts = Post::published()
    ->orderBy('views', 'desc')
    ->take(10)
    ->get();
```

**Pure PHP equivalent:**
```php
// Repeating this everywhere
$sql = "SELECT * FROM posts WHERE published = 1 AND featured = 1";
```

**Pattern:** `scope{ScopeName}`

### Global Scopes

**Apply to ALL queries automatically.**

```php
class Post extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('published', function ($query) {
            $query->where('published', true);
        });
    }
}
```

**Now:**
```php
Post::all();  // Only published posts
Post::where('featured', true)->get();  // Only published AND featured
```

**Remove global scope:**
```php
Post::withoutGlobalScope('published')->get();  // All posts
```

---

## Soft Deletes

**"Delete" records without actually removing them from database.**

### Setup

**In migration:**
```php
$table->softDeletes();  // Adds deleted_at column
```

**In model:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

### Usage

```php
// "Delete" post (sets deleted_at to now)
$post->delete();

// Query only non-deleted
$posts = Post::all();  // Excludes soft-deleted

// Include soft-deleted
$posts = Post::withTrashed()->get();

// Only soft-deleted
$posts = Post::onlyTrashed()->get();

// Restore soft-deleted
$post = Post::withTrashed()->find(1);
$post->restore();

// Permanently delete
$post->forceDelete();

// Check if soft-deleted
if ($post->trashed()) {
    echo 'Post is deleted';
}
```

**Use case:** User accounts, posts, orders - where you need "undo" capability.

---

## Complete Model Example

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'published',
        'published_at',
        'featured',
        'meta_title',
        'meta_description',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'published' => 'boolean',
        'featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Get the post's full URL.
     */
    public function getUrlAttribute()
    {
        return route('posts.show', $this->slug);
    }

    /**
     * Auto-generate slug from title.
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;

        if (!$this->exists) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope for featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    /**
     * Scope for popular posts.
     */
    public function scopePopular($query, $threshold = 100)
    {
        return $query->where('views', '>', $threshold);
    }
}
```

**Usage:**
```php
// Create post with auto-generated slug
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'Great content here',
]);
// slug automatically set to "my-first-post"

// Get URL
echo $post->url;  // https://mysite.com/posts/my-first-post

// Query published posts
$posts = Post::published()->latest()->paginate(10);

// Get popular featured posts
$posts = Post::featured()->popular(500)->get();
```

---

## Summary

**What You Learned:**
- What models are and their role in MVC
- How to create models with Artisan
- Laravel's model conventions (table names, primary keys, timestamps)
- Basic Eloquent operations (CRUD)
- Mass assignment protection (fillable vs guarded)
- Accessors and mutators (getters/setters)
- Attribute casting
- Query scopes (local and global)
- Soft deletes

**Key Takeaways:**
1. **Models represent database tables** - one model per table
2. **Eloquent is powerful** - no raw SQL needed for common operations
3. **Follow conventions** - less configuration required
4. **Protect mass assignment** - use `$fillable` or `$guarded`
5. **Scopes are reusable queries** - DRY principle
6. **Soft deletes preserve data** - "undo" capability
7. **Timestamps are automatic** - Laravel manages created_at/updated_at

**Pure PHP vs Laravel:**
- Pure PHP: Write SQL every time, manual timestamp management
- Laravel: Eloquent methods, automatic timestamps, chainable queries

**Next Lesson:** We'll learn about Request and Response handling in detail!

---

## Practice Exercise

**Create a complete Post model:**

```bash
php artisan make:model Post -m
```

**Define in migration:**
- id, title, slug, content, excerpt, published (boolean), views (integer), published_at (timestamp), timestamps, soft deletes

**In model, add:**
- Mass assignment protection
- Casting (published, published_at)
- Accessor for full URL
- Mutator to auto-generate slug from title
- Scopes: published, featured, popular
- Soft deletes

**Test in Tinker:**
```bash
php artisan tinker

>>> $post = Post::create(['title' => 'Test Post', 'content' => 'Content'])
>>> $post->slug  // Auto-generated
>>> $post->url   // Accessor
>>> Post::published()->count()
>>> $post->delete()  // Soft delete
>>> Post::withTrashed()->count()
```

---

## Quick Quiz

**1. What command creates a model?**
```bash
php artisan make:model Post
```

**2. What's Laravel's table naming convention?**
- Plural, snake_case of model name (Post → posts)

**3. What's the difference between `find()` and `findOrFail()`?**
- `find()` returns null if not found
- `findOrFail()` throws 404 error if not found

**4. What's mass assignment?**
- Creating/updating records with an array of data
- Must protect with `$fillable` or `$guarded`

**5. What's an accessor?**
- A getter that transforms data when retrieving (get{Attribute}Attribute)

**6. What's a mutator?**
- A setter that transforms data when saving (set{Attribute}Attribute)

**7. What does soft delete do?**
- Sets `deleted_at` timestamp instead of actually deleting the record

---

**Next**: [Lesson 09 - Request and Response →](09-request-response.md)
