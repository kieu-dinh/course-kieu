# Lesson 7: Unit Tests in Laravel

**Duration**: 3-4 hours
**Prerequisites**: Lesson 6 - PHPUnit Introduction
**Objective**: Learn to write comprehensive unit tests for Laravel models, services, and business logic

---

## Introduction

In the previous lesson, you learned PHPUnit basics with simple classes. Now you'll test **Laravel-specific code**: models, relationships, scopes, custom methods, and business logic.

Unit tests in Laravel focus on:
- Model methods and properties
- Business logic in service classes
- Helper functions
- Custom validation rules
- Value objects and DTOs

**Remember**: Unit tests should be fast, isolated, and test small pieces of functionality.

---

## Testing Laravel Models

Models contain business logic beyond just database access. Let's test them thoroughly.

### Basic Model Test

Create a User model test:

```bash
php artisan make:test UserTest --unit
```

In `tests/Unit/UserTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'email', 'password'];
        $user = new User();

        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();

        $this->assertEquals($hidden, $user->getHidden());
    }

    /** @test */
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = new User();

        $this->assertArrayHasKey('email_verified_at', $user->getCasts());
        $this->assertEquals('datetime', $user->getCasts()['email_verified_at']);
    }
}
```

### Testing Model Methods

Let's add custom methods to test:

In `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime'];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPremium(): bool
    {
        return $this->plan === 'premium' || $this->plan === 'enterprise';
    }

    public function getInitials(): string
    {
        $nameParts = explode(' ', $this->name);
        $initials = '';

        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return $initials;
    }
}
```

Test these methods:

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /** @test */
    public function it_returns_full_name_attribute()
    {
        $user = new User();
        $user->first_name = 'John';
        $user->last_name = 'Doe';

        $this->assertEquals('John Doe', $user->full_name);
    }

    /** @test */
    public function it_checks_if_user_is_admin()
    {
        $admin = new User(['role' => 'admin']);
        $regularUser = new User(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    /** @test */
    public function it_checks_if_user_is_premium()
    {
        $premiumUser = new User(['plan' => 'premium']);
        $enterpriseUser = new User(['plan' => 'enterprise']);
        $freeUser = new User(['plan' => 'free']);

        $this->assertTrue($premiumUser->isPremium());
        $this->assertTrue($enterpriseUser->isPremium());
        $this->assertFalse($freeUser->isPremium());
    }

    /** @test */
    public function it_returns_user_initials()
    {
        $user = new User(['name' => 'John Doe']);
        $this->assertEquals('JD', $user->getInitials());

        $user2 = new User(['name' => 'Mary Jane Watson']);
        $this->assertEquals('MJW', $user2->getInitials());
    }
}
```

Run tests:

```bash
php artisan test --filter=UserTest
```

---

## Testing with Databases (RefreshDatabase)

Most models need database access. Laravel provides `RefreshDatabase` trait for testing.

Change from `PHPUnit\Framework\TestCase` to `Tests\TestCase`:

```php
<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase; // ⚠️ Changed from PHPUnit\Framework\TestCase

class UserTest extends TestCase
{
    use RefreshDatabase; // ⚠️ Added

    /** @test */
    public function it_creates_a_user()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertNotNull($user->id);
        $this->assertEquals('John Doe', $user->name);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function it_updates_user_data()
    {
        $user = User::factory()->create(['name' => 'John']);

        $user->update(['name' => 'Jane']);

        $this->assertEquals('Jane', $user->fresh()->name);
        $this->assertDatabaseHas('users', ['name' => 'Jane']);
    }

    /** @test */
    public function it_deletes_a_user()
    {
        $user = User::factory()->create();

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
```

**Key differences**:
- `Tests\TestCase` - Provides Laravel features
- `RefreshDatabase` - Resets database after each test
- `User::factory()` - Uses factories to create test data

---

## Factories for Test Data

Factories generate fake data for testing. Laravel includes a User factory by default.

### Using Factories

```php
/** @test */
public function it_creates_multiple_users()
{
    // Create 1 user
    $user = User::factory()->create();
    $this->assertDatabaseCount('users', 1);

    // Create 5 users
    $users = User::factory()->count(5)->create();
    $this->assertCount(5, $users);
    $this->assertDatabaseCount('users', 6); // 1 + 5
}

/** @test */
public function it_creates_user_with_custom_attributes()
{
    $user = User::factory()->create([
        'name' => 'Custom Name',
        'email' => 'custom@example.com'
    ]);

    $this->assertEquals('Custom Name', $user->name);
    $this->assertEquals('custom@example.com', $user->email);
}

/** @test */
public function it_creates_admin_user()
{
    $admin = User::factory()->create(['role' => 'admin']);

    $this->assertTrue($admin->isAdmin());
}
```

### Creating Custom Factories

Create a Task model and factory:

```bash
php artisan make:model Task -mf
```

In `database/factories/TaskFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'completed' => false,
            'user_id' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed' => false,
        ]);
    }
}
```

Use in tests:

```php
/** @test */
public function it_creates_completed_task()
{
    $task = Task::factory()->completed()->create();

    $this->assertTrue($task->completed);
}

