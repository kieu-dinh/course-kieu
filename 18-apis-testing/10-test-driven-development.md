# Lesson 10: Test-Driven Development (TDD)

**Duration**: 3-4 hours
**Prerequisites**: Lessons 6-9 (All testing lessons)
**Objective**: Master Test-Driven Development by building features test-first using the Red-Green-Refactor cycle

---

## Introduction

So far, you've written tests **after** writing code. **Test-Driven Development (TDD)** flips this around: you write tests **before** writing code.

It sounds backwards, but TDD has powerful benefits:
- **Better design** - Tests force you to think about API design first
- **Higher confidence** - Every line of code is tested
- **Less debugging** - Bugs are caught immediately
- **Living documentation** - Tests show how code should work
- **Fearless refactoring** - Tests catch regressions

Think of TDD like building with blueprints: you plan before you build, ensuring everything fits together correctly.

---

## The Red-Green-Refactor Cycle

TDD follows a simple cycle:

### 1. RED: Write a Failing Test

Write a test for functionality that doesn't exist yet. Run it. **It fails** (red).

```php
/** @test */
public function it_calculates_total_price()
{
    $cart = new ShoppingCart();
    $cart->addItem('Laptop', 1000, 1);

    $total = $cart->getTotal();

    $this->assertEquals(1000, $total);
}
```

