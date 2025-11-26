# Lesson 08 - Soft Deletes

## The "Oops, I Deleted That!" Problem

With PDO and regular deletes, once data is gone, it's gone forever:

```php
// PDO: Permanent deletion
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([1]);

// Post is gone forever!
// No undo. No recovery. Just... gone.
```

**Problems:**
- Users accidentally delete important data
- No audit trail of deletions
- Can't implement "trash bin" feature
- Compliance issues (need to keep records)

**With soft deletes**, records are marked as deleted but stay in the database:

```php
// Eloquent: Soft delete
$post = Post::find(1);
$post->delete();

// Post still in database!
// deleted_at = '2024-01-15 10:30:00'

// Can restore it
$post->restore();
```

---

## What are Soft Deletes?

**Soft deletes** mark records as deleted without actually removing them from the database. A `deleted_at` timestamp column indicates when a record was deleted.

**Normal queries automatically exclude soft-deleted records**, but you can include or query them when needed.

### The deleted_at Column

```
| id | title      | content  | deleted_at          |
|----|------------|----------|---------------------|
| 1  | Post 1     | ...      | NULL                |  ← Active
| 2  | Post 2     | ...      | 2024-01-15 10:30:00 |  ← Soft deleted
| 3  | Post 3     | ...      | NULL                |  ← Active
```

- `deleted_at = NULL` → Record is active
- `deleted_at = timestamp` → Record is soft-deleted

---

## Setting Up Soft Deletes

### Step 1: Add Column to Migration

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
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
            $table->softDeletes();  // Adds deleted_at column
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

**`softDeletes()`** adds a `deleted_at` TIMESTAMP column.

### Adding to Existing Table

```php
// Add soft deletes to existing table
Schema::table('posts', function (Blueprint $table) {
    $table->softDeletes();
});

// Remove soft deletes
Schema::table('posts', function (Blueprint $table) {
    $table->dropSoftDeletes();
});
```

### Step 2: Add Trait to Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;  // Enable soft deletes

    protected $fillable = ['title', 'content', 'user_id'];
}
```

**That's it!** Your model now uses soft deletes.

---

## Using Soft Deletes

### Soft Deleting Records

```php
// Soft delete a post
$post = Post::find(1);
$post->delete();

// deleted_at is now set
// Post still exists in database but is "deleted"
```

**SQL executed:**
```sql
UPDATE posts SET deleted_at = '2024-01-15 10:30:00' WHERE id = 1
```

**Compare with hard delete (no soft deletes):**
```sql
DELETE FROM posts WHERE id = 1  -- Gone forever!
```

### Checking if Soft Deleted

```php
$post = Post::withTrashed()->find(1);

if ($post->trashed()) {
    echo "This post is in the trash";
} else {
    echo "This post is active";
}
```

### Normal Queries Exclude Soft Deleted

```php
// Automatically excludes soft-deleted posts
$posts = Post::all();  // WHERE deleted_at IS NULL

$post = Post::find(1);  // WHERE id = 1 AND deleted_at IS NULL

$count = Post::count();  // Counts only non-deleted posts
```

**Soft-deleted records are invisible to normal queries!**

---

## Querying Soft Deleted Records

### Include Soft Deleted Records

```php
// Get all posts including soft-deleted
$posts = Post::withTrashed()->get();

// Find post including soft-deleted
$post = Post::withTrashed()->find(1);

// Where clause including soft-deleted
$posts = Post::withTrashed()
    ->where('user_id', 1)
    ->get();
```

### Get ONLY Soft Deleted Records

```php
// Get only trashed posts
$trashedPosts = Post::onlyTrashed()->get();

// Count trashed posts
$trashedCount = Post::onlyTrashed()->count();
```

---

## Restoring Soft Deleted Records

### Restore a Single Record

```php
$post = Post::withTrashed()->find(1);
$post->restore();

// deleted_at is now NULL again
// Post is active!
```

**SQL executed:**
```sql
UPDATE posts SET deleted_at = NULL WHERE id = 1
```

### Restore Multiple Records

```php
// Restore all soft-deleted posts by user
Post::onlyTrashed()
    ->where('user_id', 1)
    ->restore();

// Restore specific posts
Post::withTrashed()
    ->whereIn('id', [1, 2, 3])
    ->restore();
```

---

## Permanent Deletion (Force Delete)

Sometimes you need to **really** delete a record:

```php
// Soft delete first
$post = Post::find(1);
$post->delete();  // Sets deleted_at

