# Lesson 2: API Resources and Collections

**Duration**: 3-4 hours
**Prerequisites**: Lesson 1 - Laravel API Routes
**Objective**: Learn to transform model data into clean, consistent JSON responses using Laravel's API Resources

---

## Introduction

In the previous lesson, you returned data like this:

```php
public function index()
{
    $tasks = Task::all();
    return response()->json(['data' => $tasks]);
}
```

This works, but it has problems:
- **Exposes everything**: All model attributes, even sensitive ones
- **No control**: Can't format dates, rename fields, or add computed values
- **Inconsistent**: Different endpoints might return data differently
- **Messy relationships**: Including related models creates nested JSON chaos

Laravel's **API Resources** solve all of these problems. They act as a transformation layer between your Eloquent models and JSON responses.

Think of resources as a "presenter" or "view" for your API - just like Blade templates format HTML, resources format JSON.

---

## Why Use API Resources?

### Without Resources (Bad)

```php
// Returns everything from the database
$user = User::find(1);
return $user;

// Output:
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "password": "$2y$10$...",  // ❌ Exposed password hash!
    "remember_token": "...",    // ❌ Exposed token!
    "email_verified_at": "2024-01-15 10:30:00",
    "created_at": "2024-01-01 08:00:00",
    "updated_at": "2024-01-15 10:30:00"
}
```

### With Resources (Good)

```php
// Returns only what you specify
return new UserResource($user);

// Output:
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "joined": "January 1, 2024"  // ✅ Formatted date
    // No password, no tokens!
}
```

**Benefits**:
- **Security**: Hide sensitive fields
- **Consistency**: Same format everywhere
- **Flexibility**: Transform, format, add computed values
- **Documentation**: Resources serve as API documentation
- **Maintainability**: Change response format in one place

---

## Creating Your First Resource

### Step 1: Generate Resource

```bash
php artisan make:resource TaskResource
```

This creates: `app/Http/Resources/TaskResource.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
```

By default, it returns everything (like no resource). Let's customize it!

### Step 2: Customize the Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => (bool) $this->completed,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

**Key points**:
- `$this` refers to the Task model
- You control exactly what to include
- You can format, cast, or transform values
- Access model properties: `$this->property`
- Access model relationships: `$this->relationship`

### Step 3: Use in Controller

```php
use App\Http\Resources\TaskResource;

public function show(Task $task)
{
    return new TaskResource($task);
}
```

That's it! Laravel automatically:
- Converts to JSON
- Sets proper headers
- Returns 200 status code

---

## Resource Collections

What about returning multiple items? Use **Collections**.

### Method 1: Collection Method

```php
use App\Http\Resources\TaskResource;

public function index()
{
    $tasks = Task::all();
    return TaskResource::collection($tasks);
}
```

Output:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Task 1",
            "completed": false,
            ...
        },
        {
            "id": 2,
            "title": "Task 2",
            "completed": true,
            ...
        }
    ]
}
```

**Notice**: Automatically wraps in `data` key.

### Method 2: Custom Collection Class

For more control, create a collection class:

```bash
php artisan make:resource TaskCollection
```

This creates: `app/Http/Resources/TaskCollection.php`

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'completed' => $this->collection->where('completed', true)->count(),
                'pending' => $this->collection->where('completed', false)->count(),
            ]
        ];
    }
}
```

Use in controller:

```php
use App\Http\Resources\TaskCollection;

public function index()
{
    $tasks = Task::all();
    return new TaskCollection($tasks);
}
```

Output:
```json
{
    "data": [
        { "id": 1, "title": "Task 1", ... },
        { "id": 2, "title": "Task 2", ... }
    ],
    "meta": {
        "total": 10,
        "completed": 4,
        "pending": 6
    }
}
```

---

## Conditional Attributes

Show/hide fields based on conditions:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'description' => $this->description,
        'completed' => (bool) $this->completed,

        // Only show email to authenticated users
        'user_email' => $this->when($request->user(), function () {
            return $this->user->email;
        }),

        // Only show admin data to admins
        'admin_notes' => $this->when(
            $request->user()?->is_admin,
            $this->admin_notes
        ),

        // Show created_by only if it exists
        'created_by' => $this->whenNotNull($this->created_by),

        'created_at' => $this->created_at->format('Y-m-d H:i:s'),
    ];
}
```

**Methods**:
- `$this->when($condition, $value)` - Include if condition is true
- `$this->whenNotNull($value)` - Include if not null
- `$this->whenHas('field')` - Include if field exists
- `$this->mergeWhen($condition, $array)` - Merge entire array if condition is true

---

## Relationships in Resources

### Eager Loading (Important!)

Before including relationships, **always eager load** them to avoid N+1 queries:

```php
public function index()
{
    // ❌ BAD - N+1 problem
    $tasks = Task::all();

    // ✅ GOOD - Eager load relationships
    $tasks = Task::with('user', 'comments')->get();

    return TaskResource::collection($tasks);
}
```

### Including Relationships

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'completed' => (bool) $this->completed,

        // Include related user
        'user' => new UserResource($this->whenLoaded('user')),

        // Include related comments
        'comments' => CommentResource::collection($this->whenLoaded('comments')),

        'created_at' => $this->created_at->format('Y-m-d H:i:s'),
    ];
}
```

