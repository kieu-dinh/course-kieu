# Lesson 14 - API Versioning Strategies

**Duration**: 30-45 minutes

---

## Why Version Your API?

You need to make breaking changes to your API, but:
- Existing apps depend on current API
- Can't update all clients immediately
- Mobile apps can't be force-updated
- Third-party integrations need time to adapt

**Solution:** API Versioning!

**Breaking changes** = Changes that break existing clients:
- Remove an endpoint
- Remove a field from response
- Change field type (string → integer)
- Change required parameters
- Change response format

**Non-breaking changes** = Safe to add:
- Add new endpoint
- Add optional parameter
- Add new field to response

---

## Versioning Strategies

### 1. URL Path Versioning (Most Common)

Version in the URL path:

```
https://api.example.com/v1/users
https://api.example.com/v2/users
```

**Pros:**
- Very clear and visible
- Easy to implement
- Easy to test (just change URL)
- Can route to different code versions

**Cons:**
- URLs become longer
- Can't version individual endpoints

**Recommended!** This is what most APIs use (Stripe, Twitter, GitHub).

### 2. Header Versioning

Version in custom header:

```
GET /api/users
Accept-Version: v2
```

**Pros:**
- Clean URLs
- Can version per request
- Keeps URLs stable

**Cons:**
- Less visible
- Harder to test
- Need to document header

### 3. Query Parameter Versioning

Version as query parameter:

```
GET /api/users?version=2
```

**Pros:**
- Simple to implement
- Easy to test

**Cons:**
- Less clean
- Can conflict with other parameters
- Not standard practice

### 4. Content Negotiation (Accept Header)

Use standard Accept header:

```
GET /api/users
Accept: application/vnd.myapi.v2+json
```

**Pros:**
- RESTful standard
- Very flexible

**Cons:**
- Complex to implement
- Hard to understand

---

## Implementing URL Path Versioning

### Router with Versioning

```php
<?php
// core/Router.php (add versioning support)

class Router {
    private $routes = [];
    private $version = null;

    /**
     * Set API version
     */
    public function version($version, $callback) {
        $this->version = $version;
        $callback($this);
        $this->version = null;
    }

    /**
     * Add route (modified to support versioning)
     */
    private function addRoute($method, $uri, $handler, $middleware = []) {
        // Add version prefix if set
        if ($this->version) {
            $uri = "/{$this->version}" . $uri;
        }

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    // ... rest of Router class
}
```

### Routes with Versions

```php
<?php
// routes/api.php

$router = new Router();

// Version 1
$router->version('v1', function($router) {
    $router->get('/api/users', [V1\UserController::class, 'index']);
    $router->get('/api/users/{id}', [V1\UserController::class, 'show']);
    $router->post('/api/users', [V1\UserController::class, 'store']);
});

// Version 2
$router->version('v2', function($router) {
    $router->get('/api/users', [V2\UserController::class, 'index']);
    $router->get('/api/users/{id}', [V2\UserController::class, 'show']);
    $router->post('/api/users', [V2\UserController::class, 'store']);
});

// Dispatch
$router->dispatch();
```

### Directory Structure

```
controllers/
├── V1/
│   ├── UserController.php
│   └── PostController.php
├── V2/
│   ├── UserController.php
│   └── PostController.php
└── V3/
    ├── UserController.php
    └── PostController.php
```

---

## Version 1 Example

```php
<?php
// controllers/V1/UserController.php

namespace V1;

class UserController {
    /**
     * GET /v1/api/users
     */
    public function index() {
        $users = User::all();

        // V1 format: simple array
        Response::success($users);
    }

    /**
     * GET /v1/api/users/{id}
     */
    public function show($id) {
        $user = User::find($id);

        if (!$user) {
            Response::notFound('User not found');
        }

        // V1: Return all fields
        Response::success($user);
    }

    /**
     * POST /v1/api/users
     */
    public function store() {
        $data = Request::json();

        // V1: Name as single field
        $validator = new Validator($data, [
            'name' => 'required',
            'email' => 'required|email'
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        $user = User::create($data);
        Response::success($user, 201);
    }
}
```

---

## Version 2 Example (Breaking Changes)

