# Lesson 02 - CRUD Operations with Eloquent

## From PDO to Eloquent: The CRUD Evolution

In Module 06, CRUD operations required careful SQL writing and error handling:

```php
// PDO: Verbose and error-prone
try {
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $userId]);
    $id = $pdo->lastInsertId();
} catch (PDOException $e) {
    // Handle error
}
```

With Eloquent, CRUD becomes elegant and safe:

```php
// Eloquent: Clean and simple
$post = Post::create([
    'title' => $title,
    'content' => $content,
    'user_id' => $userId
]);
```

Let's explore all the ways to perform CRUD operations with Eloquent!

---

## CREATE: Adding Records

### Method 1: Create Instance, Set Properties, Save

The most explicit way:

```php
$post = new Post();
$post->title = 'My First Post';
$post->content = 'This is the content';
$post->user_id = auth()->id();
$post->save();

echo "Created post ID: {$post->id}";
```

**Compare with PDO:**
```php
$stmt = $pdo->prepare("INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)");
$stmt->execute(['My First Post', 'This is the content', $userId]);
$id = $pdo->lastInsertId();
```

### Method 2: Mass Assignment with create()

The concise way:

```php
$post = Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content',
    'user_id' => auth()->id()
]);
```

**But you'll get an error!** `MassAssignmentException`

Why? Security! Laravel protects you from accidentally allowing users to set any field.

---

## Mass Assignment Protection

Imagine a form that submits `$_POST` data:

```php
// DANGEROUS if not protected!
$post = Post::create($_POST);
```

What if a malicious user adds `is_admin=1` to the form? They'd become admin!

Laravel requires you to explicitly allow which fields can be mass-assigned.

### Option 1: $fillable (Whitelist)

List fields that **can** be mass-assigned:

```php
class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id'
    ];
}
```

Now this works:

```php
$post = Post::create([
    'title' => 'My Post',
    'content' => 'Content here',
    'user_id' => 1
]);
```

But this fails:

```php
$post = Post::create([
    'id' => 999,        // Not fillable - ignored
    'title' => 'Hack',
    'created_at' => '2020-01-01'  // Not fillable - ignored
]);
```

### Option 2: $guarded (Blacklist)

List fields that **cannot** be mass-assigned:

```php
class Post extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    // All other fields are fillable
}
```

**Use $fillable in most cases - it's safer!**

### Option 3: Disable Protection (Dangerous!)

```php
class Post extends Model
{
    protected $guarded = [];  // Allow everything - use carefully!
}
```

**Only do this if you trust your input 100%!**

---

## More Ways to Create Records

### fill() - Set Attributes Without Saving

```php
$post = new Post();
$post->fill([
    'title' => 'My Post',
    'content' => 'Content'
]);
// Not saved yet!

$post->save();  // Now it's saved
```

### firstOrCreate() - Find or Create

```php
// Find post with this slug, or create it if not found
$post = Post::firstOrCreate(
    ['slug' => 'my-first-post'],  // Search criteria
    [                              // Additional attributes if creating
        'title' => 'My First Post',
        'content' => 'Content here',
        'user_id' => 1
    ]
);
```

**Use case:** Prevent duplicates, like tags or categories.

### firstOrNew() - Find or Instantiate (Doesn't Save)

```php
$post = Post::firstOrNew(
    ['slug' => 'my-first-post'],
    ['title' => 'My First Post']
);

// $post exists in memory but not in database yet
if (!$post->exists) {
    // This is a new post
    $post->content = 'Add more data';
    $post->save();
}
```

### updateOrCreate() - Update if Exists, Create if Not

```php
$post = Post::updateOrCreate(
    ['slug' => 'my-first-post'],  // Search criteria
    [                              // Data to update or create with
        'title' => 'Updated Title',
        'content' => 'Updated content'
    ]
);
```

**Use case:** API endpoints where you want to save without checking if record exists.

---

## READ: Retrieving Records

### Get by Primary Key

```php
// Get by ID
$post = Post::find(1);
if ($post) {
    echo $post->title;
} else {
    echo "Not found";
}

// Get or throw 404
$post = Post::findOrFail(1);  // Throws ModelNotFoundException if not found

// Get multiple by ID
$posts = Post::find([1, 2, 3]);
```

**PDO comparison:**
```php
$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([1]);
$post = $stmt->fetch();
```