/** @test */
public function it_creates_pending_task()
{
    $task = Task::factory()->pending()->create();

    $this->assertFalse($task->completed);
}

/** @test */
public function it_belongs_to_user()
{
    $task = Task::factory()->create();

    $this->assertInstanceOf(User::class, $task->user);
    $this->assertNotNull($task->user_id);
}
```

---

## Testing Relationships

Test that model relationships work correctly.

### One-to-Many Relationship

```php
// In User model
public function tasks()
{
    return $this->hasMany(Task::class);
}

// In Task model
public function user()
{
    return $this->belongsTo(User::class);
}
```

Test it:

```php
/** @test */
public function user_has_many_tasks()
{
    $user = User::factory()->create();
    $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);

    $this->assertCount(3, $user->tasks);
    $this->assertInstanceOf(Task::class, $user->tasks->first());
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

### Many-to-Many Relationship

```php
// Models
class Post extends Model
{
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}

class Tag extends Model
{
    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
```

Test it:

```php
/** @test */
public function post_can_have_many_tags()
{
    $post = Post::factory()->create();
    $tags = Tag::factory()->count(3)->create();

    $post->tags()->attach($tags);

    $this->assertCount(3, $post->tags);
    $this->assertInstanceOf(Tag::class, $post->tags->first());
}

/** @test */
public function tag_can_have_many_posts()
{
    $tag = Tag::factory()->create();
    $posts = Post::factory()->count(5)->create();

    $tag->posts()->attach($posts);

    $this->assertCount(5, $tag->posts);
}
```

---

## Testing Scopes

Scopes filter queries. Test them to ensure they work correctly.

```php
// In Task model
public function scopeCompleted($query)
{
    return $query->where('completed', true);
}

public function scopePending($query)
{
    return $query->where('completed', false);
}

public function scopeForUser($query, User $user)
{
    return $query->where('user_id', $user->id);
}
```

Test scopes:

```php
/** @test */
public function scope_returns_completed_tasks()
{
    Task::factory()->completed()->count(3)->create();
    Task::factory()->pending()->count(2)->create();

    $completed = Task::completed()->get();

    $this->assertCount(3, $completed);
    $this->assertTrue($completed->every(fn ($task) => $task->completed === true));
}

/** @test */
public function scope_returns_pending_tasks()
{
    Task::factory()->completed()->count(3)->create();
    Task::factory()->pending()->count(2)->create();

    $pending = Task::pending()->get();

    $this->assertCount(2, $pending);
    $this->assertTrue($pending->every(fn ($task) => $task->completed === false));
}

/** @test */
public function scope_returns_tasks_for_specific_user()
{
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Task::factory()->count(3)->create(['user_id' => $user1->id]);
    Task::factory()->count(2)->create(['user_id' => $user2->id]);

    $user1Tasks = Task::forUser($user1)->get();

    $this->assertCount(3, $user1Tasks);
    $this->assertTrue($user1Tasks->every(fn ($task) => $task->user_id === $user1->id));
}
```