Run test: **FAILS** (ShoppingCart class doesn't exist)

### 2. GREEN: Write Minimal Code to Pass

Write just enough code to make the test pass. Don't worry about perfect code yet.

```php
class ShoppingCart
{
    protected array $items = [];

    public function addItem(string $name, float $price, int $quantity): void
    {
        $this->items[] = [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
        ];
    }

    public function getTotal(): float
    {
        return array_sum(array_column($this->items, 'price'));
    }
}
```

Run test: **PASSES** (green)

### 3. REFACTOR: Improve the Code

Now improve the code while keeping tests green.

```php
public function getTotal(): float
{
    return array_reduce($this->items, function ($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
}
```

Run test: **Still PASSES** (green)

**Repeat the cycle**: Red → Green → Refactor → Red → Green → Refactor...

---

## Why TDD Works

### 1. Forces Good Design

When you write tests first, you think from the user's perspective:
- "How should this API work?"
- "What's the simplest interface?"
- "What should happen in edge cases?"

This leads to better, more usable code.

### 2. Provides Fast Feedback

You know immediately if your code works. No manual testing, no waiting.

### 3. Prevents Over-Engineering

You only write code that's needed to pass tests. No "maybe we'll need this later" code.

### 4. Acts as Documentation

Tests show exactly how to use your code, with real examples.

---

## TDD Example: Building a Task Manager

Let's build a complete feature using TDD.

### Step 1: Define Requirements

We want a TaskManager that can:
- Add tasks
- Get all tasks
- Mark tasks as completed
- Get only completed tasks
- Get only pending tasks
- Delete tasks

### Step 2: RED - Write First Test

```bash
php artisan make:test TaskManagerTest --unit
```

In `tests/Unit/TaskManagerTest.php`:

```php
<?php

namespace Tests\Unit;

use App\TaskManager;
use PHPUnit\Framework\TestCase;

class TaskManagerTest extends TestCase
{
    /** @test */
    public function it_can_add_a_task()
    {
        $manager = new TaskManager();

        $manager->addTask('Buy groceries');

        $this->assertCount(1, $manager->getTasks());
    }
}
```

Run test:

```bash
php artisan test --filter=TaskManagerTest
```

**Result**: FAILS - TaskManager class doesn't exist

### Step 3: GREEN - Make It Pass

Create `app/TaskManager.php`:

```php
<?php

namespace App;

class TaskManager
{
    protected array $tasks = [];

    public function addTask(string $title): void
    {
        $this->tasks[] = $title;
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }
}
```

Run test: **PASSES**

### Step 4: RED - Add Another Test

```php
/** @test */
public function it_stores_task_with_details()
{
    $manager = new TaskManager();

    $manager->addTask('Buy groceries');

    $task = $manager->getTasks()[0];
    $this->assertEquals('Buy groceries', $task['title']);
    $this->assertFalse($task['completed']);
}
```

Run test: **FAILS** - Task is a string, not an array

### Step 5: GREEN - Make It Pass

```php
public function addTask(string $title): void
{
    $this->tasks[] = [
        'title' => $title,
        'completed' => false,
    ];
}
```

Run test: **PASSES**

### Step 6: RED - Test Marking Complete

```php
/** @test */
public function it_marks_task_as_completed()
{
    $manager = new TaskManager();
    $manager->addTask('Buy groceries');

    $manager->markAsCompleted(0);

    $task = $manager->getTasks()[0];
    $this->assertTrue($task['completed']);
}
```

Run test: **FAILS** - markAsCompleted() doesn't exist

### Step 7: GREEN - Implement It

```php
public function markAsCompleted(int $index): void
{
    if (isset($this->tasks[$index])) {
        $this->tasks[$index]['completed'] = true;
    }
}
```

Run test: **PASSES**

### Step 8: RED - Test Filtering

```php
/** @test */
public function it_returns_only_completed_tasks()
{
    $manager = new TaskManager();
    $manager->addTask('Task 1');
    $manager->addTask('Task 2');
    $manager->addTask('Task 3');
    $manager->markAsCompleted(0);
    $manager->markAsCompleted(2);

    $completed = $manager->getCompletedTasks();

    $this->assertCount(2, $completed);
    $this->assertEquals('Task 1', $completed[0]['title']);
    $this->assertEquals('Task 3', $completed[1]['title']);
}
```

Run test: **FAILS** - getCompletedTasks() doesn't exist

### Step 9: GREEN - Implement It

```php
public function getCompletedTasks(): array
{
    return array_filter($this->tasks, fn($task) => $task['completed']);
}
```

Run test: **PASSES** (but notice array keys are preserved)

### Step 10: REFACTOR - Fix Array Keys

```php
public function getCompletedTasks(): array
{
    return array_values(
        array_filter($this->tasks, fn($task) => $task['completed'])
    );
}
```

Run test: **Still PASSES**

### Step 11: Continue TDD Cycle

Keep adding tests and implementing:

```php
/** @test */
public function it_returns_only_pending_tasks()
{
    $manager = new TaskManager();
    $manager->addTask('Task 1');
    $manager->addTask('Task 2');
    $manager->addTask('Task 3');
    $manager->markAsCompleted(1);

    $pending = $manager->getPendingTasks();

    $this->assertCount(2, $pending);
    $this->assertFalse($pending[0]['completed']);
    $this->assertFalse($pending[1]['completed']);
}
```

Implementation:

```php
public function getPendingTasks(): array
{
    return array_values(
        array_filter($this->tasks, fn($task) => !$task['completed'])
    );
}
```

### Step 12: Test Edge Cases

```php
/** @test */
public function it_handles_invalid_task_index()
{
    $manager = new TaskManager();
    $manager->addTask('Task 1');

    $manager->markAsCompleted(999); // Invalid index

    // Should not throw error
    $this->assertFalse($manager->getTasks()[0]['completed']);
}

/** @test */
public function it_deletes_a_task()
{
    $manager = new TaskManager();
    $manager->addTask('Task 1');
    $manager->addTask('Task 2');

    $manager->deleteTask(0);

    $this->assertCount(1, $manager->getTasks());
    $this->assertEquals('Task 2', $manager->getTasks()[0]['title']);
}
```

Implementation:

```php
public function deleteTask(int $index): void
{
    if (isset($this->tasks[$index])) {
        unset($this->tasks[$index]);
        $this->tasks = array_values($this->tasks);
    }
}
```

---

## TDD with Laravel: Building an API Endpoint

Let's use TDD to build a Task API endpoint.

### Step 1: RED - Feature Test

```bash
php artisan make:test Api/TaskControllerTest
```

In `tests/Feature/Api/TaskControllerTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_users_can_create_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'My Task'
            ]);

        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'title' => 'My Task',
                'completed' => false
            ]
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'My Task',
            'user_id' => $user->id
        ]);
    }
}
```

Run test: **FAILS** - Route doesn't exist

### Step 2: GREEN - Create Route

In `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/tasks', [TaskController::class, 'store']);
});
```

Run test: **FAILS** - Controller doesn't exist

### Step 3: GREEN - Create Controller

```bash
php artisan make:controller Api/TaskController --api
```

In `app/Http/Controllers/Api/TaskController.php`:

```php
public function store(Request $request)
{
    $task = $request->user()->tasks()->create([
        'title' => $request->title,
        'completed' => false,
    ]);

    return response()->json([
        'data' => [
            'title' => $task->title,
            'completed' => $task->completed,
        ]
    ], 201);
}
```

Run test: **FAILS** - Tasks table doesn't exist

### Step 4: GREEN - Create Migration and Model

```bash
php artisan make:model Task -m
```

In migration:

```php
Schema::create('tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->boolean('completed')->default(false);
    $table->timestamps();
});
```

In `app/Models/Task.php`:

```php
protected $fillable = ['title', 'completed', 'user_id'];

public function user()
{
    return $this->belongsTo(User::class);
}
```

In `app/Models/User.php`:

```php
public function tasks()
{
    return $this->hasMany(Task::class);
}
```

Run migration:

```bash
php artisan migrate
```

Run test: **PASSES**

### Step 5: RED - Test Validation

```php
/** @test */
public function creating_task_requires_title()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['title']);
}
```

Run test: **FAILS** - No validation yet

### Step 6: GREEN - Add Validation

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
    ]);

    $task = $request->user()->tasks()->create([
        'title' => $validated['title'],
        'completed' => false,
    ]);

    return response()->json([
        'data' => [
            'title' => $task->title,
            'completed' => $task->completed,
        ]
    ], 201);
}
```

Run test: **PASSES**

### Step 7: REFACTOR - Use Resource

Create resource:

```bash
php artisan make:resource TaskResource
```

In `app/Http/Resources/TaskResource.php`:

```php
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'completed' => (bool) $this->completed,
        'created_at' => $this->created_at->toIso8601String(),
    ];
}
```

Update controller:

```php
use App\Http\Resources\TaskResource;

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
    ]);

    $task = $request->user()->tasks()->create($validated);

    return new TaskResource($task);
}
```

Run test: **Still PASSES**

### Step 8: Continue TDD

Keep adding tests and implementing:

```php
/** @test */
public function guests_cannot_create_tasks()
{
    $response = $this->postJson('/api/tasks', [
        'title' => 'Task'
    ]);

    $response->assertUnauthorized();
}

