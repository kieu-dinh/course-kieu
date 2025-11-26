# Lesson 9: Database Testing

**Duration**: 2-3 hours
**Prerequisites**: Lessons 6-8 (PHPUnit, Unit Tests, Feature Tests)
**Objective**: Master database testing techniques including transactions, seeding, and testing database-specific features

---

## Introduction

Most applications depend heavily on databases. Testing database interactions is crucial:
- Verify data is saved correctly
- Test relationships between models
- Ensure migrations work
- Validate database constraints
- Test complex queries

But databases present challenges:
- Tests must be isolated (one test shouldn't affect another)
- Tests need clean state (start fresh each time)
- Tests should be fast (database operations can be slow)

Laravel provides powerful tools to solve these problems.

---

## The RefreshDatabase Trait

The most important tool for database testing:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_user()
    {
        // Database is fresh and empty here
        $user = User::create([...]);
        // ...
    }

    /** @test */
    public function it_creates_another_user()
    {
        // Database is fresh again - previous test's data is gone
        $user = User::create([...]);
        // ...
    }
}
```

**What it does**:
- Before each test: Runs migrations (creates tables)
- After each test: Rolls back database changes
- Result: Each test starts with a clean database

**How it works**:
- Uses database transactions (fast)
- Rolls back at the end of each test
- No actual commits to database

---

## Alternative: DatabaseMigrations Trait

If transactions don't work (some databases don't support them), use `DatabaseMigrations`:

```php
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExampleTest extends TestCase
{
    use DatabaseMigrations; // Instead of RefreshDatabase

    // Tests...
}
```

**Difference**:
- `RefreshDatabase`: Uses transactions (fast)
- `DatabaseMigrations`: Migrates and resets database each time (slower)

**Use `DatabaseMigrations` when**:
- Your test uses multiple database connections
- You're testing transactions themselves
- Database doesn't support nested transactions

---

## Database Assertions

Laravel provides helpful assertions for testing database state:

### assertDatabaseHas

Check a record exists with specific attributes:

```php
/** @test */
public function it_creates_a_task()
{
    $user = User::factory()->create();

    $user->tasks()->create([
        'title' => 'My Task',
        'description' => 'Task description',
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'My Task',
        'description' => 'Task description',
        'user_id' => $user->id,
    ]);
}
```

### assertDatabaseMissing

Check a record doesn't exist:

```php
/** @test */
public function it_deletes_a_task()
{
    $task = Task::factory()->create();

    $task->delete();

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id
    ]);
}
```

### assertDatabaseCount

Check the number of records:

```php
/** @test */
public function it_creates_multiple_tasks()
{
    $user = User::factory()->create();

    Task::factory()->count(5)->create(['user_id' => $user->id]);

    $this->assertDatabaseCount('tasks', 5);
}
```

### assertDatabaseEmpty

Check a table is empty:

```php
/** @test */
public function database_starts_empty()
{
    $this->assertDatabaseEmpty('tasks');
}
```

### assertModelExists / assertModelMissing

Check if a model exists in database:

```php
/** @test */
public function it_saves_model_to_database()
{
    $task = Task::factory()->create();

    $this->assertModelExists($task);
}

/** @test */
public function it_removes_model_from_database()
{
    $task = Task::factory()->create();
    $task->delete();

    $this->assertModelMissing($task);
}
```

### assertSoftDeleted

For models using soft deletes:

```php
/** @test */
public function it_soft_deletes_task()
{
    $task = Task::factory()->create();

    $task->delete(); // Soft delete

    $this->assertSoftDeleted('tasks', [
        'id' => $task->id
    ]);

    // Or
    $this->assertSoftDeleted($task);
}
```

### assertNotSoftDeleted

```php
/** @test */
public function it_does_not_soft_delete_task()
{
    $task = Task::factory()->create();

    $this->assertNotSoftDeleted($task);
}
```

---

## Testing Migrations

Test that your migrations create tables correctly:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function tasks_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable('tasks'));

        $this->assertTrue(Schema::hasColumns('tasks', [
            'id',
            'user_id',
            'title',
            'description',
            'completed',
            'created_at',
            'updated_at',
        ]));
    }

    /** @test */
    public function users_table_has_expected_columns()
    {
        $columns = Schema::getColumnListing('users');

        $this->assertContains('id', $columns);
        $this->assertContains('name', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('password', $columns);
    }
}
```