**Key method**: `$this->whenLoaded('relationship')`
- Only includes if relationship was eager loaded
- Prevents N+1 queries
- Returns null if not loaded

### Conditional Relationships

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,

        // Include user only if requested
        'user' => new UserResource($this->whenLoaded('user')),

        // Include comments count if loaded
        'comments_count' => $this->when(
            $this->relationLoaded('comments'),
            $this->comments->count()
        ),

        // Or use withCount in query:
        // $tasks = Task::withCount('comments')->get();
        'comments_count' => $this->when(
            isset($this->comments_count),
            $this->comments_count
        ),
    ];
}
```

---

## Nested Resources

Create a complete example with relationships:

### Models

```php
// Task belongs to User
class Task extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

### UserResource

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
```

### CommentResource

```php
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'author' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
```

### TaskResource with Relationships

```php
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => (bool) $this->completed,

            // Nested resources
            'owner' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            // Counts
            'comments_count' => $this->when(
                isset($this->comments_count),
                $this->comments_count
            ),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### Controller

```php
public function show(Task $task)
{
    // Load relationships
    $task->load('user', 'comments.user');

    return new TaskResource($task);
}

public function index()
{
    $tasks = Task::with('user')
        ->withCount('comments')
        ->get();

    return TaskResource::collection($tasks);
}
```

---

## Adding Computed Values

Add fields that don't exist in the database:

```php
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'completed' => (bool) $this->completed,

            // Computed values
            'status' => $this->completed ? 'Done' : 'Pending',
            'overdue' => $this->due_date && $this->due_date->isPast() && !$this->completed,
            'days_old' => $this->created_at->diffInDays(now()),
            'is_mine' => $request->user()?->id === $this->user_id,

            // Format dates
            'due_date_human' => $this->due_date?->format('M d, Y'),
            'created_ago' => $this->created_at->diffForHumans(),
        ];
    }
}
```

---

## Resource Responses with Additional Data

### Add Extra Data (Outside 'data' wrapper)

```php
public function index()
{
    $tasks = Task::all();

    return TaskResource::collection($tasks)
        ->additional([
            'meta' => [
                'version' => '1.0',
                'timestamp' => now()->toIso8601String(),
            ]
        ]);
}
```

Output:
```json
{
    "data": [...],
    "meta": {
        "version": "1.0",
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

### Customize Response

```php
public function show(Task $task)
{
    return (new TaskResource($task))
        ->response()
        ->header('X-Custom-Header', 'value')
        ->setStatusCode(200);
}
```

---

## Pagination with Resources

Resources work seamlessly with Laravel pagination:

```php
public function index()
{
    $tasks = Task::with('user')
        ->paginate(15);

    return TaskResource::collection($tasks);
}
```

Output:
```json
{
    "data": [...],
    "links": {
        "first": "http://localhost/api/tasks?page=1",
        "last": "http://localhost/api/tasks?page=3",
        "prev": null,
        "next": "http://localhost/api/tasks?page=2"
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "path": "http://localhost/api/tasks",
        "per_page": 15,
        "to": 15,
        "total": 45
    }
}
```

**Automatic**: Laravel handles pagination metadata for you!

---

## Customizing the Resource Wrapper

By default, resources wrap data in `"data"` key. You can change or remove it.

### Remove Wrapper (Not Recommended)

In `AppServiceProvider`:

```php
use Illuminate\Http\Resources\Json\JsonResource;

public function boot(): void
{
    JsonResource::withoutWrapping();
}
```

Now resources return array directly (no `data` wrapper).

### Customize Wrapper

In your resource:

```php
class TaskResource extends JsonResource
{
    public static $wrap = 'tasks'; // Instead of 'data'

    public function toArray(Request $request): array
    {
        return [...];
    }
}
```

Output:
```json
{
    "tasks": [...]
}
```

---

## Advanced: Conditional Includes

Allow clients to request specific relationships:

### Controller

```php
public function index(Request $request)
{
    $query = Task::query();

    // Allow ?include=user,comments
    if ($request->has('include')) {
        $includes = explode(',', $request->include);
        $query->with($includes);
    }

    $tasks = $query->paginate(15);

    return TaskResource::collection($tasks);
}
```

### Resource

```php
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'completed' => (bool) $this->completed,

            // Only include if loaded
            'user' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### Usage