/** @test */
public function users_can_list_their_tasks()
{
    // Implement index()
}

/** @test */
public function users_can_update_their_tasks()
{
    // Implement update()
}

/** @test */
public function users_cannot_update_other_users_tasks()
{
    // Test authorization
}
```

---

## TDD Best Practices

### 1. Write the Smallest Test Possible

```php
// ✅ GOOD - Tests one thing
/** @test */
public function it_adds_item_to_cart()
{
    $cart = new Cart();
    $cart->addItem('Laptop', 1000);
    $this->assertCount(1, $cart->getItems());
}

// ❌ BAD - Tests too much at once
/** @test */
public function it_handles_cart_operations()
{
    $cart = new Cart();
    $cart->addItem('Laptop', 1000);
    $cart->addItem('Mouse', 20);
    $cart->removeItem('Mouse');
    $cart->applyDiscount(0.1);
    $this->assertEquals(900, $cart->getTotal());
}
```

### 2. Write the Simplest Code to Pass

```php
// First iteration - hardcode if needed
public function getTotal(): float
{
    return 1000; // Makes first test pass
}

// Next iteration - implement real logic
public function getTotal(): float
{
    return array_sum(array_column($this->items, 'price'));
}
```

### 3. Refactor Only When Tests Pass

Never refactor with failing tests. Always keep them green.

### 4. Test Behavior, Not Implementation

```php
// ✅ GOOD - Tests behavior
/** @test */
public function it_returns_completed_tasks()
{
    // Test what the method does
}