---

## Testing Relationships

Test that Eloquent relationships work correctly:

### One-to-Many

```php
/** @test */
public function user_has_many_tasks()
{
    $user = User::factory()->create();
    $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);

    $this->assertCount(3, $user->tasks);
    $this->assertInstanceOf(Task::class, $user->tasks->first());

    // Check all tasks belong to user
    $user->tasks->each(function ($task) use ($user) {
        $this->assertEquals($user->id, $task->user_id);
    });
}

/** @test */
public function task_belongs_to_user()
{
    $user = User::factory()->create();
    $task = Task::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(User::class, $task->user);
    $this->assertEquals($user->id, $task->user->id);
}
```

### Many-to-Many

```php
/** @test */
public function post_can_have_many_tags()
{
    $post = Post::factory()->create();
    $tags = Tag::factory()->count(3)->create();

    $post->tags()->attach($tags);

    $this->assertCount(3, $post->tags);

    // Check pivot table
    $this->assertDatabaseCount('post_tag', 3);

    $tags->each(function ($tag) use ($post) {
        $this->assertDatabaseHas('post_tag', [
            'post_id' => $post->id,
            'tag_id' => $tag->id,
        ]);
    });
}

/** @test */
public function tag_can_have_many_posts()
{
    $tag = Tag::factory()->create();
    $posts = Post::factory()->count(4)->create();

    $tag->posts()->attach($posts);

    $this->assertCount(4, $tag->posts);
}
```

### Has One

```php
/** @test */
public function user_has_one_profile()
{
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $this->assertInstanceOf(Profile::class, $user->profile);
    $this->assertEquals($profile->id, $user->profile->id);
}
```

### Polymorphic Relationships

```php
/** @test */
public function post_can_have_comments()
{
    $post = Post::factory()->create();
    $comment = Comment::factory()->create([
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
    ]);

    $this->assertCount(1, $post->comments);
    $this->assertEquals($comment->id, $post->comments->first()->id);
}

/** @test */
public function comment_can_belong_to_post_or_video()
{
    $post = Post::factory()->create();
    $video = Video::factory()->create();

    $postComment = Comment::factory()->create([
        'commentable_type' => Post::class,
        'commentable_id' => $post->id,
    ]);

    $videoComment = Comment::factory()->create([
        'commentable_type' => Video::class,
        'commentable_id' => $video->id,
    ]);

    $this->assertInstanceOf(Post::class, $postComment->commentable);
    $this->assertInstanceOf(Video::class, $videoComment->commentable);
}
```

---

## Testing Query Scopes

Test that query scopes filter correctly:

```php
/** @test */
public function completed_scope_returns_only_completed_tasks()
{
    Task::factory()->completed()->count(3)->create();
    Task::factory()->pending()->count(2)->create();

    $completed = Task::completed()->get();

    $this->assertCount(3, $completed);
    $completed->each(function ($task) {
        $this->assertTrue($task->completed);
    });
}

/** @test */
public function published_scope_returns_only_published_posts()
{
    Post::factory()->published()->count(4)->create();
    Post::factory()->draft()->count(3)->create();

    $published = Post::published()->get();

    $this->assertCount(4, $published);
    $published->each(function ($post) {
        $this->assertEquals('published', $post->status);
    });
}

/** @test */
public function for_user_scope_returns_only_user_tasks()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Task::factory()->count(5)->create(['user_id' => $user1->id]);
    Task::factory()->count(3)->create(['user_id' => $user2->id]);

    $user1Tasks = Task::forUser($user1)->get();

    $this->assertCount(5, $user1Tasks);
    $user1Tasks->each(function ($task) use ($user1) {
        $this->assertEquals($user1->id, $task->user_id);
    });
}
```

---

## Testing Complex Queries

Test queries with multiple conditions, joins, aggregations:

```php
/** @test */
public function it_returns_users_with_completed_tasks_count()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Task::factory()->completed()->count(3)->create(['user_id' => $user1->id]);
    Task::factory()->pending()->count(2)->create(['user_id' => $user1->id]);
    Task::factory()->completed()->count(1)->create(['user_id' => $user2->id]);

    $users = User::withCount(['tasks' => function ($query) {
        $query->where('completed', true);
    }])->get();

    $this->assertEquals(3, $users->firstWhere('id', $user1->id)->tasks_count);
    $this->assertEquals(1, $users->firstWhere('id', $user2->id)->tasks_count);
}

/** @test */
public function it_returns_tasks_with_comments_count()
{
    $task1 = Task::factory()->create();
    $task2 = Task::factory()->create();

    Comment::factory()->count(5)->create(['task_id' => $task1->id]);
    Comment::factory()->count(2)->create(['task_id' => $task2->id]);

    $tasks = Task::withCount('comments')->get();

    $this->assertEquals(5, $tasks->firstWhere('id', $task1->id)->comments_count);
    $this->assertEquals(2, $tasks->firstWhere('id', $task2->id)->comments_count);
}

/** @test */
public function it_returns_average_task_completion_rate()
{
    $user = User::factory()->create();

    Task::factory()->completed()->count(7)->create(['user_id' => $user->id]);
    Task::factory()->pending()->count(3)->create(['user_id' => $user->id]);

    $completionRate = $user->tasks()
        ->selectRaw('AVG(completed) * 100 as rate')
        ->value('rate');

    $this->assertEquals(70, $completionRate);
}
```

---

## Seeding Test Data

Use seeders to create consistent test data:

### Create a Seeder for Tests

```bash
php artisan make:seeder TestDataSeeder
```

In `database/seeders/TestDataSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create specific users
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@test.com',
            'role' => 'user',
        ]);

        // Create tasks
        Task::factory()->count(10)->create(['user_id' => $admin->id]);
        Task::factory()->count(5)->create(['user_id' => $user->id]);

        // Create tags
        Tag::factory()->count(5)->create();
    }
}
```

### Use in Tests

```php
/** @test */
public function it_lists_all_tasks_for_admin()
{
    $this->seed(TestDataSeeder::class);

    $admin = User::where('email', 'admin@test.com')->first();

    $response = $this->actingAs($admin)
        ->get('/tasks');

    $response->assertOk();
    // Admin sees all tasks (10 + 5 = 15)
    // Test logic...
}
```

### Seed Once for Multiple Tests

```php
class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run seeder before each test
        $this->seed(TestDataSeeder::class);
    }

    /** @test */
    public function test_one()
    {
        // Seeded data is available
    }

    /** @test */
    public function test_two()
    {
        // Seeded data is available again (fresh)
    }
}
```

---

## Testing Database Transactions

Test that transactions work correctly:

```php
/** @test */
public function it_rolls_back_on_error()
{
    $this->assertDatabaseCount('tasks', 0);

    try {
        DB::transaction(function () {
            Task::factory()->create(['title' => 'Task 1']);
            Task::factory()->create(['title' => 'Task 2']);

            // Force an error
            throw new \Exception('Something went wrong');
        });
    } catch (\Exception $e) {
        // Exception caught
    }

    // No tasks should be saved (transaction rolled back)
    $this->assertDatabaseCount('tasks', 0);
}

/** @test */
public function it_commits_on_success()
{
    $this->assertDatabaseCount('tasks', 0);

    DB::transaction(function () {
        Task::factory()->create(['title' => 'Task 1']);
        Task::factory()->create(['title' => 'Task 2']);
    });

    // Tasks should be saved
    $this->assertDatabaseCount('tasks', 2);
}
```

---

## Testing Database Constraints

Test that database constraints (unique, foreign keys, etc.) work:

### Unique Constraint

```php
/** @test */
public function email_must_be_unique()
{
    User::factory()->create(['email' => 'john@example.com']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    User::factory()->create(['email' => 'john@example.com']);
}
```

### Foreign Key Constraint

