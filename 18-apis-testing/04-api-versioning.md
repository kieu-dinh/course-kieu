# Lesson 4: API Versioning

**Duration**: 2-3 hours
**Prerequisites**: Lessons 1-3 (API Routes, Resources, Authentication)
**Objective**: Learn how to version your APIs to manage changes without breaking existing clients

---

## Introduction

Imagine this scenario: You built an API and it's being used by mobile apps, web apps, and third-party integrations. Now you need to change how the API works. But if you change it, **all existing apps break**.

That's where **API versioning** comes in. It allows you to:
- Make breaking changes without affecting existing clients
- Support multiple API versions simultaneously
- Gradually migrate clients to newer versions
- Maintain backward compatibility

Think of API versioning like software versions: WordPress 5 and WordPress 6 can coexist. Users upgrade when they're ready, not when you force them.

---

## What is a Breaking Change?

Not all changes require a new API version. Understanding what breaks compatibility is crucial.

### Breaking Changes (Require New Version)

These changes will break existing client code:

1. **Removing fields**:
   ```php
   // v1: Returns name
   ['name' => 'John Doe']

   // v2: Removes name (BREAKING!)
   ['full_name' => 'John Doe']
   ```

2. **Renaming fields**:
   ```php
   // v1
   ['user_id' => 1]

   // v2: Renames field (BREAKING!)
   ['author_id' => 1]
   ```

3. **Changing field types**:
   ```php
   // v1: Boolean
   ['completed' => true]

   // v2: String (BREAKING!)
   ['completed' => 'yes']
   ```

4. **Removing endpoints**:
   ```
   v1: GET /api/tasks ✅
   v2: GET /api/tasks ❌ (removed)
   ```

5. **Changing authentication method**:
   ```
   v1: Token authentication
   v2: OAuth only (BREAKING!)
   ```

6. **Changing required parameters**:
   ```php
   // v1: title optional
   ['title' => 'nullable']

   // v2: title required (BREAKING!)
   ['title' => 'required']
   ```

### Non-Breaking Changes (No New Version Needed)

These are safe to make:

1. **Adding new optional fields**:
   ```php
   // v1
   ['id' => 1, 'title' => 'Task']

   // v1 (updated): Added field, clients ignore it
   ['id' => 1, 'title' => 'Task', 'color' => 'blue']
   ```

2. **Adding new endpoints**:
   ```
   v1: GET /api/tasks
   v1 (updated): Added GET /api/tasks/archived
   ```

3. **Adding new optional parameters**:
   ```php
   // Before: create(title, description)
   // After: create(title, description, priority?) // Priority is optional
   ```

4. **Changing error messages** (text only, not structure)

5. **Performance improvements** (same behavior, faster)

**Rule of thumb**: If existing client code works without modification, it's not breaking.

---

## Versioning Strategies

There are several ways to version APIs:

### 1. URL Path Versioning (Most Common)

```
/api/v1/tasks
/api/v2/tasks
/api/v3/tasks
```

**Pros**:
- Clear and visible
- Easy to implement
- Simple to route
- Most common approach

**Cons**:
- URLs change
- More routes to maintain

**Best for**: Most applications, especially public APIs

### 2. Header Versioning

```
GET /api/tasks
Accept: application/vnd.yourapp.v1+json

GET /api/tasks
Accept: application/vnd.yourapp.v2+json
```

**Pros**:
- URLs stay the same
- Professional/RESTful

**Cons**:
- Less visible
- Harder to test (need to set headers)
- More complex routing

**Best for**: Advanced APIs, when URLs must remain stable

### 3. Query Parameter Versioning

```
/api/tasks?version=1
/api/tasks?version=2
```

**Pros**:
- Simple to implement
- Easy to test

**Cons**:
- Not RESTful
- Can be forgotten
- URLs become messy

**Best for**: Internal APIs, quick solutions

### 4. Subdomain Versioning

```
v1.api.yourapp.com/tasks
v2.api.yourapp.com/tasks
```

**Pros**:
- Complete separation
- Can use different servers

**Cons**:
- Complex infrastructure
- DNS management required

**Best for**: Very large APIs with dedicated infrastructure