### Get All Records

```php
$posts = Post::all();

// With specific columns
$posts = Post::all(['id', 'title', 'created_at']);
```

**Warning:** Be careful with `all()` on large tables! Use pagination instead.

### Get First Match

```php
// First record in table
$post = Post::first();

// First match of where clause
$post = Post::where('status', 'published')->first();

// First or throw 404
$post = Post::where('slug', 'my-post')->firstOrFail();
```

### Where Clauses

```php
// Simple where
$posts = Post::where('status', 'published')->get();

// Multiple conditions (AND)
$posts = Post::where('status', 'published')
    ->where('user_id', 1)
    ->get();

// Or conditions
$posts = Post::where('status', 'published')
    ->orWhere('status', 'featured')
    ->get();

// Where with operator
$posts = Post::where('views', '>', 1000)->get();
$posts = Post::where('views', '>=', 100)->get();

// Like operator
$posts = Post::where('title', 'like', '%Laravel%')->get();

// Where in array
$posts = Post::whereIn('status', ['published', 'featured'])->get();
$posts = Post::whereNotIn('status', ['draft', 'archived'])->get();

// Where between
$posts = Post::whereBetween('views', [100, 1000])->get();

// Where null
$posts = Post::whereNull('deleted_at')->get();
$posts = Post::whereNotNull('published_at')->get();

// Where date
$posts = Post::whereDate('created_at', '2024-01-15')->get();
$posts = Post::whereYear('created_at', 2024)->get();
$posts = Post::whereMonth('created_at', 12)->get();
$posts = Post::whereDay('created_at', 25)->get();
```

**PDO comparison:**
```php
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = ? AND views > ?");
$stmt->execute(['published', 1000]);
$posts = $stmt->fetchAll();
```

### Advanced Where

```php
// Group conditions
$posts = Post::where('status', 'published')
    ->where(function ($query) {
        $query->where('views', '>', 1000)
              ->orWhere('is_featured', true);
    })
    ->get();
// SQL: WHERE status = 'published' AND (views > 1000 OR is_featured = 1)

// When conditional
$status = request('status');
$posts = Post::when($status, function ($query, $status) {
    return $query->where('status', $status);
})->get();
// Only adds where clause if $status is truthy
```

### Ordering

```php
// Order by column
$posts = Post::orderBy('created_at', 'desc')->get();
$posts = Post::orderBy('title', 'asc')->get();

// Multiple order by
$posts = Post::orderBy('status', 'desc')
    ->orderBy('created_at', 'desc')
    ->get();

// Latest and oldest (assumes created_at)
$posts = Post::latest()->get();          // orderBy('created_at', 'desc')
$posts = Post::oldest()->get();          // orderBy('created_at', 'asc')

// Latest by custom column
$posts = Post::latest('published_at')->get();

// Random order
$posts = Post::inRandomOrder()->get();
```

### Limiting and Offsetting

```php
// Take first N records
$posts = Post::take(10)->get();
$posts = Post::limit(10)->get();  // Alias for take()

// Skip and take (pagination)
$posts = Post::skip(20)->take(10)->get();
$posts = Post::offset(20)->limit(10)->get();  // Aliases

// Get page 3 (posts 21-30)
$page = 3;
$perPage = 10;
$posts = Post::skip(($page - 1) * $perPage)->take($perPage)->get();
```

### Selecting Columns

```php
// Select specific columns
$posts = Post::select('id', 'title', 'created_at')->get();

// Add columns to selection
$posts = Post::select('id', 'title')
    ->addSelect('content')
    ->get();

// Select with DB raw expression
$posts = Post::select('id', 'title', DB::raw('DATE(created_at) as date'))
    ->get();
```

### Aggregates

```php
// Count
$count = Post::count();
$publishedCount = Post::where('status', 'published')->count();

// Check existence
$exists = Post::where('slug', 'my-post')->exists();
$doesntExist = Post::where('slug', 'my-post')->doesntExist();

// Sum, avg, min, max
$totalViews = Post::sum('views');
$avgViews = Post::avg('views');
$maxViews = Post::max('views');
$minViews = Post::min('views');
```

---

## UPDATE: Modifying Records

### Method 1: Find, Modify, Save

```php
$post = Post::find(1);
$post->title = 'Updated Title';
$post->content = 'Updated content';
$post->save();
```