// Now permanently delete
$post = Post::withTrashed()->find(1);
$post->forceDelete();  // Actually deletes from database

// Or directly force delete without soft delete
$post = Post::find(1);
$post->forceDelete();  // Bypasses soft delete
```

**SQL executed:**
```sql
DELETE FROM posts WHERE id = 1  -- Actually removed
```

---

## Real-World Example: Trash System

### Posts with Trash Bin

```php
class PostController extends Controller
{
    // List active posts
    public function index()
    {
        $posts = Post::latest()->paginate(10);
        return view('posts.index', compact('posts'));
    }

    // List trashed posts
    public function trash()
    {
        $posts = Post::onlyTrashed()->latest('deleted_at')->paginate(10);
        return view('posts.trash', compact('posts'));
    }

    // Soft delete post (move to trash)
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post moved to trash');
    }

    // Restore from trash
    public function restore($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->restore();

        return redirect()->route('posts.index')
            ->with('success', 'Post restored');
    }

    // Permanently delete
    public function forceDestroy($id)
    {
        $post = Post::withTrashed()->findOrFail($id);
        $post->forceDelete();

        return redirect()->route('posts.trash')
            ->with('success', 'Post permanently deleted');
    }

    // Empty trash (permanently delete all trashed posts)
    public function emptyTrash()
    {
        Post::onlyTrashed()->forceDelete();

        return redirect()->route('posts.trash')
            ->with('success', 'Trash emptied');
    }
}
```

### Routes

```php
// routes/web.php
Route::resource('posts', PostController::class);

Route::get('posts/trash', [PostController::class, 'trash'])->name('posts.trash');
Route::post('posts/{id}/restore', [PostController::class, 'restore'])->name('posts.restore');
Route::delete('posts/{id}/force', [PostController::class, 'forceDestroy'])->name('posts.force-destroy');
Route::delete('posts/trash/empty', [PostController::class, 'emptyTrash'])->name('posts.empty-trash');
```

### Views

**posts/index.blade.php:**

```blade
<h1>Posts</h1>
<a href="{{ route('posts.trash') }}">View Trash ({{ Post::onlyTrashed()->count() }})</a>

@foreach ($posts as $post)
    <div class="post">
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->content }}</p>

        <form action="{{ route('posts.destroy', $post) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit">Move to Trash</button>
        </form>
    </div>
@endforeach
```

**posts/trash.blade.php:**

```blade
<h1>Trash</h1>
<a href="{{ route('posts.index') }}">Back to Posts</a>

<form action="{{ route('posts.empty-trash') }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" onclick="return confirm('Permanently delete all?')">
        Empty Trash
    </button>
</form>

@foreach ($posts as $post)
    <div class="post">
        <h2>{{ $post->title }}</h2>
        <p>Deleted: {{ $post->deleted_at->diffForHumans() }}</p>

        <form action="{{ route('posts.restore', $post->id) }}" method="POST" style="display:inline">
            @csrf
            <button type="submit">Restore</button>
        </form>

        <form action="{{ route('posts.force-destroy', $post->id) }}" method="POST" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Permanently delete?')">
                Delete Forever
            </button>
        </form>
    </div>
@endforeach
```

---

## Soft Deletes with Relationships

### Cascade Soft Deletes

When you soft delete a parent, you might want to soft delete children:

```php
class User extends Model
{
    use SoftDeletes;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected static function booted()
    {
        // When user is soft deleted, soft delete their posts
        static::deleting(function ($user) {
            $user->posts()->delete();
        });

        // When user is restored, restore their posts
        static::restoring(function ($user) {
            $user->posts()->onlyTrashed()->restore();
        });
    }
}
```

### Query Relationships with Soft Deletes

```php
$user = User::with('posts')->find(1);
// Only loads active posts (deleted_at IS NULL)

$user = User::with(['posts' => function ($query) {
    $query->withTrashed();
}])->find(1);
// Loads all posts including soft-deleted

$user = User::with(['posts' => function ($query) {
    $query->onlyTrashed();
}])->find(1);
// Only loads soft-deleted posts
```

---

## Comparing PDO and Eloquent

### PDO Approach

Manually implement soft deletes:

```php
// "Delete" (set deleted_at)
$stmt = $pdo->prepare("UPDATE posts SET deleted_at = NOW() WHERE id = ?");
$stmt->execute([1]);

// Get active posts (must remember WHERE clause every time!)
$stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NULL");
$posts = $stmt->fetchAll();