```php
/** @test */
public function task_requires_valid_user_id()
{
    $this->expectException(\Illuminate\Database\QueryException::class);

    // Try to create task with non-existent user_id
    Task::factory()->create(['user_id' => 999999]);
}

/** @test */
public function deleting_user_deletes_their_tasks()
{
    $user = User::factory()->create();
    Task::factory()->count(3)->create(['user_id' => $user->id]);

    $this->assertDatabaseCount('tasks', 3);

    $user->delete();

    // If cascade delete is set up, tasks should be deleted
    $this->assertDatabaseCount('tasks', 0);
}
```

---

## Testing with Multiple Databases

If your app uses multiple database connections:

```php
/** @test */
public function it_works_with_multiple_databases()
{
    // Use default connection
    $user = User::factory()->create();
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // Use second connection
    $analytics = Analytics::factory()->on('analytics')->create();
    $this->assertDatabaseHas('analytics', ['id' => $analytics->id], 'analytics');
}
```

---

## Performance Testing Queries

Test query performance (number of queries, N+1 problems):

```php
/** @test */
public function it_avoids_n_plus_one_queries()
{
    $user = User::factory()->create();
    Task::factory()->count(10)->create(['user_id' => $user->id]);

    // Count queries
    DB::enableQueryLog();

    // ❌ BAD - N+1 problem (1 + 10 queries)
    $tasks = Task::all();
    foreach ($tasks as $task) {
        $task->user->name; // Triggers query for each task
    }
    $badQueryCount = count(DB::getQueryLog());

    DB::flushQueryLog();

    // ✅ GOOD - Eager loading (2 queries total)
    $tasks = Task::with('user')->get();
    foreach ($tasks as $task) {
        $task->user->name; // No additional queries
    }
    $goodQueryCount = count(DB::getQueryLog());

    $this->assertLessThan($badQueryCount, $goodQueryCount);
    $this->assertEquals(2, $goodQueryCount); // 1 for tasks, 1 for users
}
```

---

## Best Practices

### 1. Always Use RefreshDatabase

```php
use RefreshDatabase; // Essential for database tests
```

### 2. Use Factories for Test Data

```php
// ✅ GOOD
$user = User::factory()->create();

// ❌ BAD
$user = User::create([
    'name' => 'Test',
    'email' => 'test@test.com',
    'password' => bcrypt('password'),
]);
```

### 3. Test Both Success and Failure Cases

```php
/** @test */
public function it_creates_valid_task() { ... }

/** @test */
public function it_fails_to_create_invalid_task() { ... }
```

### 4. Test Relationships in Both Directions

```php
/** @test */
public function user_has_many_tasks() { ... }

/** @test */
public function task_belongs_to_user() { ... }
```

### 5. Keep Database Tests Fast

```php
// Minimize database operations
// Use in-memory SQLite for testing (faster)
```

In `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## Quick Quiz

1. **What does the `RefreshDatabase` trait do?**

2. **How do you check if a database record exists in tests?**

3. **What's the difference between `RefreshDatabase` and `DatabaseMigrations`?**

4. **How do you test that a relationship works correctly?**

5. **Why use factories instead of manually creating test data?**

---

## Practice Exercise

**Build comprehensive database tests for a Blog system**:

1. Models:
   - User (has many posts, has many comments)
   - Post (belongs to user, has many comments, has many tags)
   - Comment (belongs to user, belongs to post)
   - Tag (has many posts)

2. Write tests for:
   - All relationships (in both directions)
   - Query scopes (published, draft, by user)
   - Complex queries (posts with comment count, users with post count)
   - Database constraints (unique email, foreign keys)
   - Cascade deletes
   - Soft deletes
   - Migrations (tables have correct columns)

**Requirements**:
- At least 25 database tests
- Test all relationships
- Test scopes and complex queries
- Use factories
- Use RefreshDatabase
- Test edge cases

---

## What's Next?

In the next lesson, you'll learn about **Test-Driven Development (TDD)** - the practice of writing tests BEFORE writing code. You'll learn the Red-Green-Refactor cycle and build a complete feature using TDD from scratch!

Database testing ensures your data layer works correctly. Master it, and you'll catch bugs before they reach production!
