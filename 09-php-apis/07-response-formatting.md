# Lesson 07 - JSON Response Formatting and Status Codes

**Duration**: 45-60 minutes

---

## Why Response Format Matters

Consistent API responses make your API:
- **Predictable** - Clients know what to expect
- **Easy to use** - Same structure every time
- **Professional** - Shows attention to detail
- **Debuggable** - Clear error messages

**Bad API** (inconsistent):
```json
// Success response
{"id": 1, "name": "John"}

// Error response
"User not found"

// Another error
{"message": "Invalid email", "code": 400}
```

**Good API** (consistent):
```json
// Success response
{"success": true, "data": {"id": 1, "name": "John"}}

// Error response
{"success": false, "error": "User not found"}

// Validation error
{"success": false, "error": "Validation failed", "errors": {"email": "Invalid"}}
```

---

## HTTP Status Codes

Status codes tell the client what happened **without parsing the response body**.

### Success Codes (2xx)

| Code | Name | When to Use |
|------|------|-------------|
| 200 | OK | Successful GET, PUT, PATCH |
| 201 | Created | Successful POST (resource created) |
| 202 | Accepted | Request accepted, processing async |
| 204 | No Content | Successful DELETE (no response body) |

### Client Error Codes (4xx)

| Code | Name | When to Use |
|------|------|-------------|
| 400 | Bad Request | Invalid syntax, malformed JSON |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not allowed |
| 404 | Not Found | Resource doesn't exist |
| 405 | Method Not Allowed | Wrong HTTP method for endpoint |
| 409 | Conflict | Resource conflict (duplicate email) |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |

### Server Error Codes (5xx)

| Code | Name | When to Use |
|------|------|-------------|
| 500 | Internal Server Error | Unexpected server error (bug!) |
| 502 | Bad Gateway | Upstream service failed |
| 503 | Service Unavailable | Server overloaded/maintenance |

**Rule:** 4xx = client's fault, 5xx = server's fault

---

## Standard Response Formats

### Success Response (Single Resource)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

### Success Response (Collection)

```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "John"},
    {"id": 2, "name": "Jane"}
  ]
}
```

### Success Response with Metadata

```json
{
  "success": true,
  "data": [
    {"id": 1, "title": "Post 1"},
    {"id": 2, "title": "Post 2"}
  ],
  "meta": {
    "total": 50,
    "page": 1,
    "per_page": 10,
    "last_page": 5
  }
}
```

### Error Response (Simple)

```json
{
  "success": false,
  "error": "User not found"
}
```

### Error Response (with Details)

```json
{
  "success": false,
  "error": "Validation failed",
  "errors": {
    "email": "Invalid email format",
    "password": "Password must be at least 8 characters"
  }
}
```

### Error Response (with Code)

```json
{
  "success": false,
  "error": "Unauthorized",
  "code": "AUTH_REQUIRED",
  "message": "You must be logged in to access this resource"
}
```

---

## Enhanced Response Class

Let's build a comprehensive Response helper:

```php
<?php
// helpers/Response.php

class Response {
    /**
     * Send success response
     */
    public static function success($data = null, $statusCode = 200, $meta = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = ['success' => true];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send error response
     */
    public static function error($message, $statusCode = 400, $errors = null, $code = null) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $response = [
            'success' => false,
            'error' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if ($code !== null) {
            $response['code'] = $code;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send created response (201)
     */
    public static function created($data = null, $message = 'Resource created successfully') {
        self::success($data, 201);
    }

    /**
     * Send no content response (204)
     */
    public static function noContent() {
        http_response_code(204);
        exit;
    }

    /**
     * Send not found error (404)
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404, null, 'NOT_FOUND');
    }

    /**
     * Send validation error (422)
     */
    public static function validationError($errors, $message = 'Validation failed') {
        self::error($message, 422, $errors, 'VALIDATION_ERROR');
    }

    /**
     * Send unauthorized error (401)
     */
    public static function unauthorized($message = 'Unauthorized') {
        self::error($message, 401, null, 'UNAUTHORIZED');
    }

    /**
     * Send forbidden error (403)
     */
    public static function forbidden($message = 'Forbidden') {
        self::error($message, 403, null, 'FORBIDDEN');
    }

    /**
     * Send method not allowed error (405)
     */
    public static function methodNotAllowed($allowed = []) {
        if (!empty($allowed)) {
            header('Allow: ' . implode(', ', $allowed));
        }

        self::error('Method not allowed', 405, null, 'METHOD_NOT_ALLOWED');
    }

    /**
     * Send conflict error (409)
     */
    public static function conflict($message = 'Resource already exists') {
        self::error($message, 409, null, 'CONFLICT');
    }

    /**
     * Send too many requests error (429)
     */
    public static function tooManyRequests($message = 'Too many requests', $retryAfter = null) {
        if ($retryAfter) {
            header("Retry-After: {$retryAfter}");
        }

        self::error($message, 429, null, 'TOO_MANY_REQUESTS');
    }

    /**
     * Send internal server error (500)
     */
    public static function serverError($message = 'Internal server error') {
        self::error($message, 500, null, 'INTERNAL_ERROR');
    }

    /**
     * Send collection with pagination
     */
    public static function collection($items, $total, $page, $perPage) {
        $meta = [
            'total' => (int)$total,
            'count' => count($items),
            'per_page' => (int)$perPage,
            'current_page' => (int)$page,
            'total_pages' => (int)ceil($total / $perPage)
        ];

        self::success($items, 200, $meta);
    }
}
```