// Get trashed posts
$stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NOT NULL");
$trashedPosts = $stmt->fetchAll();

// Restore
$stmt = $pdo->prepare("UPDATE posts SET deleted_at = NULL WHERE id = ?");
$stmt->execute([1]);

// Force delete
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([1]);
```

**Problems:**
- Must remember `WHERE deleted_at IS NULL` on EVERY query
- Easy to forget and show deleted records
- Lots of repetitive SQL

### Eloquent Approach

```php
// Soft delete
$post->delete();

// Normal queries automatically filter
$posts = Post::all();  // Auto-excludes deleted

// Include deleted
$posts = Post::withTrashed()->get();

// Only deleted
$posts = Post::onlyTrashed()->get();

// Restore
$post->restore();

// Force delete
$post->forceDelete();
```

**Much cleaner and safer!**

---

## Advanced: Custom Deleted Column

If you need a different column name:

```php
class Post extends Model
{
    use SoftDeletes;

    const DELETED_AT = 'removed_at';  // Use 'removed_at' instead of 'deleted_at'
}

// Migration
$table->timestamp('removed_at')->nullable();
```

---

## Auto-Delete Old Soft Deleted Records

Clean up old trashed records automatically:

```php
// In a scheduled command
class CleanupOldTrashedPosts extends Command
{
    protected $signature = 'cleanup:posts';

    public function handle()
    {
        // Permanently delete posts trashed more than 30 days ago
        $deleted = Post::onlyTrashed()
            ->where('deleted_at', '<', now()->subDays(30))
            ->forceDelete();

        $this->info("Deleted {$deleted} old posts");
    }
}

// Schedule in app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('cleanup:posts')->daily();
}
```

---

## When to Use Soft Deletes

### Good Use Cases

1. **User-facing trash bins** - Gmail-style trash
2. **Audit trails** - Track who deleted what and when
3. **Compliance** - Legal requirement to keep records
4. **Undo functionality** - Let users undo deletions
5. **Referenced data** - Posts referenced by other tables
6. **Valuable data** - Customer records, financial data

### When NOT to Use Soft Deletes

1. **Temporary data** - Session data, cache entries
2. **Large datasets** - Logs, analytics (use archiving instead)
3. **Performance-critical** - Adds overhead to every query
4. **Simple apps** - Adds complexity if not needed

---

## Soft Deletes + Unique Constraints

Problem: Soft deletes conflict with unique constraints:

```sql
-- If email is unique
| id | email             | deleted_at |
|----|-------------------|------------|
| 1  | john@example.com  | NULL       |
| 2  | john@example.com  | 2024-01-15 |  ← Can't create: email exists!
```

**Solutions:**

### Solution 1: Composite Unique Index

```php
// Migration
$table->unique(['email', 'deleted_at']);
```

Now you can have multiple deleted records with same email, but only one active.

### Solution 2: Conditional Unique Index (MySQL 8+)

```php
Schema::table('users', function (Blueprint $table) {
    DB::statement('CREATE UNIQUE INDEX users_email_unique ON users (email) WHERE deleted_at IS NULL');
});
```

### Solution 3: Append Timestamp to Email on Delete

```php
class User extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::deleting(function ($user) {
            // Append timestamp to email when soft deleting
            $user->email = $user->email . '.deleted.' . time();
            $user->save();
        });
    }
}
```

---

## Testing Soft Deletes

```php
use Tests\TestCase;
use App\Models\Post;

class PostSoftDeleteTest extends TestCase
{
    /** @test */
    public function it_soft_deletes_a_post()
    {
        $post = Post::factory()->create();

        $post->delete();

        // Post should be soft deleted
        $this->assertSoftDeleted($post);

        // Should not appear in normal queries
        $this->assertNull(Post::find($post->id));

        // Should appear in withTrashed queries
        $this->assertNotNull(Post::withTrashed()->find($post->id));
    }

    /** @test */
    public function it_restores_a_soft_deleted_post()
    {
        $post = Post::factory()->create();
        $post->delete();

        $post->restore();

        // Post should be active
        $this->assertNotSoftDeleted($post);
        $this->assertNotNull(Post::find($post->id));
    }