---

## Testing Service Classes

Business logic should live in service classes, not controllers. Let's test them.

### Create a Service Class

```bash
mkdir app/Services
```

Create `app/Services/TaskService.php`:

```php
<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function createTask(User $user, array $data): Task
    {
        return $user->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'completed' => false,
        ]);
    }

    public function markAsCompleted(Task $task): Task
    {
        $task->update(['completed' => true]);
        return $task->fresh();
    }

    public function getUserTaskStats(User $user): array
    {
        $tasks = $user->tasks;

        return [
            'total' => $tasks->count(),
            'completed' => $tasks->where('completed', true)->count(),
            'pending' => $tasks->where('completed', false)->count(),
            'completion_rate' => $tasks->count() > 0
                ? round(($tasks->where('completed', true)->count() / $tasks->count()) * 100, 2)
                : 0,
        ];
    }

    public function deleteCompletedTasks(User $user): int
    {
        return $user->tasks()->where('completed', true)->delete();
    }
}
```

### Test the Service

```bash
php artisan make:test TaskServiceTest --unit
```

In `tests/Unit/TaskServiceTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService();
    }

    /** @test */
    public function it_creates_a_task_for_user()
    {
        $user = User::factory()->create();

        $task = $this->service->createTask($user, [
            'title' => 'Test Task',
            'description' => 'Test Description'
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals($user->id, $task->user_id);
        $this->assertFalse($task->completed);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function it_marks_task_as_completed()
    {
        $task = Task::factory()->pending()->create();

        $updatedTask = $this->service->markAsCompleted($task);

        $this->assertTrue($updatedTask->completed);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'completed' => true
        ]);
    }

    /** @test */
    public function it_calculates_user_task_stats()
    {
        $user = User::factory()->create();
        Task::factory()->completed()->count(6)->create(['user_id' => $user->id]);
        Task::factory()->pending()->count(4)->create(['user_id' => $user->id]);

        $stats = $this->service->getUserTaskStats($user);

        $this->assertEquals(10, $stats['total']);
        $this->assertEquals(6, $stats['completed']);
        $this->assertEquals(4, $stats['pending']);
        $this->assertEquals(60, $stats['completion_rate']); // 6/10 * 100
    }

    /** @test */
    public function it_returns_zero_completion_rate_for_no_tasks()
    {
        $user = User::factory()->create();

        $stats = $this->service->getUserTaskStats($user);

        $this->assertEquals(0, $stats['total']);
        $this->assertEquals(0, $stats['completion_rate']);
    }

    /** @test */
    public function it_deletes_completed_tasks()
    {
        $user = User::factory()->create();
        Task::factory()->completed()->count(3)->create(['user_id' => $user->id]);
        Task::factory()->pending()->count(2)->create(['user_id' => $user->id]);

        $deleted = $this->service->deleteCompletedTasks($user);

        $this->assertEquals(3, $deleted);
        $this->assertDatabaseCount('tasks', 2); // Only pending remain
        $this->assertEquals(2, $user->fresh()->tasks()->count());
    }
}
```

---

## Testing Custom Validation Rules

Laravel allows custom validation rules. Test them too!

### Create Custom Rule

```bash
php artisan make:rule Uppercase
```

In `app/Rules/Uppercase.php`:

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Uppercase implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strtoupper($value) !== $value) {
            $fail("The {$attribute} must be in uppercase.");
        }
    }
}
```

### Test the Rule

```bash
php artisan make:test UppercaseRuleTest --unit
```

In `tests/Unit/UppercaseRuleTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Rules\Uppercase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class UppercaseRuleTest extends TestCase
{
    /** @test */
    public function it_passes_for_uppercase_string()
    {
        $validator = Validator::make(
            ['name' => 'JOHN DOE'],
            ['name' => new Uppercase()]
        );

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function it_fails_for_lowercase_string()
    {
        $validator = Validator::make(
            ['name' => 'john doe'],
            ['name' => new Uppercase()]
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            'The name must be in uppercase.',
            $validator->errors()->first('name')
        );
    }

    /** @test */
    public function it_fails_for_mixed_case_string()
    {
        $validator = Validator::make(
            ['name' => 'John Doe'],
            ['name' => new Uppercase()]
        );

        $this->assertTrue($validator->fails());
    }
}
```

---

## Database Assertions

Laravel provides helpful database assertions:

```php
// Check record exists
$this->assertDatabaseHas('users', [
    'email' => 'john@example.com'
]);

// Check record doesn't exist
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com'
]);