---

## Usage Examples

### GET Request - Single Resource

```php
// GET /api/users/5

public function show($id) {
    $user = User::find($id);

    if (!$user) {
        Response::notFound('User not found');
    }

    // Remove sensitive data
    unset($user['password']);

    Response::success($user);
}

// Response (200 OK):
{
  "success": true,
  "data": {
    "id": 5,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### GET Request - Collection with Pagination

```php
// GET /api/posts?page=2&per_page=10

public function index() {
    $page = Request::query('page', 1);
    $perPage = Request::query('per_page', 10);

    $offset = ($page - 1) * $perPage;

    // Get posts
    $posts = Post::paginate($offset, $perPage);

    // Get total count
    $total = Post::count();

    Response::collection($posts, $total, $page, $perPage);
}

// Response (200 OK):
{
  "success": true,
  "data": [
    {"id": 11, "title": "Post 11"},
    {"id": 12, "title": "Post 12"}
  ],
  "meta": {
    "total": 50,
    "count": 10,
    "per_page": 10,
    "current_page": 2,
    "total_pages": 5
  }
}
```

### POST Request - Create Resource

```php
// POST /api/users

public function store() {
    $data = Request::json();

    // Validate
    $validator = new Validator($data, [
        'name' => 'required',
        'email' => 'required|email'
    ]);

    if (!$validator->validate()) {
        Response::validationError($validator->errors());
    }

    // Check if email exists
    if (User::findByEmail($data['email'])) {
        Response::conflict('Email already exists');
    }

    // Create user
    $user = User::create($data);
    unset($user['password']);

    Response::created($user);
}

// Response (201 Created):
{
  "success": true,
  "data": {
    "id": 10,
    "name": "Jane Smith",
    "email": "jane@example.com"
  }
}
```

### PATCH Request - Update Resource

```php
// PATCH /api/users/5

public function update($id) {
    $user = User::find($id);

    if (!$user) {
        Response::notFound('User not found');
    }

    $data = Request::json();

    // Validate
    if (Request::has('email')) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Invalid email format']);
        }

        // Check if email taken by another user
        $existing = User::findByEmail($data['email']);
        if ($existing && $existing['id'] != $id) {
            Response::conflict('Email already taken');
        }
    }

    // Update
    $user->update($data);
    unset($user['password']);

    Response::success($user);
}

// Response (200 OK):
{
  "success": true,
  "data": {
    "id": 5,
    "name": "John Updated",
    "email": "john.new@example.com"
  }
}
```

### DELETE Request

```php
// DELETE /api/users/5

public function destroy($id) {
    $user = User::find($id);

    if (!$user) {
        Response::notFound('User not found');
    }

    // Check authorization
    if ($user['id'] !== Auth::userId()) {
        Response::forbidden('You can only delete your own account');
    }

    $user->delete();

    Response::noContent();
}

// Response (204 No Content):
// (Empty response body)
```

### Validation Error

```php
// POST /api/products
// Body: {"name": ""}

public function store() {
    $data = Request::json();

    $validator = new Validator($data, [
        'name' => 'required|min:3',
        'price' => 'required|numeric',
        'category' => 'required|in:electronics,clothing'
    ]);

    if (!$validator->validate()) {
        Response::validationError($validator->errors());
    }

    // ...
}