**For this course**: We'll use **URL path versioning** (most common and practical).

---

## Implementing URL Path Versioning

### Step 1: Organize Controllers

Create separate controllers for each version:

```
app/Http/Controllers/Api/
├── V1/
│   ├── TaskController.php
│   ├── PostController.php
│   └── UserController.php
└── V2/
    ├── TaskController.php
    ├── PostController.php
    └── UserController.php
```

Generate v1 controllers:
```bash
php artisan make:controller Api/V1/TaskController --api
```

### Step 2: Organize Resources

Create separate resources for each version:

```
app/Http/Resources/
├── V1/
│   ├── TaskResource.php
│   ├── TaskCollection.php
│   └── UserResource.php
└── V2/
    ├── TaskResource.php
    ├── TaskCollection.php
    └── UserResource.php
```

### Step 3: Create V1 API

**V1 TaskResource** (`app/Http/Resources/V1/TaskResource.php`):

```php
<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => (bool) $this->completed,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
```

**V1 TaskController** (`app/Http/Controllers/Api/V1/TaskController.php`):

```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->get();
        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $task = $request->user()->tasks()->create($validated);
        return new TaskResource($task);
    }

    // ... other methods
}
```

**V1 Routes** (`routes/api.php`):

```php
use App\Http\Controllers\Api\V1\TaskController as V1TaskController;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', V1TaskController::class);
});
```

**Test V1**:
```
GET /api/v1/tasks
```

### Step 4: Create V2 with Changes

Now let's say we want to make breaking changes in v2:
- Rename `description` to `details`
- Add `priority` field
- Change date format to ISO8601

**V2 TaskResource** (`app/Http/Resources/V2/TaskResource.php`):

```php
<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'details' => $this->description, // ⚠️ Renamed from 'description'
            'priority' => $this->priority ?? 'normal', // ⚠️ New field
            'completed' => (bool) $this->completed,
            'created_at' => $this->created_at->toIso8601String(), // ⚠️ Different format
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
```

**V2 TaskController** (`app/Http/Controllers/Api/V2/TaskController.php`):

```php
<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $tasks = $request->user()->tasks()->get();
        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'nullable|string', // ⚠️ Changed from 'description'
            'priority' => 'nullable|in:low,normal,high', // ⚠️ New field
        ]);

        // Map 'details' back to 'description' for database
        $task = $request->user()->tasks()->create([
            'title' => $validated['title'],
            'description' => $validated['details'] ?? null,
            'priority' => $validated['priority'] ?? 'normal',
        ]);

        return new TaskResource($task);
    }

    // ... other methods
}
```

**V2 Routes**:

```php
use App\Http\Controllers\Api\V1\TaskController as V1TaskController;
use App\Http\Controllers\Api\V2\TaskController as V2TaskController;

// Version 1 (legacy, maintained for backward compatibility)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', V1TaskController::class);
});

// Version 2 (current, recommended)
Route::prefix('v2')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', V2TaskController::class);
});
```

Now both versions work simultaneously:

```bash
# V1 - Old clients still work
GET /api/v1/tasks
Response: {"id": 1, "description": "...", "created_at": "2024-01-15 10:00:00"}

# V2 - New clients use new format
GET /api/v2/tasks
Response: {"id": 1, "details": "...", "priority": "normal", "created_at": "2024-01-15T10:00:00Z"}
```

---

## Handling Database Changes

Sometimes you need to add fields to support v2. Use migrations:

```bash
php artisan make:migration add_priority_to_tasks_table
```

```php
public function up()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->string('priority')->default('normal')->after('description');
    });
}
```

```bash
php artisan migrate
```

Update model:

```php
class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority', // New field
        'completed',
        'user_id',
    ];
}
```

**Important**: V1 doesn't break because:
- New field has default value
- V1 resource doesn't include `priority`
- Old clients never see the new field

---

## Deprecation Strategy

When releasing v2, don't immediately remove v1. Follow this timeline:

### Phase 1: Release v2 (Month 0)

- ✅ v2 released
- ✅ v1 still fully supported
- 📢 Announce v2 in documentation
- 📢 Email users about v2 benefits

### Phase 2: Deprecation Notice (Month 3)