```bash
# Without relationships
GET /api/tasks

# With user
GET /api/tasks?include=user

# With user and comments
GET /api/tasks?include=user,comments
```

---

## Real-World Example: Blog API

Let's build a complete blog API with resources:

### Models

```php
// Post.php
public function author()
{
    return $this->belongsTo(User::class, 'user_id');
}

public function comments()
{
    return $this->hasMany(Comment::class);
}

public function tags()
{
    return $this->belongsToMany(Tag::class);
}
```

### UserResource

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar_url,
        ];
    }
}
```

### TagResource

```php
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
```

### CommentResource

```php
class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'author' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
```

### PostResource

```php
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'status' => $this->status,

            // Computed
            'read_time' => $this->getReadTimeAttribute(),
            'is_published' => $this->status === 'published',

            // Relationships
            'author' => new UserResource($this->whenLoaded('author')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),

            // Counts
            'comments_count' => $this->when(
                isset($this->comments_count),
                $this->comments_count
            ),

            // Dates
            'published_at' => $this->published_at?->format('M d, Y'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
```

### PostCollection

```php
class PostCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'published' => $this->collection->where('status', 'published')->count(),
                'draft' => $this->collection->where('status', 'draft')->count(),
            ]
        ];
    }
}
```

### Controller

```php
class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('author', 'tags')
            ->withCount('comments')
            ->published()
            ->latest()
            ->paginate(15);

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        $post->load('author', 'tags', 'comments.user');

        return new PostResource($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'array',
        ]);

        $post = $request->user()->posts()->create($validated);
        $post->tags()->attach($request->tags);

        return new PostResource($post);
    }
}
```

---

## Best Practices

1. **Always Use Resources**: Don't return models directly
2. **Eager Load**: Use `with()` before returning collections
3. **Use `whenLoaded()`**: Prevent N+1 queries
4. **Consistent Naming**: Use clear, consistent field names
5. **Hide Sensitive Data**: Never expose passwords, tokens, etc.
6. **Format Dates**: Use consistent date formats
7. **Document Fields**: Add comments explaining computed fields
8. **Version Resources**: When API changes, create new resource versions

---

## Common Mistakes

### 1. Not Eager Loading

```php
// ❌ BAD - N+1 queries
public function index()
{
    $tasks = Task::all();
    return TaskResource::collection($tasks);
}

// In TaskResource:
'user' => new UserResource($this->user) // Causes N+1!

// ✅ GOOD
public function index()
{
    $tasks = Task::with('user')->get();
    return TaskResource::collection($tasks);
}

// In TaskResource:
'user' => new UserResource($this->whenLoaded('user'))
```

### 2. Exposing Sensitive Data

```php
// ❌ BAD
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'password' => $this->password, // Never!
        'api_token' => $this->api_token, // Never!
    ];
}
```

### 3. Returning Parent Array

```php
// ❌ BAD - Returns unwanted fields
public function toArray(Request $request): array
{
    return parent::toArray($request);
}

// ✅ GOOD - Be explicit
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        // Only what you want
    ];
}
```

---

## Quick Quiz

1. **Why use API Resources instead of returning models directly?**
   - Think about: security, consistency, flexibility

2. **What's the difference between a Resource and a ResourceCollection?**
   - When do you use each?

3. **What does `$this->whenLoaded('user')` do?**
   - Why is it important?

4. **How do you add computed fields to a resource?**
   - Give an example

5. **What's the N+1 problem and how do resources help prevent it?**

---

## Practice Exercise

**Build a "Products API" with Resources**:

Create:
1. Models: `Product`, `Category`, `Review`
2. Relationships:
   - Product belongs to Category
   - Product has many Reviews
3. Resources:
   - `ProductResource` with category and reviews
   - `CategoryResource`
   - `ReviewResource`
4. Controllers that use resources
5. Computed fields:
   - `average_rating` (from reviews)
   - `review_count`
   - `in_stock` (boolean)
   - `price_formatted` (with currency)

**Requirements**:
- Use `whenLoaded()` for relationships
- Add pagination to product list
- Include conditional fields for authenticated users
- Format dates properly
- Hide sensitive data (cost price, supplier info)

---

## What's Next?

In the next lesson, you'll learn about **Sanctum Authentication** - how to secure your APIs so only authenticated users can access them. You'll learn about token-based authentication, perfect for SPAs and mobile apps.

Resources are the foundation of professional APIs. Master them, and your APIs will be clean, consistent, and secure!