// Check count
$this->assertDatabaseCount('users', 5);

// Check model exists
$this->assertModelExists($user);

// Check model missing (soft deleted or hard deleted)
$this->assertModelMissing($user);

// Check soft deleted
$this->assertSoftDeleted('users', [
    'id' => $user->id
]);
```

---

## Best Practices for Unit Tests

### 1. Keep Unit Tests Fast

```php
// ❌ SLOW - Makes HTTP request
/** @test */
public function it_fetches_data_from_api()
{
    $response = Http::get('https://api.example.com/data');
    // ...
}

// ✅ FAST - Fake HTTP
/** @test */
public function it_fetches_data_from_api()
{
    Http::fake(['*' => Http::response(['data' => 'test'], 200)]);
    $response = Http::get('https://api.example.com/data');
    // ...
}
```

### 2. Test One Thing at a Time

```php
// ❌ BAD - Tests multiple things
/** @test */
public function it_handles_user_operations()
{
    $user = User::factory()->create();
    $this->assertNotNull($user->id);

    $user->update(['name' => 'New Name']);
    $this->assertEquals('New Name', $user->name);

    $user->delete();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
}

// ✅ GOOD - Separate tests
/** @test */
public function it_creates_user()
{
    $user = User::factory()->create();
    $this->assertNotNull($user->id);
}

/** @test */
public function it_updates_user()
{
    $user = User::factory()->create();
    $user->update(['name' => 'New Name']);
    $this->assertEquals('New Name', $user->name);
}

/** @test */
public function it_deletes_user()
{
    $user = User::factory()->create();
    $user->delete();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
}
```

### 3. Use Factories, Not Manual Creation

```php
// ❌ TEDIOUS
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);

// ✅ SIMPLE
$user = User::factory()->create();
```

### 4. Clean Descriptive Names

```php
// ✅ GOOD
test_it_calculates_total_with_discount
test_it_throws_exception_for_invalid_email
test_user_can_only_see_own_tasks

// ❌ BAD
test_total
test_email
test_tasks
```

---

## Quick Quiz

1. **When should you use `RefreshDatabase` trait?**

2. **What's the difference between `Tests\TestCase` and `PHPUnit\Framework\TestCase`?**

3. **Why use factories instead of manually creating records?**

4. **How do you test that a database record exists?**

5. **What should unit tests focus on in Laravel?**

---

## Practice Exercise

**Build and test a BlogService**:

1. Models:
   - Post (title, content, published, user_id)
   - Comment (content, post_id, user_id)

2. Create `PostService` with methods:
   - `createPost(User $user, array $data): Post`
   - `publishPost(Post $post): Post`
   - `unpublishPost(Post $post): Post`
   - `getPostStats(Post $post): array` (views, comments, likes)
   - `deletePost(Post $post): bool`

3. Write comprehensive unit tests:
   - Test each service method
   - Test model relationships
   - Test scopes (published, unpublished)
   - Test custom methods
   - Use factories for data
   - Test edge cases

**Requirements**:
- At least 15 tests
- Use `RefreshDatabase`
- Use factories
- Test relationships
- Test edge cases
- Descriptive test names

---

## What's Next?

In the next lesson, you'll learn about **Feature Tests** - testing complete features from HTTP requests to responses, including authentication, API endpoints, and full workflows. Feature tests are bigger than unit tests and test how components work together!

Unit tests are the foundation of a reliable codebase. Master them, and you'll write better code!
