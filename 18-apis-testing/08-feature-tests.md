# Lesson 8: Feature Tests

**Duration**: 3-4 hours
**Prerequisites**: Lessons 6-7 (PHPUnit, Unit Tests)
**Objective**: Learn to write feature tests that test complete application workflows from HTTP request to response

---

## Introduction

**Unit tests** test small pieces in isolation (a single method). **Feature tests** test entire features from start to finish - like a user interacting with your application.

Think of it this way:
- **Unit test**: "Does this calculator add numbers correctly?"
- **Feature test**: "Can a user register, log in, create a task, and log out?"

Feature tests:
- Make HTTP requests to your application
- Test controllers, middleware, validation, database
- Verify responses (status, content, redirects)
- Check database state after actions
- Test authentication and authorization

**They're slower than unit tests but more realistic.**

---

## Feature Test Structure

Feature tests extend `Tests\TestCase` and use `RefreshDatabase`:

```php
<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_the_homepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
```

**Key parts**:
- `$this->get()` - Makes HTTP GET request
- `$response->assertStatus(200)` - Verifies 200 OK response

---

## Making HTTP Requests

Laravel provides methods for all HTTP verbs:

```php
// GET request
$response = $this->get('/posts');
$response = $this->get('/posts/1');

// POST request
$response = $this->post('/posts', [
    'title' => 'My Post',
    'content' => 'Post content'
]);

// PUT request
$response = $this->put('/posts/1', [
    'title' => 'Updated Title'
]);

// PATCH request
$response = $this->patch('/posts/1', [
    'title' => 'Partially Updated'
]);

// DELETE request
$response = $this->delete('/posts/1');
```

### With Headers

```php
$response = $this->withHeaders([
    'Accept' => 'application/json',
    'X-Custom-Header' => 'value'
])->get('/api/tasks');
```

### With Authentication

```php
$user = User::factory()->create();

$response = $this->actingAs($user)->get('/dashboard');
```

### JSON Requests

```php
$response = $this->postJson('/api/tasks', [
    'title' => 'My Task'
]);

// Automatically sets:
// - Content-Type: application/json
// - Accept: application/json
```

---

## Response Assertions

After making a request, assert the response is correct:

### Status Codes

```php
$response->assertStatus(200);         // Specific status
$response->assertOk();                // 200
$response->assertCreated();           // 201
$response->assertNoContent();         // 204
$response->assertNotFound();          // 404
$response->assertForbidden();         // 403
$response->assertUnauthorized();      // 401
$response->assertUnprocessable();     // 422
```

### Content

```php
// Check response contains text
$response->assertSee('Welcome');
$response->assertSee('Hello World');
$response->assertDontSee('Error');

// Check JSON structure
$response->assertJson([
    'success' => true,
    'data' => [
        'title' => 'My Task'
    ]
]);

// Check JSON has key
$response->assertJsonPath('data.title', 'My Task');

// Check JSON structure matches
$response->assertJsonStructure([
    'data' => [
        'id',
        'title',
        'completed'
    ]
]);

// Check JSON count
$response->assertJsonCount(10, 'data');
```

### Redirects

```php
$response->assertRedirect('/dashboard');
$response->assertRedirectToRoute('home');
```

### Session

```php
$response->assertSessionHas('message', 'Success!');
$response->assertSessionHasErrors(['email']);
```

### Cookies

```php
$response->assertCookie('name', 'value');
```

---

## Testing Authentication

### Registration

Create `tests/Feature/Auth/RegistrationTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function registration_requires_valid_email()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function registration_requires_password_confirmation()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** @test */
    public function email_must_be_unique()
    {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
```

### Login

Create `tests/Feature/Auth/LoginTest.php`:

```php
<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function users_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'password123')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function users_cannot_login_with_incorrect_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
```

---

## Testing API Endpoints

Test your API endpoints thoroughly:

### Create Task API

Create `tests/Feature/Api/TaskTest.php`:

```php
<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guests_cannot_access_tasks()
    {
        $response = $this->getJson('/api/tasks');

        $response->assertUnauthorized();
    }

    /** @test */
    public function authenticated_users_can_list_their_tasks()
    {
        $user = User::factory()->create();
        $tasks = Task::factory()->count(3)->create(['user_id' => $user->id]);

        // Create tasks for another user (should not appear)
        Task::factory()->count(2)->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/tasks');

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'completed', 'created_at']
            ]
        ]);
    }

    /** @test */
    public function authenticated_users_can_create_task()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'title' => 'My New Task',
                'description' => 'Task description'
            ]);

        $response->assertCreated();
        $response->assertJson([
            'data' => [
                'title' => 'My New Task',
                'description' => 'Task description',
                'completed' => false
            ]
        ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'My New Task',
            'user_id' => $user->id
        ]);
    }

    /** @test */
    public function creating_task_requires_title()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/tasks', [
                'description' => 'Task without title'
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function users_can_view_their_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => $task->title
            ]
        ]);
    }

    /** @test */
    public function users_cannot_view_other_users_tasks()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/tasks/{$task->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function users_can_update_their_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Title',
                'completed' => true
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'completed' => true
        ]);
    }

    /** @test */
    public function users_cannot_update_other_users_tasks()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Hacked Title'
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('tasks', [
            'title' => 'Hacked Title'
        ]);
    }

    /** @test */
    public function users_can_delete_their_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function users_cannot_delete_other_users_tasks()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertForbidden();
        $this->assertModelExists($task);
    }
}
```

Run tests:

```bash
php artisan test --filter=TaskTest
```

---

## Testing Complete Workflows

Test entire user journeys:

```php
/** @test */
public function user_can_complete_full_task_workflow()
{
    // 1. Register
    $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertAuthenticated();
    $user = User::where('email', 'john@example.com')->first();

    // 2. Create task via API
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [
            'title' => 'My First Task'
        ]);

    $taskId = $response->json('data.id');

    // 3. Mark task as completed
    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/tasks/{$taskId}", [
            'completed' => true
        ])
        ->assertOk();

    // 4. Verify task is completed
    $this->actingAs($user, 'sanctum')
        ->getJson("/api/tasks/{$taskId}")
        ->assertJson([
            'data' => [
                'completed' => true
            ]
        ]);

    // 5. Delete task
    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/tasks/{$taskId}")
        ->assertOk();

    $this->assertDatabaseMissing('tasks', ['id' => $taskId]);

    // 6. Logout
    $this->actingAs($user)->post('/logout');
    $this->assertGuest();
}
```

---

## Testing Validation

Test that validation rules work correctly:

```php
/** @test */
public function it_validates_task_creation()
{
    $user = User::factory()->create();

    // Missing title
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);

    // Title too long
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [
            'title' => str_repeat('a', 300) // Max is 255
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);

    // Valid data
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [
            'title' => 'Valid Task'
        ])
        ->assertCreated();
}
```

---

## Testing Authorization

Test that users can only access what they're allowed to:

```php
/** @test */
public function admin_can_delete_any_post()
{
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);
    $post = Post::factory()->create(['user_id' => $user->id]);

    $this->actingAs($admin)
        ->delete("/posts/{$post->id}")
        ->assertRedirect();

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
}

/** @test */
public function regular_users_cannot_delete_others_posts()
{
    $user = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $post = Post::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($user)
        ->delete("/posts/{$post->id}")
        ->assertForbidden();

    $this->assertModelExists($post);
}
```

---

## Testing Pagination

```php
/** @test */
public function it_paginates_tasks()
{
    $user = User::factory()->create();
    Task::factory()->count(30)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/tasks?page=1');

    $response->assertOk();
    $response->assertJsonStructure([
        'data',
        'links' => ['first', 'last', 'prev', 'next'],
        'meta' => ['current_page', 'per_page', 'total']
    ]);
    $response->assertJsonCount(15, 'data'); // Default per_page
}

/** @test */
public function it_returns_correct_page()
{
    $user = User::factory()->create();
    Task::factory()->count(30)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/tasks?page=2');

    $response->assertOk();
    $response->assertJsonPath('meta.current_page', 2);
}
```

---

## Testing File Uploads

```php
/** @test */
public function users_can_upload_avatar()
{
    Storage::fake('public');

    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)
        ->post('/profile/avatar', [
            'avatar' => $file
        ]);

    $response->assertRedirect();
    Storage::disk('public')->assertExists('avatars/' . $file->hashName());
}

/** @test */
public function avatar_must_be_an_image()
{
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf');

    $response = $this->actingAs($user)
        ->post('/profile/avatar', [
            'avatar' => $file
        ]);

    $response->assertSessionHasErrors(['avatar']);
}
```

