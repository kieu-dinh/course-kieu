# Lesson 1: Laravel API Routes

**Duration**: 3-4 hours
**Prerequisites**: Module 17 - Livewire
**Objective**: Learn how to create RESTful API routes in Laravel and understand API fundamentals

---

## Introduction

Welcome to the world of APIs! You've already built APIs in pure PHP (Module 09), but now you'll see how Laravel makes API development faster, cleaner, and more powerful.

An API (Application Programming Interface) allows different applications to communicate with each other. Think of it as a waiter in a restaurant: you tell the waiter what you want (request), the waiter takes your order to the kitchen (server), and brings back your food (response).

In this lesson, you'll learn how Laravel's routing system is specifically designed to make building APIs simple and elegant.

---

## Why Laravel for APIs?

Remember when you built APIs in pure PHP? You had to:
- Manually parse JSON requests
- Set correct headers yourself
- Handle HTTP methods with `$_SERVER['REQUEST_METHOD']`
- Write lots of repetitive code

Laravel provides:
- **Automatic JSON handling** - Laravel automatically converts arrays to JSON
- **Route groups** - Group related API routes together
- **Middleware** - Add authentication, rate limiting, etc. with one line
- **Resource controllers** - Generate CRUD endpoints automatically
- **Validation** - Built-in, powerful request validation
- **API Resources** - Transform your data consistently (we'll cover this in Lesson 2)

---

## RESTful API Basics

Before diving into code, let's review REST principles. REST (Representational State Transfer) is a style of API design that uses:

### HTTP Methods (Verbs)

| Method | Purpose | Example |
|--------|---------|---------|
| GET | Retrieve data | Get all posts, get one post |
| POST | Create new resource | Create a new post |
| PUT/PATCH | Update existing resource | Update a post |
| DELETE | Delete resource | Delete a post |

### HTTP Status Codes

| Code | Meaning | When to Use |
|------|---------|-------------|
| 200 | OK | Successful GET, PUT, or PATCH |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid data sent |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Authenticated but not allowed |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable Entity | Validation failed |
| 500 | Server Error | Something went wrong |

### Resource Naming Conventions

```
GET    /api/posts          - List all posts
GET    /api/posts/{id}     - Get one post
POST   /api/posts          - Create a post
PUT    /api/posts/{id}     - Update a post (full replacement)
PATCH  /api/posts/{id}     - Update a post (partial update)
DELETE /api/posts/{id}     - Delete a post
```

**Key principles**:
- Use plural nouns (`posts`, not `post`)
- Use nouns, not verbs (`/posts`, not `/get-posts`)
- Use HTTP methods to indicate action
- Keep URLs hierarchical for relationships: `/api/posts/5/comments`

---

## Laravel's API Routes File

Laravel separates web routes from API routes:

```
routes/
  ├── web.php     - For browser-based routes (returns HTML)
  └── api.php     - For API routes (returns JSON)
```

**Key differences**:

| Feature | web.php | api.php |
|---------|---------|---------|
| URL prefix | / | /api |
| Middleware | web (sessions, CSRF) | api (stateless) |
| Response | HTML views | JSON |
| Authentication | Session-based | Token-based |

Routes in `api.php` are automatically prefixed with `/api`. So if you define:

```php
Route::get('/posts', [PostController::class, 'index']);
```

The actual URL will be: `http://your-app.com/api/posts`

---

## Creating Your First API Route

### Step 1: Basic Route

Open `routes/api.php`:

```php
<?php

use Illuminate\Support\Facades\Route;

// Simple API route
Route::get('/hello', function () {
    return ['message' => 'Hello from API!'];
});
```

**What's happening?**
1. Laravel automatically converts the array to JSON
2. Sets `Content-Type: application/json` header
3. Returns proper HTTP status code (200)

Test it:
```bash
curl http://localhost/api/hello
```

Response:
```json
{
    "message": "Hello from API!"
}
```

### Step 2: Route with Parameters

```php
Route::get('/users/{id}', function ($id) {
    return [
        'id' => $id,
        'name' => 'User ' . $id,
        'email' => 'user' . $id . '@example.com'
    ];
});
```

Test:
```bash
curl http://localhost/api/users/5
```

### Step 3: Using HTTP Methods

```php
// GET - Retrieve
Route::get('/posts', function () {
    return ['posts' => []];
});

// POST - Create
Route::post('/posts', function () {
    return ['message' => 'Post created'];
});

// PUT - Full update
Route::put('/posts/{id}', function ($id) {
    return ['message' => "Post $id updated"];
});

// PATCH - Partial update
Route::patch('/posts/{id}', function ($id) {
    return ['message' => "Post $id partially updated"];
});

// DELETE - Remove
Route::delete('/posts/{id}', function ($id) {
    return ['message' => "Post $id deleted"];
});
```

---

## API Controllers

Just like web routes, you should use controllers for API logic. But API controllers have some differences.

### Creating an API Controller

Laravel can generate API controllers automatically:

```bash
php artisan make:controller Api/PostController --api
```

The `--api` flag creates a controller without the `create` and `edit` methods (because APIs don't return HTML forms).

Generated controller:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // GET /api/posts
    public function index()
    {
        //
    }

    // POST /api/posts
    public function store(Request $request)
    {
        //
    }

    // GET /api/posts/{id}
    public function show(string $id)
    {
        //
    }

    // PUT/PATCH /api/posts/{id}
    public function update(Request $request, string $id)
    {
        //
    }

    // DELETE /api/posts/{id}
    public function destroy(string $id)
    {
        //
    }
}
```

**Notice**: No `create()` or `edit()` methods! APIs don't need to display forms.

---

## API Resource Routes

Instead of defining each route individually, use `apiResource`:

```php
use App\Http\Controllers\Api\PostController;

Route::apiResource('posts', PostController::class);
```

This single line creates these routes:

```
GET    /api/posts          → index()
POST   /api/posts          → store()
GET    /api/posts/{id}     → show()
PUT    /api/posts/{id}     → update()
PATCH  /api/posts/{id}     → update()
DELETE /api/posts/{id}     → destroy()
```

View all routes:
```bash
php artisan route:list
```

Filter to see only API routes:
```bash
php artisan route:list --path=api
```

---

## Building a Complete API Example

Let's build a simple Task API:

### Step 1: Create Model and Migration

```bash
php artisan make:model Task -m
```

Edit migration:

```php
public function up(): void
{
    Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('completed')->default(false);
        $table->timestamps();
    });
}
```

Run migration:
```bash
php artisan migrate
```

### Step 2: Create API Controller

```bash
php artisan make:controller Api/TaskController --api
```

Edit `app/Http/Controllers/Api/TaskController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * GET /api/tasks
     * List all tasks
     */
    public function index()
    {
        $tasks = Task::all();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * POST /api/tasks
     * Create a new task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean'
        ]);

        $task = Task::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => $task
        ], 201); // 201 = Created
    }

    /**
     * GET /api/tasks/{id}
     * Show a single task
     */
    public function show(Task $task)
    {
        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * PUT/PATCH /api/tasks/{id}
     * Update a task
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean'
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => $task
        ]);
    }

    /**
     * DELETE /api/tasks/{id}
     * Delete a task
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ], 200);

        // Or return 204 (No Content):
        // return response()->noContent();
    }
}
```

**Key points**:
- We use `response()->json()` to explicitly return JSON
- We can specify status codes as the second parameter
- Route model binding works automatically (`Task $task`)
- Validation happens before processing

### Step 3: Register Routes

In `routes/api.php`:

```php
use App\Http\Controllers\Api\TaskController;

Route::apiResource('tasks', TaskController::class);
```

### Step 4: Make Task Model Fillable

In `app/Models/Task.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'completed'
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];
}
```

---

## Testing Your API

### Using cURL

```bash
# List all tasks
curl http://localhost/api/tasks

# Create a task
curl -X POST http://localhost/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Buy groceries","description":"Milk, eggs, bread"}'

# Get one task
curl http://localhost/api/tasks/1

# Update a task
curl -X PUT http://localhost/api/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"title":"Buy groceries","completed":true}'

# Delete a task
curl -X DELETE http://localhost/api/tasks/1
```

### Using Postman or Insomnia

These are GUI tools that make API testing easier:
- Download Postman (free): https://www.postman.com/
- Or Insomnia: https://insomnia.rest/

Steps:
1. Create a new request
2. Select method (GET, POST, etc.)
3. Enter URL: `http://localhost/api/tasks`
4. For POST/PUT, select Body → raw → JSON
5. Enter JSON data
6. Click Send

---

## Route Grouping

Group related routes for better organization:

```php
// Group API v1 routes
Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('users', UserController::class);
});

// Now routes are: /api/v1/tasks, /api/v1/posts, etc.
```

Add middleware to all routes in a group:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('posts', PostController::class);
});
```

Combine prefix and middleware:

```php
Route::prefix('v1')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('posts', PostController::class);
    });
```

---

## Custom API Endpoints

Not everything fits the CRUD pattern. Add custom endpoints:

```php
Route::apiResource('tasks', TaskController::class);

// Custom endpoints
Route::patch('tasks/{task}/complete', [TaskController::class, 'markComplete']);
Route::get('tasks/completed', [TaskController::class, 'completed']);
Route::get('tasks/pending', [TaskController::class, 'pending']);
```

**Important**: Put specific routes BEFORE resource routes:

```php
// ✅ CORRECT - Specific routes first
Route::get('tasks/completed', [TaskController::class, 'completed']);
Route::apiResource('tasks', TaskController::class);

// ❌ WRONG - Laravel will think "completed" is an ID
Route::apiResource('tasks', TaskController::class);
Route::get('tasks/completed', [TaskController::class, 'completed']);
```

In controller:

```php
public function completed()
{
    $tasks = Task::where('completed', true)->get();

    return response()->json([
        'success' => true,
        'data' => $tasks
    ]);
}

public function markComplete(Task $task)
{
    $task->update(['completed' => true]);

    return response()->json([
        'success' => true,
        'message' => 'Task marked as complete',
        'data' => $task
    ]);
}
```

---

## Error Handling

Always handle errors gracefully in APIs:

```php
public function show(Task $task)
{
    try {
        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Task not found'
        ], 404);
    }
}
```

For validation errors, Laravel automatically returns 422:

```php
public function store(Request $request)
{
    // If validation fails, Laravel automatically returns:
    // Status: 422
    // Body: { "message": "...", "errors": { "title": ["..."] } }
    $validated = $request->validate([
        'title' => 'required|string|max:255',
    ]);

    // ...
}
```

---

## Response Helpers

Laravel provides helpful response methods:

```php
// Basic JSON
return response()->json(['data' => $data]);

// With status code
return response()->json(['data' => $data], 201);

// No content (204)
return response()->noContent();

// Created (201) with location header
return response()->json($task, 201)
    ->header('Location', route('tasks.show', $task));
```

---

## Rate Limiting (Preview)

Laravel includes basic rate limiting for APIs:

In `routes/api.php`:

```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

This limits to 60 requests per minute. We'll cover rate limiting in detail in Lesson 5.

---

## API Versioning (Preview)

Prepare for future changes by versioning your API:

```
routes/api.php
app/Http/Controllers/
  └── Api/
      ├── V1/
      │   ├── TaskController.php
      │   └── PostController.php
      └── V2/
          ├── TaskController.php
          └── PostController.php
```

In `routes/api.php`:

```php
// Version 1
Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', \App\Http\Controllers\Api\V1\TaskController::class);
});

// Version 2 (when you need breaking changes)
Route::prefix('v2')->group(function () {
    Route::apiResource('tasks', \App\Http\Controllers\Api\V2\TaskController::class);
});
```

Now clients can use:
- `/api/v1/tasks` - Old version (maintain for backward compatibility)
- `/api/v2/tasks` - New version (with improvements)

We'll explore versioning in Lesson 4.

---

## Best Practices

1. **Use Resource Routes**: Don't manually define routes when `apiResource` works
2. **Consistent Response Format**: Always use the same structure
3. **Proper Status Codes**: 200 for success, 201 for created, 404 for not found, etc.
4. **Validate Input**: Always validate before processing
5. **Use Route Model Binding**: Let Laravel find models automatically
6. **Version Your API**: Plan for future changes
7. **Document Your API**: Use comments or tools like Swagger (Lesson 6)
8. **Return JSON, Always**: Never return HTML from API routes

---

## Common Response Format

Create a consistent format for all API responses:

```php
// Success response
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Operation successful' // optional
]);

// Error response
return response()->json([
    'success' => false,
    'message' => 'Error message',
    'errors' => [] // validation errors if applicable
], 400);
```

This makes it easy for frontend developers to handle responses.

---

## Quick Quiz

Test your understanding:

1. **What's the difference between `web.php` and `api.php` routes?**
   - Think about: middleware, response types, URL prefixes

2. **When should you use POST vs PUT vs PATCH?**
   - Consider: creating vs updating, full vs partial updates

3. **What does `Route::apiResource()` create?**
   - How many routes? What HTTP methods?

4. **Why put specific routes before resource routes?**
   - What happens if you don't?

5. **What status code should you return when creating a resource?**
   - Hint: It's not 200!

---

## Practice Exercise

Before moving to the next lesson, try this:

**Build a "Notes" API**:
- Model: `Note` with `title`, `content`, `color`
- Controller: `Api/NoteController`
- Routes: Full CRUD (create, read, update, delete)
- Custom endpoint: GET `/api/notes/by-color/{color}`
- Test all endpoints with cURL or Postman

**Requirements**:
- Use `apiResource` for main CRUD
- Add validation
- Return consistent JSON format
- Use proper status codes
- Add route model binding

---

## What's Next?

In the next lesson, you'll learn about **API Resources and Collections** - Laravel's way to transform your data into consistent, clean JSON responses. Instead of returning raw model data, you'll control exactly what fields to include, format dates, include relationships, and more.

You're building the foundation for professional APIs! Keep practicing!