**PDO comparison:**
```php
$stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
$stmt->execute(['Updated Title', 'Updated content', 1]);
```

### Method 2: update() Method

```php
$post = Post::find(1);
$post->update([
    'title' => 'Updated Title',
    'content' => 'Updated content'
]);
```

### Method 3: Mass Update

Update multiple records at once:

```php
// Update all posts by user
Post::where('user_id', 1)->update(['status' => 'published']);

// Update all draft posts
Post::where('status', 'draft')->update([
    'status' => 'published',
    'published_at' => now()
]);
```

### Increment and Decrement

```php
// Increment views by 1
$post->increment('views');

// Increment by specific amount
$post->increment('views', 10);

// Decrement
$post->decrement('likes');

// Increment multiple columns
$post->increment('views', 1, ['last_viewed_at' => now()]);

// Increment without updating timestamps
$post->incrementQuietly('views');
```

**PDO comparison:**
```php
$stmt = $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
$stmt->execute([1]);
```

### Touch (Update Timestamps)

```php
// Update updated_at to now
$post->touch();

// Useful for marking related models as updated
$comment->post->touch();  // Update parent post's updated_at
```

---

## DELETE: Removing Records

### Method 1: Delete Instance

```php
$post = Post::find(1);
$post->delete();
```

**PDO comparison:**
```php
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([1]);
```

### Method 2: Delete by ID

```php
// Delete one
Post::destroy(1);

// Delete multiple
Post::destroy([1, 2, 3]);
Post::destroy(1, 2, 3);  // Also works
```

### Method 3: Delete with Query

```php
// Delete all draft posts
Post::where('status', 'draft')->delete();

// Delete old posts
Post::where('created_at', '<', now()->subYear())->delete();
```

### Check if Deleted

```php
$deleted = Post::where('user_id', 1)->delete();
echo "Deleted {$deleted} posts";
```

### Truncate (Delete All, Reset IDs)

```php
// Delete everything and reset auto-increment
Post::truncate();
```

---

## Working with Attributes

### Checking if Attribute Changed

```php
$post = Post::find(1);
$post->title = 'New Title';

$post->isDirty();               // true - has changes
$post->isDirty('title');        // true - title changed
$post->isDirty('content');      // false - content didn't change

$post->isClean();               // false - opposite of isDirty()

$post->save();

$post->wasChanged();            // true - changes were saved
$post->wasChanged('title');     // true - title was saved
```

### Get Original Values

```php
$post = Post::find(1);
$originalTitle = $post->title;

$post->title = 'New Title';

$post->getOriginal('title');    // Returns original value
$post->getOriginal();           // Returns all original values

$post->getChanges();            // Returns changed attributes
```

### Refresh from Database

```php
$post = Post::find(1);
$post->title = 'Changed';

$post->refresh();  // Reload from database
echo $post->title;  // Original title, not "Changed"
```

---

## Attribute Casting

Cast database values to PHP types automatically:

```php
class Post extends Model
{
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'views' => 'integer',
        'metadata' => 'array',
        'settings' => 'json',
    ];
}
```

Usage:

```php
$post = Post::find(1);

// Boolean casting
if ($post->is_published) {  // true/false, not 1/0
    echo "Published!";
}

// DateTime casting
echo $post->published_at->format('Y-m-d');  // Carbon instance
echo $post->published_at->diffForHumans();  // "2 days ago"

// Array casting (stored as JSON in database)
$post->metadata = ['author' => 'John', 'tags' => ['PHP', 'Laravel']];
$post->save();  // Automatically encoded to JSON

$post = Post::find(1);
echo $post->metadata['author'];  // Automatically decoded from JSON
```

**Without casting:**
```php
// PDO way - manual JSON handling
$metadata = json_decode($post['metadata'], true);
echo $metadata['author'];
```

---

## Default Attribute Values

Set default values for new models:

```php
class Post extends Model
{
    protected $attributes = [
        'status' => 'draft',
        'views' => 0,
        'is_featured' => false,
    ];
}
```

Usage:

```php
$post = new Post(['title' => 'My Post']);
echo $post->status;  // 'draft' (default value)
```

---

## Hidden and Visible Attributes

Control which attributes appear when converting to JSON/array:

```php
class User extends Model
{
    // Hide sensitive fields
    protected $hidden = ['password', 'remember_token'];

    // Or show only specific fields
    protected $visible = ['id', 'name', 'email'];
}
```