```php
<?php
// controllers/V2/UserController.php

namespace V2;

class UserController {
    /**
     * GET /v2/api/users
     */
    public function index() {
        $users = User::all();

        // V2 format: wrapped in 'users' key with metadata
        Response::success([
            'users' => $users,
            'total' => count($users)
        ]);
    }

    /**
     * GET /v2/api/users/{id}
     */
    public function show($id) {
        $user = User::find($id);

        if (!$user) {
            Response::notFound('User not found');
        }

        // V2: Hide sensitive fields
        unset($user['password']);
        unset($user['remember_token']);

        // V2: Add computed fields
        $user['full_url'] = "https://myapp.com/users/{$user['id']}";

        Response::success($user);
    }

    /**
     * POST /v2/api/users
     */
    public function store() {
        $data = Request::json();

        // V2: Split name into first_name and last_name
        $validator = new Validator($data, [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'age' => 'integer'  // New required field in V2
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        // Store with new format
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'age' => $data['age']
        ]);

        Response::success($user, 201);
    }
}
```

**Breaking changes from V1 to V2:**
1. Response format changed (wrapped in 'users' key)
2. Removed password field from response
3. Added full_url computed field
4. Split 'name' into 'first_name' and 'last_name'
5. Added required 'age' field

---

## Default Version

Always specify a default version:

```php
// public/index.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If no version specified, use v1
if (!preg_match('/\/v\d+\//', $uri)) {
    // Redirect to v1
    $uri = str_replace('/api/', '/v1/api/', $uri);

    // Or inject v1 in routing
}
```

---

## Deprecation Warnings

Warn clients when a version will be deprecated:

```php
<?php
// controllers/V1/UserController.php (old version)

public function index() {
    // Add deprecation warning header
    header('X-API-Deprecation: true');
    header('X-API-Sunset: 2024-12-31');
    header('X-API-Migrate: https://api.example.com/v2/users');

    // Include in response
    $users = User::all();

    Response::success($users, 200, [
        'deprecation' => [
            'deprecated' => true,
            'sunset_date' => '2024-12-31',
            'migrate_to' => 'https://api.example.com/v2/users',
            'documentation' => 'https://docs.example.com/migration-v1-v2'
        ]
    ]);
}
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "deprecation": {
      "deprecated": true,
      "sunset_date": "2024-12-31",
      "migrate_to": "https://api.example.com/v2/users",
      "documentation": "https://docs.example.com/migration-v1-v2"
    }
  }
}
```

---

## Version Detection Middleware

```php
<?php
// middleware/VersionMiddleware.php

class VersionMiddleware {
    private static $supportedVersions = ['v1', 'v2'];
    private static $defaultVersion = 'v1';

    public static function handle() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Extract version from URL
        if (preg_match('/\/(v\d+)\//', $uri, $matches)) {
            $version = $matches[1];

            // Check if version is supported
            if (!in_array($version, self::$supportedVersions)) {
                Response::error('Unsupported API version', 400, null, 'UNSUPPORTED_VERSION');
            }

            // Store version for later use
            define('API_VERSION', $version);
        } else {
            // No version specified - use default
            define('API_VERSION', self::$defaultVersion);
        }

        return true;
    }

    /**
     * Get current API version
     */
    public static function getVersion() {
        return defined('API_VERSION') ? API_VERSION : self::$defaultVersion;
    }
}
```

---

## Shared Code Between Versions

Don't duplicate everything! Share common code:

```php
<?php
// controllers/BaseUserController.php

abstract class BaseUserController {
    protected $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Shared validation logic
     */
    protected function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(['email' => 'Invalid email']);
        }

        if (User::findByEmail($email)) {
            throw new ValidationException(['email' => 'Email already exists']);
        }
    }

    /**
     * Shared authorization logic
     */
    protected function checkOwnership($userId) {
        if (Auth::userId() !== $userId && !Auth::isAdmin()) {
            throw new ForbiddenException('You can only modify your own account');
        }
    }
}
```

**Version-specific controllers extend base:**
```php
<?php
// controllers/V1/UserController.php

namespace V1;

class UserController extends BaseUserController {
    public function store() {
        $data = Request::json();

        // Use shared validation
        $this->validateEmail($data['email']);

        // V1-specific logic
        $user = $this->userModel->create($data);
        Response::success($user, 201);
    }
}
```