- ⚠️ Add deprecation headers to v1 responses
- 📢 Announce v1 deprecation timeline
- 📧 Email users: "v1 will be removed in 6 months"

```php
// V1 Controller - Add deprecation header
public function index(Request $request)
{
    return TaskResource::collection($tasks)
        ->response()
        ->header('X-API-Deprecation', 'true')
        ->header('X-API-Sunset', '2024-12-31');
}
```

### Phase 3: Warning Period (Month 6)

- ⚠️ Return warnings in v1 responses
- 📢 "v1 will be removed in 3 months"
- 📊 Track v1 usage, contact heavy users

```php
public function index(Request $request)
{
    return response()->json([
        'warning' => 'API v1 is deprecated. Please upgrade to v2. v1 will be removed on 2024-12-31.',
        'data' => TaskResource::collection($tasks),
    ])->header('X-API-Deprecation', 'true');
}
```

### Phase 4: Removal (Month 9-12)

- ❌ Remove v1 routes
- ✅ Only v2 remains
- 📝 Document migration path

**Golden rule**: Give users at least 6-12 months to migrate.

---

## Version Negotiation

Allow clients to request a specific version, with fallback:

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Default to latest version
    Route::apiResource('tasks', V2TaskController::class);

    // Explicit versions
    Route::prefix('v1')->group(function () {
        Route::apiResource('tasks', V1TaskController::class);
    });

    Route::prefix('v2')->group(function () {
        Route::apiResource('tasks', V2TaskController::class);
    });
});
```

Clients can:
- Use `/api/tasks` → Gets v2 (latest)
- Use `/api/v1/tasks` → Gets v1 explicitly
- Use `/api/v2/tasks` → Gets v2 explicitly

---

## Documentation is Critical

When versioning, documentation becomes essential. Document each version separately.

### Version Documentation Structure

```
docs/
├── api/
│   ├── v1/
│   │   ├── authentication.md
│   │   ├── tasks.md
│   │   └── users.md
│   └── v2/
│       ├── authentication.md
│       ├── tasks.md
│       ├── users.md
│       └── migration-from-v1.md
```

### Migration Guide Example

```markdown
# Migrating from API v1 to v2

## Breaking Changes

### Tasks Endpoint

#### Field Renames
- `description` → `details`

**v1**:
```json
{
  "description": "Task details here"
}
```

**v2**:
```json
{
  "details": "Task details here"
}
```

#### New Fields
- Added `priority` field (values: low, normal, high)

#### Date Format Changes
- Dates now use ISO8601 format instead of MySQL datetime

**v1**: `"created_at": "2024-01-15 10:00:00"`
**v2**: `"created_at": "2024-01-15T10:00:00Z"`

## Update Your Code

### JavaScript Example

**v1**:
```javascript
const task = await api.post('/v1/tasks', {
  title: 'My task',
  description: 'Task details'
});
console.log(task.description);
```

**v2**:
```javascript
const task = await api.post('/v2/tasks', {
  title: 'My task',
  details: 'Task details', // ⚠️ renamed
  priority: 'high' // ⚠️ new field
});
console.log(task.details); // ⚠️ renamed
```
```

---

## Best Practices

### 1. Version from Day One

Even if you don't plan changes, start with v1:

```php
// ✅ GOOD - Versioned from start
Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});

// ❌ BAD - No version, hard to add later
Route::apiResource('tasks', TaskController::class);
```

### 2. Use Semantic Versioning (Major.Minor)

- **Major version** (v1, v2, v3): Breaking changes
- **Minor version** (v1.1, v1.2): New features, no breaking changes

Most APIs only version major changes (v1, v2).

### 3. Keep Versions Independent

Don't share code between versions:

```php
// ❌ BAD - Shared code between versions
class TaskController extends Controller
{
    public function index(Request $request, $version)
    {
        if ($version === 'v1') {
            // v1 logic
        } else {
            // v2 logic
        }
    }
}

// ✅ GOOD - Separate controllers
// V1TaskController
// V2TaskController
```

### 4. Version Resources, Not Just Routes

```
// ✅ GOOD
app/Http/Resources/V1/TaskResource.php
app/Http/Resources/V2/TaskResource.php

