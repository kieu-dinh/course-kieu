# Lesson 03 - Eloquent Relationships

## Remember JOINs from Module 06?

With PDO, getting related data required manual JOINs:

```php
// Get a post with its author
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name, u.email as author_email
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([1]);
$post = $stmt->fetch();

echo "Post: {$post['title']}";
echo "Author: {$post['author_name']}";
```

**Problems:**
- Manual JOIN syntax every time
- Column name conflicts (which `id`?)
- Getting related collections is complex
- Many-to-many requires junction table queries

**With Eloquent relationships:**

```php
$post = Post::find(1);
echo "Post: {$post->title}";
echo "Author: {$post->user->name}";
```

**That's it!** Eloquent handles the JOIN automatically.

---

## Types of Relationships

Eloquent supports all relationship types:

| Relationship | Example | Eloquent Method |
|--------------|---------|-----------------|
| **One-to-One** | User has one Profile | `hasOne()` / `belongsTo()` |
| **One-to-Many** | User has many Posts | `hasMany()` / `belongsTo()` |
| **Many-to-Many** | Posts have many Tags | `belongsToMany()` |
| **Has Many Through** | Country has many Posts through Users | `hasManyThrough()` |
| **Polymorphic** | Comments belong to Posts or Videos | `morphTo()` / `morphMany()` |

We'll focus on the first three - they cover 95% of use cases.

---

## One-to-Many Relationships

**The most common relationship:** One user has many posts. One post belongs to one user.

### Database Setup

```php
// users migration
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamps();
});

// posts migration
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->text('content');
    $table->timestamps();
});
```

**Note:** `foreignId('user_id')->constrained()` creates the foreign key automatically!

### Defining the Relationship

**User Model (One side - "has many"):**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

**Post Model (Many side - "belongs to"):**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Using the Relationship

**Get user's posts:**

```php
$user = User::find(1);

// Get all posts (returns Collection)
$posts = $user->posts;

foreach ($posts as $post) {
    echo $post->title . "\n";
}

// Access as property or method
$posts = $user->posts;       // Property - executes query
$posts = $user->posts();     // Method - returns query builder

// Chain additional queries
$publishedPosts = $user->posts()
    ->where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->get();
```

**Get post's author:**

```php
$post = Post::find(1);

// Get the user (returns User model)
$author = $post->user;

echo "Author: {$author->name}";
echo "Email: {$author->email}";
```

### Creating Related Records

**Create post for a user:**

```php
$user = User::find(1);

// Method 1: Using relationship
$post = $user->posts()->create([
    'title' => 'My First Post',
    'content' => 'Content here'
]);
// user_id automatically set!

// Method 2: Using save()
$post = new Post([
    'title' => 'Another Post',
    'content' => 'More content'
]);
$user->posts()->save($post);

// Method 3: Save multiple
$user->posts()->saveMany([
    new Post(['title' => 'Post 1', 'content' => 'Content 1']),
    new Post(['title' => 'Post 2', 'content' => 'Content 2']),
]);
```

**PDO comparison:**

```php
// Get user ID
$userId = 1;

// Insert post with user_id
$stmt = $pdo->prepare("INSERT INTO posts (user_id, title, content) VALUES (?, ?, ?)");
$stmt->execute([$userId, 'My Post', 'Content']);
```

### Checking Relationship Existence

```php
// Check if user has posts
$user = User::find(1);
if ($user->posts->isNotEmpty()) {
    echo "User has posts!";
}

// Or using count
if ($user->posts()->count() > 0) {
    echo "User has {$user->posts()->count()} posts";
}

// Query users who have posts
$usersWithPosts = User::has('posts')->get();

// Query users who have at least 5 posts
$activeUsers = User::has('posts', '>=', 5)->get();

// Query users who have published posts
$authors = User::whereHas('posts', function ($query) {
    $query->where('status', 'published');
})->get();
```

---

## Inverse Relationships (BelongsTo)

Every `hasMany` has an inverse `belongsTo`:

```php
class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Usage
$post = Post::find(1);
$author = $post->user;
```

### Custom Foreign Keys

By convention, Eloquent expects:
- Foreign key: `user_id` (model name + _id)
- Local key: `id`

**If your columns are different:**

```php
// Posts table has 'author_id' instead of 'user_id'
class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}

// If parent key is not 'id'
class Post extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }
}
```

---

## One-to-One Relationships

**Less common:** One user has one profile. One profile belongs to one user.