Usage:

```php
$user = User::find(1);

return $user->toArray();  // password not included
return response()->json($user);  // password not included
```

**Override for specific instances:**

```php
$user = User::find(1);

// Hide additional field
$user->makeHidden('email');

// Show hidden field
$user->makeVisible('password');
```

---

## Soft Deletes

Instead of permanently deleting records, mark them as deleted:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

**Migration:**

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->softDeletes();  // Adds deleted_at column
    $table->timestamps();
});
```

**Using soft deletes:**

```php
// "Delete" a post (sets deleted_at timestamp)
$post = Post::find(1);
$post->delete();

// Normal queries exclude soft-deleted records
$posts = Post::all();  // Doesn't include deleted posts

// Check if soft-deleted
if ($post->trashed()) {
    echo "This post is deleted";
}

// Include soft-deleted records
$posts = Post::withTrashed()->get();

// Get only soft-deleted records
$posts = Post::onlyTrashed()->get();

// Restore soft-deleted record
$post = Post::withTrashed()->find(1);
$post->restore();

// Permanently delete (force delete)
$post->forceDelete();
```

**PDO comparison:**

With PDO, you'd manually manage a `deleted_at` column and add `WHERE deleted_at IS NULL` to every query!

---

## Complete CRUD Example: Blog Post Manager

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // List all posts
    public function index()
    {
        $posts = Post::where('status', 'published')
            ->latest()
            ->paginate(10);

        return view('posts.index', compact('posts'));
    }

    // Show create form
    public function create()
    {
        return view('posts.create');
    }

    // Store new post
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'status' => 'in:draft,published',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'status' => $validated['status'] ?? 'draft',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created!');
    }

    // Show single post
    public function show(Post $post)  // Route model binding!
    {
        return view('posts.show', compact('post'));
    }

    // Show edit form
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // Update post
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post updated!');
    }

    // Delete post
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted!');
    }
}
```

**PDO equivalent would be 200+ lines of SQL!**

---

## Pagination

### Simple Pagination

```php
// Get 15 posts per page
$posts = Post::paginate(15);

// In Blade
@foreach ($posts as $post)
    <h2>{{ $post->title }}</h2>
@endforeach

{{ $posts->links() }}  // Pagination links
```

### Custom Pagination

```php
// Custom per page
$posts = Post::paginate(10);

// Simple pagination (next/previous only)
$posts = Post::simplePaginate(10);

// Manual pagination
$posts = Post::paginate(
    $perPage = 15,
    $columns = ['*'],
    $pageName = 'page',
    $page = 1
);
```

**PDO comparison:**
```php
$page = $_GET['page'] ?? 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT * FROM posts LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$posts = $stmt->fetchAll();

// Then manually create pagination links!
```

---

## Chunking Large Result Sets

Process thousands of records without memory issues:

```php
// Process 100 records at a time
Post::chunk(100, function ($posts) {
    foreach ($posts as $post) {
        // Process each post
        $post->update(['processed' => true]);
    }
});

// With filter
Post::where('status', 'draft')
    ->chunk(100, function ($posts) {
        // Process draft posts
    });
```

### Lazy Loading with cursor()

For even better memory efficiency:

```php
foreach (Post::cursor() as $post) {
    // Only one post in memory at a time
    echo $post->title . "\n";
}
```

---

## Raw Expressions

Sometimes you need raw SQL:

```php
// Raw where clause
$posts = Post::whereRaw('views > ? AND created_at > ?', [1000, '2024-01-01'])->get();

// Raw select
$posts = Post::selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->get();

// Raw order by
$posts = Post::orderByRaw('FIELD(status, "featured", "published", "draft")')->get();

// Update with raw SQL
Post::where('id', 1)->update(['views' => DB::raw('views + 1')]);
```

---

## Common Patterns

### Find or Fail with Custom Message

```php
$post = Post::findOr(1, function () {
    abort(404, 'Post not found!');
});
```

### Get or Create Pattern

```php
// For unique slugs
$post = Post::where('slug', $slug)->first();

if (!$post) {
    $post = Post::create([
        'title' => $title,
        'slug' => $slug,
        'content' => $content
    ]);
}

// Or use firstOrCreate()
$post = Post::firstOrCreate(['slug' => $slug], [
    'title' => $title,
    'content' => $content
]);
```