// Response (422 Unprocessable Entity):
{
  "success": false,
  "error": "Validation failed",
  "code": "VALIDATION_ERROR",
  "errors": {
    "name": "Name must be at least 3 characters",
    "price": "Price is required",
    "category": "Category is required"
  }
}
```

### Authentication Error

```php
// GET /api/profile

public function profile() {
    $token = Request::bearerToken();

    if (!$token) {
        Response::unauthorized('Authentication required');
    }

    $user = Auth::verifyToken($token);

    if (!$user) {
        Response::unauthorized('Invalid token');
    }

    Response::success($user);
}

// Response (401 Unauthorized):
{
  "success": false,
  "error": "Authentication required",
  "code": "UNAUTHORIZED"
}
```

### Authorization Error

```php
// DELETE /api/posts/10

public function destroy($id) {
    $post = Post::find($id);

    if (!$post) {
        Response::notFound('Post not found');
    }

    // Check if user owns the post
    if ($post['author_id'] !== Auth::userId()) {
        Response::forbidden('You can only delete your own posts');
    }

    $post->delete();
    Response::noContent();
}

// Response (403 Forbidden):
{
  "success": false,
  "error": "You can only delete your own posts",
  "code": "FORBIDDEN"
}
```

### Rate Limiting

```php
public function index() {
    $apiKey = Request::header('X-API-Key');

    // Check rate limit
    if (RateLimiter::exceeded($apiKey)) {
        Response::tooManyRequests('Rate limit exceeded. Try again in 60 seconds.', 60);
    }

    // ...
}

// Response (429 Too Many Requests):
// Headers: Retry-After: 60
{
  "success": false,
  "error": "Rate limit exceeded. Try again in 60 seconds.",
  "code": "TOO_MANY_REQUESTS"
}
```

---

## Response Transformers

Sometimes you need to format data before sending:

```php
<?php
// helpers/Transformer.php

class UserTransformer {
    /**
     * Transform single user
     */
    public static function item($user) {
        return [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $user['avatar_url'] ?? 'https://via.placeholder.com/150',
            'created_at' => $user['created_at'],
            'is_active' => (bool)$user['active']
        ];
    }

    /**
     * Transform collection of users
     */
    public static function collection($users) {
        return array_map([self::class, 'item'], $users);
    }

    /**
     * Transform user with posts
     */
    public static function withPosts($user) {
        $data = self::item($user);
        $data['posts'] = PostTransformer::collection($user['posts'] ?? []);
        return $data;
    }
}
```

**Usage:**
```php
public function show($id) {
    $user = User::find($id);

    if (!$user) {
        Response::notFound('User not found');
    }

    // Transform before sending
    $transformed = UserTransformer::item($user);

    Response::success($transformed);
}

public function index() {
    $users = User::all();

    // Transform collection
    $transformed = UserTransformer::collection($users);

    Response::success($transformed);
}
```

---

## Error Logging

Log errors for debugging:

```php
public static function serverError($message = 'Internal server error', $exception = null) {
    // Log error details (but don't expose to client!)
    if ($exception) {
        error_log("Server Error: {$message}");
        error_log("Exception: " . $exception->getMessage());
        error_log("Stack trace: " . $exception->getTraceAsString());
    }

    // Send generic error to client
    self::error('Internal server error', 500, null, 'INTERNAL_ERROR');
}
```

**Usage:**
```php
try {
    $post = Post::create($data);
    Response::success($post, 201);
} catch (Exception $e) {
    // Log full error, send generic message
    Response::serverError('Failed to create post', $e);
}
```

---

## Headers and Content Negotiation

### Set Custom Headers

```php
public static function success($data = null, $statusCode = 200, $headers = []) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    // Add custom headers
    foreach ($headers as $key => $value) {
        header("{$key}: {$value}");
    }

    // ... rest of code
}
```

**Usage:**
```php
Response::success($user, 200, [
    'X-RateLimit-Remaining' => 99,
    'X-RateLimit-Reset' => time() + 3600
]);
```

### CORS Headers

Already covered in entry point, but can add to Response class:

```php
public static function cors() {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
    header('Access-Control-Max-Age: 86400'); // Cache preflight for 24 hours
}
```

---

## Response Compression

For large responses, enable compression:

```php
public static function success($data = null, $statusCode = 200) {
    // Enable gzip compression if supported
    if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false) {
        ob_start('ob_gzhandler');
    }

    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    // ... rest of code
}
```

---

## Response Caching

Add cache headers for GET requests:

```php
public static function cached($data, $maxAge = 300) {
    header('Cache-Control: public, max-age=' . $maxAge);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT');

    self::success($data);
}
```

**Usage:**
```php
public function show($id) {
    $product = Product::find($id);

    if (!$product) {
        Response::notFound();
    }

    // Cache for 5 minutes
    Response::cached($product, 300);
}
```

---

## Complete Example: Blog Post API

```php
<?php
// controllers/PostController.php