---

## Versioning Database Changes

Sometimes versions need different database structures:

### Transformers (Recommended)

Keep one database, transform data per version:

```php
<?php
// transformers/V1/UserTransformer.php

namespace V1;

class UserTransformer {
    public static function transform($user) {
        return [
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'], // Combine names
            'email' => $user['email'],
            'created_at' => $user['created_at']
        ];
    }
}
```

```php
// transformers/V2/UserTransformer.php

namespace V2;

class UserTransformer {
    public static function transform($user) {
        return [
            'id' => $user['id'],
            'first_name' => $user['first_name'], // Separate names
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'age' => $user['age'], // New field in V2
            'full_url' => "https://myapp.com/users/{$user['id']}",
            'created_at' => $user['created_at']
        ];
    }
}
```

---

## Migration Guide

Always provide migration documentation:

```markdown
# API Migration Guide: V1 to V2

## Breaking Changes

### 1. User Response Format Changed

**V1:**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "John Doe"}
  ]
}
```

**V2:**
```json
{
  "success": true,
  "data": {
    "users": [
      {"id": 1, "first_name": "John", "last_name": "Doe"}
    ],
    "total": 1
  }
}
```

**Action:** Update response parsing to access `data.users` instead of `data`.

### 2. User Creation Changed

**V1:**
```json
POST /v1/api/users
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

**V2:**
```json
POST /v2/api/users
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "age": 30
}
```

**Action:** Split name into first_name and last_name. Add age field.

## Timeline

- **2024-01-01**: V2 released
- **2024-06-01**: V1 deprecated (warning added)
- **2024-12-31**: V1 shutdown

## Support

Questions? Email: api-support@example.com
```

---

## Best Practices

### 1. Version from Day One

```php
// ❌ Bad - no version
GET /api/users

// ✅ Good - versioned from start
GET /v1/api/users
```

Even if you don't plan changes, start with v1!

### 2. Support Multiple Versions

Keep at least 2 versions:
- Current version
- Previous version (for migration period)

### 3. Announce Deprecation Early

Give users 6-12 months notice before shutting down a version.

### 4. Document Everything

Every breaking change needs:
- What changed
- Why it changed
- How to migrate
- Code examples

### 5. Use Semantic Versioning

- **v1 → v2**: Breaking changes
- **v2.1 → v2.2**: New features (non-breaking)
- **v2.1.1 → v2.1.2**: Bug fixes

---

## Laravel Versioning (Preview)

Laravel makes versioning easier:

```php
// routes/api.php

// V1 routes
Route::prefix('v1')->group(function () {
    Route::get('/users', [V1\UserController::class, 'index']);
});

// V2 routes
Route::prefix('v2')->group(function () {
    Route::get('/users', [V2\UserController::class, 'index']);
});
```

---

## Quick Quiz

**Question 1:** What's a breaking change?
<details>
<summary>Answer</summary>
A change that breaks existing clients: removing endpoints/fields, changing field types, changing required parameters, etc.
</details>

**Question 2:** Which versioning strategy is most common?
<details>
<summary>Answer</summary>
URL path versioning (e.g., /v1/api/users, /v2/api/users) - used by Stripe, GitHub, Twitter.
</details>

**Question 3:** Should you add version from day one?
<details>
<summary>Answer</summary>
YES! Even if you don't plan changes, start with v1. Much easier than retrofitting later.
</details>

**Question 4:** How long should you support old versions?
<details>
<summary>Answer</summary>
Typically 6-12 months after deprecation announcement, giving users time to migrate.
</details>

**Question 5:** What's the benefit of transformers?
<details>
<summary>Answer</summary>
Keep one database structure, transform data differently per version. Avoids database duplication/complexity.
</details>

---

## Summary

You learned:
- Why API versioning is necessary
- Four versioning strategies (URL path, header, query, content negotiation)
- Implementing URL path versioning (recommended)
- Creating version-specific controllers
- Handling breaking changes between versions
- Deprecation warnings and sunset dates
- Sharing code between versions with base classes
- Using transformers for version-specific responses
- Creating migration guides
- Best practices for versioning

---

## Next Lesson

**15-documentation.md** - The final lesson! Learn to document your API so others (and future you) can use it easily!