### Database Setup

```php
// profiles migration
Schema::create('profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
    $table->text('bio')->nullable();
    $table->string('avatar')->nullable();
    $table->string('website')->nullable();
    $table->timestamps();
});
```

**Note:** `unique()` ensures one-to-one relationship!

### Defining the Relationship

```php
class User extends Model
{
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model
{
    protected $fillable = ['user_id', 'bio', 'avatar', 'website'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

### Using the Relationship

```php
$user = User::find(1);

// Get profile
$profile = $user->profile;

if ($profile) {
    echo $profile->bio;
}

// Create profile for user
$user->profile()->create([
    'bio' => 'Laravel enthusiast',
    'website' => 'https://example.com'
]);

// Or use save()
$profile = new Profile(['bio' => 'PHP developer']);
$user->profile()->save($profile);
```

---

## Many-to-Many Relationships

**The complex one:** Posts have many tags. Tags have many posts.

Requires a **pivot table** (junction table).

### Database Setup

```php
// posts migration (already exists)
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->timestamps();
});

// tags migration
Schema::create('tags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->timestamps();
});

// post_tag pivot migration (alphabetically ordered: post_tag, not tag_post)
Schema::create('post_tag', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');
    $table->timestamps();

    // Prevent duplicate tags on same post
    $table->unique(['post_id', 'tag_id']);
});
```

**Naming convention:** `{model1}_{model2}` in alphabetical order.

### Defining the Relationship

```php
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}

class Tag extends Model
{
    protected $fillable = ['name'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
```

### Using the Relationship

**Get post's tags:**

```php
$post = Post::find(1);

$tags = $post->tags;

foreach ($tags as $tag) {
    echo $tag->name . " ";
}
```

**Get tag's posts:**

```php
$tag = Tag::where('name', 'Laravel')->first();

$posts = $tag->posts;

foreach ($posts as $post) {
    echo $post->title . "\n";
}
```

**PDO comparison:**

```php
// Get post's tags
$stmt = $pdo->prepare("
    SELECT t.*
    FROM tags t
    JOIN post_tag pt ON t.id = pt.tag_id
    WHERE pt.post_id = ?
");
$stmt->execute([1]);
$tags = $stmt->fetchAll();

// Get tag's posts
$stmt = $pdo->prepare("
    SELECT p.*
    FROM posts p
    JOIN post_tag pt ON p.id = pt.post_id
    JOIN tags t ON pt.tag_id = t.id
    WHERE t.name = ?
");
$stmt->execute(['Laravel']);
$posts = $stmt->fetchAll();
```

### Attaching and Detaching

**Attach tags to a post:**

```php
$post = Post::find(1);

// Attach one tag
$post->tags()->attach(1);  // Tag ID

// Attach multiple tags
$post->tags()->attach([1, 2, 3]);

// Attach with pivot data
$post->tags()->attach(1, ['created_at' => now()]);
```

**Detach tags:**

```php
// Detach one tag
$post->tags()->detach(1);

// Detach multiple
$post->tags()->detach([1, 2, 3]);

// Detach all tags
$post->tags()->detach();
```

**Sync tags (replace all):**

```php
// Replace all tags with these
$post->tags()->sync([1, 2, 3]);

// Sync without detaching (only adds missing)
$post->tags()->syncWithoutDetaching([4, 5]);
```

**Toggle tag:**

```php
// If attached, detach. If detached, attach.
$post->tags()->toggle(1);
$post->tags()->toggle([1, 2, 3]);
```

### Creating Related Records

```php
$post = Post::find(1);

// Create and attach tag
$tag = $post->tags()->create(['name' => 'New Tag']);

// Or find/create and attach
$tag = Tag::firstOrCreate(['name' => 'Laravel']);
$post->tags()->attach($tag->id);
```

### Custom Pivot Table Names

If your pivot table doesn't follow conventions:

```php
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'taggables');
    }
}
```

### Custom Pivot Column Names

```php
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'post_tag',        // Table name
            'post_id',         // Foreign key on pivot
            'tag_id'           // Related key on pivot
        );
    }
}
```

### Accessing Pivot Data

```php
$post = Post::find(1);

foreach ($post->tags as $tag) {
    echo $tag->name;
    echo $tag->pivot->created_at;  // Access pivot table data
}

// Retrieve additional pivot columns
class Post extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withPivot('created_by', 'order')
            ->withTimestamps();
    }
}