    /** @test */
    public function it_force_deletes_a_post()
    {
        $post = Post::factory()->create();

        $post->forceDelete();

        // Post should be gone
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }
}
```

---

## Practice Exercises

### Exercise 1: Blog with Trash

Build a blog system with:

1. Soft deletes on `Post` model
2. List active posts
3. "Move to Trash" button
4. Trash page showing deleted posts with:
   - Restore button
   - Delete permanently button
   - Empty trash button
5. Show trash count in navigation

### Exercise 2: E-commerce Products

Create:

1. `Product` model with soft deletes
2. Admin can soft delete products
3. Soft deleted products don't show in shop
4. Admin panel shows deleted products
5. Auto-delete products trashed > 60 days

### Exercise 3: User Account Deactivation

Implement:

1. `User` model with soft deletes
2. Users can deactivate their account (soft delete)
3. Deactivated users can't login
4. Deactivated users can reactivate (restore) within 30 days
5. After 30 days, account is permanently deleted
6. All user's posts/comments also soft deleted

---

## Common Mistakes

### 1. Forgetting withTrashed() When Needed

```php
// Wrong - returns null if soft deleted
$post = Post::find(1);
$post->restore();  // Error! $post is null

// Right
$post = Post::withTrashed()->find(1);
$post->restore();
```

### 2. Not Handling Soft Deletes in Relationships

```php
// Wrong - doesn't cascade soft delete
$user->delete();  // User deleted, but posts remain

// Right - cascade in model
protected static function booted()
{
    static::deleting(fn($user) => $user->posts()->delete());
}
```

### 3. Using delete() When Wanting Force Delete

```php
// Wrong - this soft deletes if SoftDeletes trait is used
$post->delete();

// Right - force delete
$post->forceDelete();
```

### 4. Querying Relationships Without Considering Soft Deletes

```php
// By default, relationships respect soft deletes
$user->posts;  // Only active posts

// Include trashed
$user->posts()->withTrashed()->get();
```

---

## Quick Reference

```php
// Setup
use SoftDeletes;
$table->softDeletes();

// Soft delete
$model->delete();

// Check if trashed
$model->trashed();

// Query with soft deleted
Model::withTrashed()->get();
Model::onlyTrashed()->get();

// Restore
$model->restore();
Model::onlyTrashed()->restore();

// Force delete (permanent)
$model->forceDelete();

// Testing
$this->assertSoftDeleted($model);
$this->assertNotSoftDeleted($model);
```

---

## Module Complete!

Congratulations! You've completed Module 15 - Laravel Eloquent ORM!

### What You've Learned

1. **Eloquent Basics** - ORM, Active Record, models
2. **CRUD Operations** - Create, read, update, delete with ease
3. **Relationships** - hasMany, belongsTo, belongsToMany
4. **Eager Loading** - Solve N+1 problem, optimize queries
5. **Query Scopes** - Reusable query logic
6. **Accessors/Mutators** - Automatic data transformation
7. **Mass Assignment** - Security with $fillable
8. **Soft Deletes** - Delete without losing data

### Key Takeaways

- **Eloquent >> PDO** - Much cleaner, safer, more productive
- **Relationships are automatic** - No manual JOINs needed
- **Always eager load** - Prevent N+1 problems
- **Use scopes** - Keep queries DRY
- **Protect with $fillable** - Security first
- **Soft deletes for important data** - Easy undo

### From PDO to Eloquent

Remember where you started in Module 06:

```php
// PDO: 15 lines for simple query
$stmt = $pdo->prepare("
    SELECT p.*, u.name as author_name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.status = ?
    ORDER BY p.created_at DESC
    LIMIT 10
");
$stmt->execute(['published']);
$posts = $stmt->fetchAll();
```

Now with Eloquent:

```php
// Eloquent: 1 line for the same query
$posts = Post::with('user')->published()->latest()->take(10)->get();
```

**That's the power of Eloquent!**

---

## What's Next?

**Module 16 - Laravel Authentication & Security**

Now that you're an Eloquent expert, you'll learn how Laravel handles authentication:
- User registration and login
- Password hashing and verification
- Email verification
- Password resets
- Session management
- Authorization with gates and policies

You already built authentication in pure PHP (Module 07). Now see how Laravel makes it even easier!

---

## Key Takeaways

1. **Soft deletes = safety net** - Delete without losing data
2. **Use SoftDeletes trait** - Automatic handling
3. **Normal queries exclude deleted** - Safe by default
4. **withTrashed() to include** - When you need deleted records
5. **forceDelete() for permanent** - Really delete
6. **Great for trash bins** - Gmail-style functionality
7. **Consider unique constraints** - Special handling needed
8. **PDO required manual SQL** - Eloquent automates everything

---

**Congratulations on completing Module 15! You're now an Eloquent master!**