// ❌ BAD - Tests implementation details
/** @test */
public function it_uses_array_filter_for_completed_tasks()
{
    // Don't test HOW it works
}
```

### 5. Don't Skip the Refactor Step

After tests pass, always look for improvements:
- Duplicate code
- Long methods
- Complex logic
- Better names

### 6. Keep Tests Fast

Slow tests discourage running them frequently.

### 7. One Assert Per Test (When Possible)

```php
// ✅ GOOD
/** @test */
public function it_has_title()
{
    $task = new Task(['title' => 'My Task']);
    $this->assertEquals('My Task', $task->title);
}

/** @test */
public function it_is_not_completed_by_default()
{
    $task = new Task(['title' => 'My Task']);
    $this->assertFalse($task->completed);
}

// ❌ LESS IDEAL
/** @test */
public function it_creates_task_with_defaults()
{
    $task = new Task(['title' => 'My Task']);
    $this->assertEquals('My Task', $task->title);
    $this->assertFalse($task->completed);
    $this->assertNull($task->due_date);
}
```

---

## Common TDD Mistakes

### 1. Writing Tests After Code

That's not TDD! Write tests first.

### 2. Writing Too Much Code at Once

Make small steps. Write one test, make it pass, repeat.

### 3. Not Running Tests Frequently

Run tests after every change. Get immediate feedback.

### 4. Skipping Refactoring

Don't accumulate technical debt. Refactor when tests are green.

### 5. Testing Implementation Details

Focus on behavior, not how it's implemented.

---

## TDD Benefits in Practice

### 1. Design Improvement

TDD forces you to think about:
- Method names (they appear in tests first)
- Parameter types (you use them before defining)
- Return values (you assert on them first)
- Edge cases (you write tests for them)

### 2. Confidence

Every feature is tested. You know it works.

### 3. Regression Prevention

Old features keep working as you add new ones.

### 4. Refactoring Safety

Change code confidently - tests catch breaks.

### 5. Documentation

Tests show exactly how to use your code.

---

## When NOT to Use TDD

TDD isn't always appropriate:

**Don't use TDD for**:
- Prototyping/exploring ideas
- UI layouts and design
- Integrations you can't control
- Very simple, obvious code

**Do use TDD for**:
- Business logic
- Algorithms
- API endpoints
- Data transformations
- Complex workflows

---

## Quick Quiz

1. **What are the three steps of the Red-Green-Refactor cycle?**

2. **Why write tests before code?**

3. **What does "write the simplest code to pass" mean?**

4. **When should you refactor?**

5. **What's the benefit of small test steps?**

---

## Practice Exercise

**Build a URL Shortener using TDD**:

### Requirements:
1. Shorten long URLs to short codes
2. Redirect short codes to original URLs
3. Track click counts
4. Validate URLs
5. Prevent duplicate URLs
6. Custom short codes (optional)

### Steps:
1. **Start with tests**:
   - Test URL shortening
   - Test URL retrieval
   - Test click tracking
   - Test validation
   - Test duplicates
   - Test custom codes

2. **Use Red-Green-Refactor**:
   - Write one failing test
   - Make it pass with minimal code
   - Refactor
   - Repeat

3. **Build the feature**:
   - Model: `Url` (original_url, short_code, clicks)
   - Service: `UrlShortenerService`
   - Controller: `UrlController`
   - Routes: POST /api/shorten, GET /{code}

**Requirements**:
- At least 15 tests
- Test-first approach (write tests before code)
- Small incremental steps
- Refactor as you go
- Test edge cases

**Deliverables**:
- Complete test suite
- Working URL shortener
- Clean, refactored code

---

## What's Next?

Congratulations! You've completed Module 18 - APIs & Testing! You've learned:
- Laravel API routes and resources
- Sanctum authentication
- API versioning
- Rate limiting
- PHPUnit basics
- Unit tests
- Feature tests
- Database testing
- Test-Driven Development

In Module 19, you'll learn about **Architecture & Design Patterns** - how to organize code using service layers, repositories, SOLID principles, and clean architecture patterns.

TDD is a discipline that takes practice. Start small, build the habit, and you'll write better code!