// Usage
$post->tags[0]->pivot->created_by;
$post->tags[0]->pivot->created_at;  // Available with withTimestamps()
```

---

## Querying Relationships

### Eager Loading (Prevent N+1 Problem)

**Bad - N+1 queries:**

```php
$posts = Post::all();  // 1 query

foreach ($posts as $post) {
    echo $post->user->name;  // N queries (one per post!)
}
// Total: 1 + N queries
```

**Good - Eager loading:**

```php
$posts = Post::with('user')->get();  // 2 queries only!

foreach ($posts as $post) {
    echo $post->user->name;  // No additional queries
}
```

We'll cover this in detail in Lesson 04.

### Constraining Eager Loads

```php
// Only load published posts
$users = User::with(['posts' => function ($query) {
    $query->where('status', 'published')
          ->orderBy('created_at', 'desc');
}])->get();
```

### Lazy Eager Loading

Load relationships after retrieving models:

```php
$posts = Post::all();

// Later...
$posts->load('user');
```

### Querying Relationship Existence

**Get users who have posts:**

```php
$users = User::has('posts')->get();
```

**Get users who have at least 5 posts:**

```php
$users = User::has('posts', '>=', 5)->get();
```

**Get users who have published posts:**

```php
$users = User::whereHas('posts', function ($query) {
    $query->where('status', 'published');
})->get();
```

**Get users who DON'T have posts:**

```php
$users = User::doesntHave('posts')->get();
```

### Counting Related Models

```php
// Get users with post count
$users = User::withCount('posts')->get();

foreach ($users as $user) {
    echo "{$user->name} has {$user->posts_count} posts";
}

// Filter by count
$activeUsers = User::withCount('posts')
    ->having('posts_count', '>', 10)
    ->get();
```

---

## Complete Example: Blog System

Let's build a complete blog with users, posts, comments, and tags.

### Models

**User.php:**

```php
class User extends Model
{
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

**Post.php:**

```php
class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }
}
```

**Comment.php:**

```php
class Comment extends Model
{
    protected $fillable = ['post_id', 'user_id', 'content'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

**Tag.php:**

```php
class Tag extends Model
{
    protected $fillable = ['name'];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }
}
```

### Usage Examples

**Create a complete blog post:**

```php
$user = User::find(1);

// Create post
$post = $user->posts()->create([
    'title' => 'Getting Started with Laravel',
    'content' => 'Laravel is amazing because...'
]);

// Add tags
$phpTag = Tag::firstOrCreate(['name' => 'PHP']);
$laravelTag = Tag::firstOrCreate(['name' => 'Laravel']);
$post->tags()->attach([$phpTag->id, $laravelTag->id]);

// Add a comment
$post->comments()->create([
    'user_id' => 2,
    'content' => 'Great post!'
]);
```

**Display a post with all related data:**

```php
$post = Post::with(['user', 'comments.user', 'tags'])->findOrFail(1);

// Post info
echo "Title: {$post->title}\n";
echo "Author: {$post->user->name}\n";

// Tags
echo "Tags: ";
echo $post->tags->pluck('name')->implode(', ');
echo "\n\n";

// Comments
echo "Comments:\n";
foreach ($post->comments as $comment) {
    echo "- {$comment->user->name}: {$comment->content}\n";
}
```

**Get user's dashboard data:**

```php
$user = User::withCount(['posts', 'comments'])->findOrFail(1);

echo "Welcome, {$user->name}!\n";
echo "You have {$user->posts_count} posts\n";
echo "You have {$user->comments_count} comments\n";

// Recent posts
$recentPosts = $user->posts()
    ->with('tags')
    ->latest()
    ->take(5)
    ->get();
```

**Find posts by tag:**

```php
$tag = Tag::where('name', 'Laravel')->firstOrFail();

$posts = $tag->posts()
    ->with('user')
    ->latest()
    ->paginate(10);
```

---

## Advanced: Has Many Through

Get posts through a country's users:

```php
class Country extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function posts(): HasManyThrough
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}

// Usage
$country = Country::find(1);
$posts = $country->posts;  // All posts by users in this country
```

**SQL equivalent would be very complex!**

---

## Relationship Method vs Property

```php
$user = User::find(1);

// As property - executes query immediately
$posts = $user->posts;  // Collection

// As method - returns query builder
$posts = $user->posts();  // Query builder

// Chain additional queries
$publishedPosts = $user->posts()
    ->where('status', 'published')
    ->get();