class PostController {
    // GET /api/posts - List posts with pagination
    public function index() {
        $page = Request::query('page', 1);
        $perPage = Request::query('per_page', 10);
        $status = Request::query('status', 'published');

        $offset = ($page - 1) * $perPage;

        // Get posts
        $posts = Post::where('status', $status)
            ->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $total = Post::where('status', $status)->count();

        // Transform
        $transformed = PostTransformer::collection($posts);

        Response::collection($transformed, $total, $page, $perPage);
    }

    // GET /api/posts/{id} - Get single post
    public function show($id) {
        $post = Post::with('author', 'comments')->find($id);

        if (!$post) {
            Response::notFound('Post not found');
        }

        // Cache for 5 minutes
        Response::cached(PostTransformer::withRelations($post), 300);
    }

    // POST /api/posts - Create post
    public function store() {
        // Check authentication
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $data = Request::json();

        // Validate
        $validator = new Validator($data, [
            'title' => 'required|min:5|max:200',
            'content' => 'required|min:100',
            'status' => 'in:draft,published',
            'tags' => 'array'
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        // Create post
        try {
            $post = Post::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'author_id' => $user['id'],
                'status' => $data['status'] ?? 'draft'
            ]);

            Response::created(PostTransformer::item($post));
        } catch (Exception $e) {
            Response::serverError('Failed to create post', $e);
        }
    }

    // PATCH /api/posts/{id} - Update post
    public function update($id) {
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $post = Post::find($id);
        if (!$post) {
            Response::notFound('Post not found');
        }

        // Check authorization
        if ($post['author_id'] !== $user['id']) {
            Response::forbidden('You can only edit your own posts');
        }

        $data = Request::json();

        // Validate
        $rules = [];
        if (Request::has('title')) $rules['title'] = 'min:5|max:200';
        if (Request::has('content')) $rules['content'] = 'min:100';
        if (Request::has('status')) $rules['status'] = 'in:draft,published';

        if (!empty($rules)) {
            $validator = new Validator($data, $rules);
            if (!$validator->validate()) {
                Response::validationError($validator->errors());
            }
        }

        // Update
        $post->update($data);

        Response::success(PostTransformer::item($post));
    }

    // DELETE /api/posts/{id} - Delete post
    public function destroy($id) {
        $user = Auth::user();
        if (!$user) {
            Response::unauthorized();
        }

        $post = Post::find($id);
        if (!$post) {
            Response::notFound('Post not found');
        }

        // Check authorization
        if ($post['author_id'] !== $user['id'] && !$user['is_admin']) {
            Response::forbidden('You can only delete your own posts');
        }

        $post->delete();

        Response::noContent();
    }
}
```

---

## Quick Quiz

**Question 1:** What status code should you return when creating a new resource?
<details>
<summary>Answer</summary>
201 Created (not 200 OK)
</details>

**Question 2:** What's the difference between 401 and 403?
<details>
<summary>Answer</summary>
401 Unauthorized = not authenticated (no valid credentials)
403 Forbidden = authenticated but not allowed (no permission)
</details>

**Question 3:** Should you expose detailed error messages to clients in production?
<details>
<summary>Answer</summary>
No! Log details server-side, send generic messages to client. Otherwise you expose security information.
</details>

**Question 4:** What status code for validation errors?
<details>
<summary>Answer</summary>
422 Unprocessable Entity
</details>

**Question 5:** Why use transformers before sending responses?
<details>
<summary>Answer</summary>
To format data consistently, remove sensitive fields, control exactly what data is exposed, and keep response structure clean.
</details>

---

## Summary

You learned:
- HTTP status codes and when to use each
- Standard response formats (success, error, validation)
- Building a comprehensive Response helper class
- Response transformers for data formatting
- Error logging without exposing details
- Custom headers and CORS
- Response caching and compression
- Complete examples with proper status codes and error handling

---

## Next Lesson

**08-authentication.md** - Secure your API with API keys, tokens, and authentication strategies!