### Conditional Updates

```php
// Only update if status is draft
Post::where('id', 1)
    ->where('status', 'draft')
    ->update(['status' => 'published']);

// Check if updated
$updated = Post::where('id', 1)
    ->where('status', 'draft')
    ->update(['status' => 'published']);

if ($updated) {
    echo "Status changed from draft to published";
}
```

---

## Practice Exercises

### Exercise 1: Task Manager

Build a complete task CRUD system:

1. Create `Task` model with `$fillable` protection
2. Implement all CRUD operations
3. Add soft deletes
4. Create controller methods for:
   - List all tasks
   - Create new task
   - Mark as completed (toggle boolean)
   - Delete task
   - Restore deleted task

### Exercise 2: Blog Post Features

Extend a `Post` model with:

1. Increment views when post is viewed
2. Publish/unpublish toggle
3. Featured posts (boolean flag)
4. Get posts published in last 7 days
5. Get most viewed posts (top 10)
6. Search posts by title (LIKE query)

### Exercise 3: Product Catalog

Create a `Product` model with:

1. Mass assignment protection
2. Soft deletes
3. Attribute casting for:
   - `price` (decimal)
   - `in_stock` (boolean)
   - `metadata` (array/JSON)
4. Methods to:
   - Get products under $100
   - Get out of stock products
   - Increase/decrease stock
   - Apply discount (percentage)

---

## Common Mistakes

### 1. Forgetting Mass Assignment Protection

```php
// Error: Add title to $fillable in Post model
$post = Post::create(['title' => 'My Post']);
```

### 2. Not Calling get() or first()

```php
// Wrong - returns Builder, not results
$posts = Post::where('status', 'published');

// Right
$posts = Post::where('status', 'published')->get();
```

### 3. Using find() Without Null Check

```php
// Might be null
$post = Post::find(999);
echo $post->title;  // Error!

// Better
$post = Post::findOrFail(999);  // Throws 404
```

### 4. Inefficient Counting

```php
// Bad - loads all records into memory
$count = Post::all()->count();

// Good - counts in database
$count = Post::count();
```

### 5. Updating Without Checking

```php
// What if post doesn't exist?
$post = Post::find(999);
$post->update(['title' => 'New']);  // Error!

// Better
$post = Post::findOrFail(999);
$post->update(['title' => 'New']);
```

---

## Quick Reference

```php
// CREATE
Post::create(['title' => 'Title']);
$post = new Post(['title' => 'Title']); $post->save();
Post::firstOrCreate(['slug' => 'slug'], ['title' => 'Title']);
Post::updateOrCreate(['id' => 1], ['title' => 'New Title']);

// READ
Post::all();
Post::find(1);
Post::findOrFail(1);
Post::where('status', 'published')->get();
Post::first();
Post::count();

// UPDATE
$post->update(['title' => 'New']);
$post->title = 'New'; $post->save();
Post::where('id', 1)->update(['title' => 'New']);
$post->increment('views');

// DELETE
$post->delete();
Post::destroy(1);
Post::where('status', 'draft')->delete();

// SOFT DELETE
$post->delete();              // Soft delete
$post->restore();             // Restore
$post->forceDelete();         // Permanent delete
Post::withTrashed()->get();   // Include soft-deleted
Post::onlyTrashed()->get();   // Only soft-deleted
```

---

## What's Next?

You're now proficient with Eloquent CRUD operations! But the real power of Eloquent comes from **relationships**.

**In the next lesson**, you'll learn how to:
- Define relationships between models (hasMany, belongsTo, belongsToMany)
- Query related models effortlessly
- Create complex data structures
- Build a complete blog system with users, posts, and comments

Get ready - this is where Eloquent truly shines compared to PDO!

---

## Key Takeaways

1. **Mass assignment requires $fillable or $guarded** - Security protection
2. **Multiple ways to create** - new + save(), create(), firstOrCreate()
3. **Eloquent returns Collections** - Much more powerful than arrays
4. **Soft deletes** - Delete without losing data
5. **Attribute casting** - Automatic type conversion
6. **Increment/decrement** - Clean way to update counters
7. **Always use findOrFail()** - Automatic 404 handling
8. **Eloquent > PDO for 95% of operations** - Cleaner, safer, faster to write

---

**Next Lesson:** [03 - Eloquent Relationships](./03-relationships.md)