// You can only chain on methods, not properties!
$user->posts->where('status', 'published');  // Wrong! Collection's where()
$user->posts()->where('status', 'published')->get();  // Right! Query builder
```

---

## Practice Exercises

### Exercise 1: E-commerce System

Create models and relationships for:

1. `Category` has many `Product`
2. `Product` belongs to `Category`
3. `Product` has many `Review`
4. `Review` belongs to `User` and `Product`

Tasks:
- Create all migrations with foreign keys
- Define all relationships
- Create sample data with factories
- Query products with reviews
- Get category with product count

### Exercise 2: Social Media

Build:

1. `User` has many `Post`
2. `Post` belongs to `User`
3. `User` has many `Follower` (self-referencing)
4. `Post` has many `Like` through users (many-to-many)

Tasks:
- Implement follow/unfollow
- Get user's followers
- Get posts from followed users
- Like/unlike posts
- Get post with like count

### Exercise 3: School System

Create:

1. `Student` has many `Enrollment`
2. `Course` has many `Enrollment`
3. `Student` has many `Course` through `Enrollment` (many-to-many with pivot)
4. `Course` has many `Assignment`

Tasks:
- Enroll students in courses
- Store grades in pivot table
- Get student's courses with grades
- Get course average grade
- Get top students by GPA

---

## Common Mistakes

### 1. Wrong Relationship Type

```php
// Wrong - User has ONE post
class User extends Model
{
    public function posts(): HasOne
    {
        return $this->hasOne(Post::class);
    }
}

// Right - User has MANY posts
public function posts(): HasMany
{
    return $this->hasMany(Post::class);
}
```

### 2. Forgetting Foreign Key Column

```php
// Migration must have user_id column
Schema::create('posts', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained();  // Don't forget this!
});
```

### 3. Using Property When Needing Query Builder

```php
// Wrong - can't chain on collection
$posts = $user->posts->where('status', 'published');

// Right - chain on query builder
$posts = $user->posts()->where('status', 'published')->get();
```

### 4. Not Eager Loading (N+1 Problem)

```php
// Bad - N+1 queries
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->user->name;  // Query per post!
}

// Good - 2 queries total
$posts = Post::with('user')->get();
foreach ($posts as $post) {
    echo $post->user->name;
}
```

### 5. Wrong Pivot Table Name

```php
// Wrong - tag_post
Schema::create('tag_post', ...);

// Right - alphabetical order: post_tag
Schema::create('post_tag', ...);
```

---

## Quick Reference

```php
// One-to-Many
class User extends Model
{
    public function posts() { return $this->hasMany(Post::class); }
}
class Post extends Model
{
    public function user() { return $this->belongsTo(User::class); }
}

// Many-to-Many
class Post extends Model
{
    public function tags() { return $this->belongsToMany(Tag::class); }
}
class Tag extends Model
{
    public function posts() { return $this->belongsToMany(Post::class); }
}

// Usage
$user->posts;                    // Get related
$user->posts()->create([...]);   // Create related
$post->tags()->attach(1);        // Attach (many-to-many)
$post->tags()->detach(1);        // Detach
$post->tags()->sync([1, 2]);     // Sync
User::has('posts')->get();       // Has relationship
User::withCount('posts')->get(); // With count
Post::with('user')->get();       // Eager load
```

---

## What's Next?

You now understand how to define and use relationships! But there's a critical performance issue we've mentioned several times: **the N+1 problem**.

**In the next lesson**, you'll learn:
- What the N+1 problem is and why it's dangerous
- How to detect N+1 queries in your application
- Eager loading with `with()`
- Lazy eager loading
- Optimizing complex queries
- When to use joins vs eager loading

This is crucial for building performant Laravel applications!

---

## Key Takeaways

1. **Three main relationship types** - hasMany/belongsTo, belongsToMany, hasOne
2. **Convention over configuration** - user_id, alphabetical pivot tables
3. **Access as property or method** - Property executes, method returns builder
4. **Attach/detach for many-to-many** - sync() replaces all
5. **Eloquent handles JOINs** - No manual SQL needed
6. **Always eager load when looping** - Prevent N+1 problem
7. **withCount for counting** - Efficient aggregate queries
8. **PDO would require complex JOINs** - Eloquent is much simpler!

---

**Next Lesson:** [04 - Eager Loading and the N+1 Problem](./04-eager-loading.md)