---

## Testing Email Sending

```php
/** @test */
public function welcome_email_is_sent_on_registration()
{
    Mail::fake();

    $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    Mail::assertSent(WelcomeEmail::class, function ($mail) {
        return $mail->hasTo('john@example.com');
    });
}

/** @test */
public function no_emails_sent_on_failed_registration()
{
    Mail::fake();

    $this->post('/register', [
        'email' => 'invalid-email',
    ]);

    Mail::assertNothingSent();
}
```

---

## Testing Events

```php
/** @test */
public function task_created_event_is_fired()
{
    Event::fake([TaskCreated::class]);

    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [
            'title' => 'New Task'
        ]);

    Event::assertDispatched(TaskCreated::class, function ($event) {
        return $event->task->title === 'New Task';
    });
}
```

---

## Testing Queued Jobs

```php
/** @test */
public function task_notification_job_is_queued()
{
    Queue::fake();

    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/tasks', [
            'title' => 'New Task'
        ]);

    Queue::assertPushed(SendTaskNotification::class);
}
```

---

## Best Practices

### 1. Test the Happy Path and Edge Cases

```php
/** @test */
public function users_can_create_task() // Happy path
{
    // Valid data, should succeed
}

/** @test */
public function creating_task_requires_title() // Edge case
{
    // Missing data, should fail
}

/** @test */
public function users_cannot_create_task_with_too_long_title() // Edge case
{
    // Invalid data, should fail
}
```

### 2. Use Descriptive Test Names

```php
// ✅ GOOD
test_authenticated_users_can_create_tasks
test_guests_cannot_access_tasks_api
test_users_cannot_update_other_users_tasks

// ❌ BAD
test_create
test_access
test_update
```

### 3. Test One Scenario Per Test

```php
// ❌ BAD - Tests multiple scenarios
/** @test */
public function task_crud_operations()
{
    $user = User::factory()->create();

    // Create
    $response = $this->actingAs($user)->post('/tasks', [...]);
    // Update
    $response = $this->actingAs($user)->put('/tasks/1', [...]);
    // Delete
    $response = $this->actingAs($user)->delete('/tasks/1');
}

// ✅ GOOD - Separate tests
/** @test */
public function users_can_create_tasks() { ... }

/** @test */
public function users_can_update_tasks() { ... }

/** @test */
public function users_can_delete_tasks() { ... }
```

### 4. Use Factories for Test Data

```php
// ❌ TEDIOUS
$user = User::create([
    'name' => 'John',
    'email' => 'john@test.com',
    'password' => bcrypt('password'),
]);

// ✅ SIMPLE
$user = User::factory()->create();
```

### 5. Clean Database Between Tests

```php
use RefreshDatabase; // Always use this trait
```

---

## Quick Quiz

1. **What's the difference between unit tests and feature tests?**

2. **How do you make an authenticated request in tests?**

3. **What assertion checks if a database record exists?**

4. **How do you test file uploads?**

5. **What's the difference between `$this->post()` and `$this->postJson()`?**

---

## Practice Exercise

**Build comprehensive feature tests for a Blog API**:

1. Models: Post, Comment, User

2. API Endpoints:
   - GET /api/posts (list, paginated)
   - POST /api/posts (create)
   - GET /api/posts/{id} (show)
   - PUT /api/posts/{id} (update)
   - DELETE /api/posts/{id} (delete)
   - POST /api/posts/{id}/comments (add comment)

3. Write feature tests for:
   - Authentication (guests vs authenticated)
   - CRUD operations
   - Authorization (own posts only)
   - Validation (required fields, max length, etc.)
   - Pagination
   - Relationships (post with comments)
   - Edge cases (empty data, invalid IDs, etc.)

**Requirements**:
- At least 20 feature tests
- Test happy paths and edge cases
- Test authentication and authorization
- Test validation
- Use factories
- Descriptive test names

---

## What's Next?

In the next lesson, you'll learn about **Database Testing** - techniques for testing with databases, using transactions, seeding test data, and testing database-specific features like migrations and relationships.

Feature tests give you confidence that your application works as a whole. They're essential for maintaining quality as your app grows!