// ❌ BAD
app/Http/Resources/TaskResource.php (shared between versions)
```

### 5. Always Support at Least 2 Versions

When v3 launches, keep v2 (drop v1):
- ✅ v2 (stable, widely used)
- ✅ v3 (latest)
- ❌ v1 (deprecated, removed)

### 6. Log Version Usage

Track which versions are being used:

```php
public function index(Request $request)
{
    Log::info('API v1 accessed', [
        'user_id' => $request->user()->id,
        'endpoint' => 'tasks.index'
    ]);

    return TaskResource::collection($tasks);
}
```

This helps you decide when to remove old versions.

### 7. Include Version in Response

```php
return response()->json([
    'version' => 'v2',
    'data' => TaskResource::collection($tasks),
]);
```

### 8. Test All Versions

```php
// tests/Feature/Api/V1/TaskTest.php
// tests/Feature/Api/V2/TaskTest.php
```

Each version needs its own test suite.

---

## Real-World Example: Blog API Evolution

### V1: Initial Release

```php
// V1 PostResource
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'body' => $this->body,
        'author' => $this->user->name,
        'published' => (bool) $this->published,
        'created_at' => $this->created_at->toDateTimeString(),
    ];
}
```

### V2: Add Features (Non-Breaking)

Add optional features without breaking v1:

```php
// V2 PostResource
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'body' => $this->body,
        'author' => [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'avatar' => $this->user->avatar_url,
        ],
        'published' => (bool) $this->published,
        'tags' => TagResource::collection($this->whenLoaded('tags')), // New
        'comments_count' => $this->comments_count ?? 0, // New
        'created_at' => $this->created_at->toDateTimeString(),
        'updated_at' => $this->updated_at->toDateTimeString(), // New
    ];
}
```

### V3: Breaking Changes

Rename fields, change structure:

```php
// V3 PostResource
public function toArray($request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'content' => $this->body, // ⚠️ Renamed from 'body'
        'excerpt' => $this->excerpt, // ⚠️ New required field
        'author' => new UserResource($this->whenLoaded('user')),
        'status' => $this->published ? 'published' : 'draft', // ⚠️ Changed from boolean
        'tags' => TagResource::collection($this->whenLoaded('tags')),
        'metadata' => [ // ⚠️ New nested structure
            'comments_count' => $this->comments_count ?? 0,
            'views_count' => $this->views_count ?? 0,
            'likes_count' => $this->likes_count ?? 0,
        ],
        'timestamps' => [ // ⚠️ Nested timestamps
            'created' => $this->created_at->toIso8601String(),
            'updated' => $this->updated_at->toIso8601String(),
            'published' => $this->published_at?->toIso8601String(),
        ],
    ];
}
```

Now you support three versions:
- `/api/v1/posts` - Legacy, deprecated
- `/api/v2/posts` - Stable, recommended
- `/api/v3/posts` - Latest, new features

---

## Quick Quiz

1. **What is a breaking change? Give 3 examples.**

2. **Why is URL path versioning most common?**
   - What are its advantages?

3. **How long should you support old API versions?**
   - What's a reasonable deprecation timeline?

4. **Can you add new optional fields without a new version?**
   - Why or why not?

5. **Where should you document API versions?**

---

## Practice Exercise

**Build a versioned Products API**:

### V1 Requirements:
- Model: Product (name, price, description)
- Endpoints: CRUD
- Resource returns: `id, name, price, description, created_at`

### V2 Requirements (Breaking Changes):
- Add `category_id` field (required)
- Rename `price` to `amount`
- Change `description` to `details`
- Add currency field
- Resource returns: `id, name, amount, currency, details, category, created_at`

### Tasks:
1. Create V1 API (controller, resource, routes)
2. Test V1 works
3. Create V2 API with breaking changes
4. Test both V1 and V2 work simultaneously
5. Write migration guide (markdown file)
6. Add deprecation headers to V1

---

## What's Next?

In the next lesson, you'll learn about **Rate Limiting** - how to protect your API from abuse by limiting the number of requests users can make. This is essential for production APIs!

Versioning is crucial for maintaining APIs in production. Plan for change from day one, and your future self will thank you!
