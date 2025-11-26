# Lesson 02 - HTTP Methods (Verbs)

**Duration**: 45-60 minutes

---

## What Are HTTP Methods?

HTTP methods (also called **HTTP verbs**) tell the server **what action** you want to perform on a resource.

Think of them like verbs in language:
- **GET** = Read
- **POST** = Create
- **PUT** = Replace
- **PATCH** = Modify
- **DELETE** = Remove

---

## The Five Main HTTP Methods

### 1. GET - Read/Retrieve Data

**Purpose**: Retrieve data from the server. Read-only, doesn't change anything.

**Characteristics:**
- Safe (doesn't modify data)
- Idempotent (can call multiple times, same result)
- Can be cached
- Can be bookmarked
- Parameters in URL (query string)

**Real-World Examples:**
- Loading a web page
- Searching products
- Getting your profile data
- Fetching weather information

**REST Examples:**
```
GET /api/users              → Get all users
GET /api/users/5            → Get user with ID 5
GET /api/posts?author=5     → Get posts by author 5
GET /api/products?page=2    → Get page 2 of products
```

**PHP Implementation:**
```php
// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get all users
    if ($uri === '/api/users') {
        $users = User::all();

        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    }

    // Get single user
    if (preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
        $id = $matches[1];
        $user = User::find($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $user
        ]);
    }
}
```

**Using curl:**
```bash
# Get all users
curl -X GET http://localhost/api/users

# Get specific user
curl -X GET http://localhost/api/users/5

# With query parameters
curl -X GET "http://localhost/api/posts?author=5&limit=10"
```

**Response Format:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Status Codes:**
- `200 OK` - Data found and returned
- `404 Not Found` - Resource doesn't exist

**Key Point:** GET requests should NEVER modify data. If you call GET 100 times, the data should be the same.

---

### 2. POST - Create New Resource

**Purpose**: Send data to create a NEW resource on the server.

**Characteristics:**
- Not safe (creates new data)
- Not idempotent (multiple calls create multiple resources)
- Cannot be cached
- Cannot be bookmarked
- Data in request body (not URL)

**Real-World Examples:**
- Creating a new user account
- Publishing a blog post
- Submitting a form
- Uploading a file

**REST Examples:**
```
POST /api/users       → Create new user
POST /api/posts       → Create new post
POST /api/orders      → Create new order
POST /api/comments    → Create new comment
```

**PHP Implementation:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $uri === '/api/users') {
    // Get JSON data from request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate
    if (empty($input['name']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => [
                'name' => empty($input['name']) ? 'Name is required' : null,
                'email' => empty($input['email']) ? 'Email is required' : null
            ]
        ]);
        exit;
    }

    // Check if email exists
    if (User::findByEmail($input['email'])) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => 'Email already exists'
        ]);
        exit;
    }

    // Create user
    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => password_hash($input['password'], PASSWORD_BCRYPT)
    ]);

    // Return 201 Created with new resource
    http_response_code(201);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}
```

**Using curl:**
```bash
curl -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
  }'
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
```

**Success Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Response (422 Unprocessable):**
```json
{
  "success": false,
  "errors": {
    "email": ["Email already exists"]
  }
}
```

**Status Codes:**
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid JSON or missing required fields
- `422 Unprocessable Entity` - Validation failed

**Key Point:** Each POST request creates a NEW resource. If you POST the same data twice, you get two resources!

---

### 3. PUT - Replace Entire Resource

**Purpose**: Replace an ENTIRE resource with new data.

**Characteristics:**
- Not safe (modifies data)
- Idempotent (multiple identical calls have same result)
- Replaces entire resource
- All fields should be sent

**Real-World Examples:**
- Update your entire profile
- Replace a document
- Change all product details

**REST Examples:**
```
PUT /api/users/5      → Replace user 5 completely
PUT /api/posts/10     → Replace post 10 completely
PUT /api/products/20  → Replace product 20 completely
```

**Important Difference: PUT vs PATCH**

**PUT** = Replace entire resource
```
Current: { id: 5, name: "John", email: "john@example.com", age: 30 }

PUT /api/users/5
{ "name": "Jane" }

Result: { id: 5, name: "Jane", email: null, age: null }
(Other fields are cleared because not sent!)
```

**PATCH** = Update specific fields
```
Current: { id: 5, name: "John", email: "john@example.com", age: 30 }

PATCH /api/users/5
{ "name": "Jane" }

Result: { id: 5, name: "Jane", email: "john@example.com", age: 30 }
(Other fields kept)
```

**PHP Implementation:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);

    // Find user
    $user = User::find($id);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // PUT requires ALL fields
    if (empty($input['name']) || empty($input['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required for PUT']);
        exit;
    }

    // Replace entire resource
    $user->update([
        'name' => $input['name'],
        'email' => $input['email'],
        'bio' => $input['bio'] ?? null,
        'website' => $input['website'] ?? null
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}
```

**Using curl:**
```bash
curl -X PUT http://localhost/api/users/5 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Smith",
    "email": "johnsmith@example.com",
    "bio": "Developer",
    "website": "https://johnsmith.com"
  }'
```

**Status Codes:**
- `200 OK` - Updated successfully
- `404 Not Found` - Resource doesn't exist
- `400 Bad Request` - Missing required fields

---

### 4. PATCH - Update Partial Resource

**Purpose**: Update SPECIFIC fields of a resource.

**Characteristics:**
- Not safe (modifies data)
- Idempotent (multiple identical calls have same result)
- Only updates sent fields
- Other fields remain unchanged

**Real-World Examples:**
- Change just your email
- Update product price only
- Mark post as published

**REST Examples:**
```
PATCH /api/users/5      → Update some fields of user 5
PATCH /api/posts/10     → Update some fields of post 10
PATCH /api/products/20  → Update some fields of product 20
```

**PHP Implementation:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'PATCH' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);

    // Find user
    $user = User::find($id);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Update only provided fields
    $updateData = [];

    if (isset($input['name'])) {
        $updateData['name'] = $input['name'];
    }

    if (isset($input['email'])) {
        $updateData['email'] = $input['email'];
    }

    if (isset($input['bio'])) {
        $updateData['bio'] = $input['bio'];
    }

    // Apply updates
    $user->update($updateData);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $user
    ]);
}
```

**Using curl:**
```bash
# Update only email
curl -X PATCH http://localhost/api/users/5 \
  -H "Content-Type: application/json" \
  -d '{"email": "newemail@example.com"}'

# Update multiple fields
curl -X PATCH http://localhost/api/users/5 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Updated",
    "bio": "Senior Developer"
  }'
```

**Status Codes:**
- `200 OK` - Updated successfully
- `404 Not Found` - Resource doesn't exist
- `422 Unprocessable Entity` - Validation failed

**When to Use PUT vs PATCH:**
- Use **PUT** when client sends complete resource data
- Use **PATCH** when client sends only changed fields
- In practice, **PATCH is more common** because it's more flexible

---

### 5. DELETE - Remove Resource

**Purpose**: Delete a resource from the server.

**Characteristics:**
- Not safe (removes data)
- Idempotent (deleting twice has same effect as deleting once)
- Usually returns no content or minimal confirmation

**Real-World Examples:**
- Delete your account
- Remove a blog post
- Delete a comment
- Cancel an order

**REST Examples:**
```
DELETE /api/users/5      → Delete user 5
DELETE /api/posts/10     → Delete post 10
DELETE /api/comments/88  → Delete comment 88
```

**PHP Implementation:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];

    // Find user
    $user = User::find($id);
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Check if user owns resource (authorization)
    if ($user->id !== $currentUserId && !$currentUserIsAdmin) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Delete
    $user->delete();

    // Option 1: Return 204 No Content (no response body)
    http_response_code(204);
    exit;

    // Option 2: Return 200 OK with confirmation message
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully'
    ]);
}
```

**Using curl:**
```bash
curl -X DELETE http://localhost/api/users/5 \
  -H "Authorization: Bearer your-token"
```

**Response Options:**

**Option 1: 204 No Content (preferred)**
```
Status: 204 No Content
(No response body)
```

**Option 2: 200 OK with message**
```json
{
  "success": true,
  "message": "User deleted successfully"
}
```

**Status Codes:**
- `204 No Content` - Deleted successfully, no response body
- `200 OK` - Deleted successfully with confirmation message
- `404 Not Found` - Resource doesn't exist
- `403 Forbidden` - Not allowed to delete this resource

**Idempotent Behavior:**
```bash
DELETE /api/users/5   → 204 No Content (user deleted)
DELETE /api/users/5   → 404 Not Found (already deleted)
DELETE /api/users/5   → 404 Not Found (still doesn't exist)
```

Result is the same: user doesn't exist.

**Soft Delete Alternative:**

Sometimes you don't actually delete, just mark as deleted:

```php
// Soft delete - just mark as deleted
$user->update(['deleted_at' => date('Y-m-d H:i:s')]);

// Hard delete - actually remove from database
$user->delete();
```

Soft delete is safer (can restore later).

---

## Method Comparison Table

| Method | Purpose | Safe? | Idempotent? | Request Body? | Response Body? |
|--------|---------|-------|-------------|---------------|----------------|
| GET | Read | Yes | Yes | No | Yes |
| POST | Create | No | No | Yes | Yes |
| PUT | Replace | No | Yes | Yes | Yes |
| PATCH | Update | No | Yes | Yes | Yes |
| DELETE | Remove | No | Yes | No | Optional |

**Safe**: Doesn't change server state (read-only)
**Idempotent**: Multiple identical requests have same effect as one request

---

## Complete CRUD Example

Let's implement a complete CRUD API for blog posts:

```php
<?php
// api/posts.php

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// GET - List all posts
if ($method === 'GET' && $uri === '/api/posts') {
    $posts = Post::all();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $posts
    ]);
    exit;
}

// GET - Get single post
if ($method === 'GET' && preg_match('/\/api\/posts\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $post = Post::find($id);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $post
    ]);
    exit;
}

// POST - Create new post
if ($method === 'POST' && $uri === '/api/posts') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate
    if (empty($input['title']) || empty($input['content'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Title and content are required'
        ]);
        exit;
    }

    // Create
    $post = Post::create([
        'title' => $input['title'],
        'content' => $input['content'],
        'author_id' => $currentUserId
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data' => $post
    ]);
    exit;
}

// PUT - Replace entire post
if ($method === 'PUT' && preg_match('/\/api\/posts\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);

    $post = Post::find($id);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Check ownership
    if ($post->author_id !== $currentUserId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Update all fields
    $post->update([
        'title' => $input['title'],
        'content' => $input['content']
    ]);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $post
    ]);
    exit;
}

// PATCH - Update specific fields
if ($method === 'PATCH' && preg_match('/\/api\/posts\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $input = json_decode(file_get_contents('php://input'), true);

    $post = Post::find($id);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Check ownership
    if ($post->author_id !== $currentUserId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Update only provided fields
    $updateData = [];
    if (isset($input['title'])) $updateData['title'] = $input['title'];
    if (isset($input['content'])) $updateData['content'] = $input['content'];

    $post->update($updateData);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $post
    ]);
    exit;
}

// DELETE - Remove post
if ($method === 'DELETE' && preg_match('/\/api\/posts\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];

    $post = Post::find($id);
    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Check ownership
    if ($post->author_id !== $currentUserId) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    $post->delete();

    http_response_code(204);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
```

---

## Testing with curl

**Get all posts:**
```bash
curl -X GET http://localhost/api/posts
```

**Get single post:**
```bash
curl -X GET http://localhost/api/posts/1
```

**Create post:**
```bash
curl -X POST http://localhost/api/posts \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My First Post",
    "content": "This is the content"
  }'
```

**Update entire post (PUT):**
```bash
curl -X PUT http://localhost/api/posts/1 \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Title",
    "content": "Updated content"
  }'
```

**Update partial post (PATCH):**
```bash
curl -X PATCH http://localhost/api/posts/1 \
  -H "Content-Type: application/json" \
  -d '{"title": "New Title Only"}'
```

**Delete post:**
```bash
curl -X DELETE http://localhost/api/posts/1
```

---

## Other HTTP Methods

There are more HTTP methods, but less commonly used:

### HEAD
Like GET, but returns only headers (no body). Used to check if resource exists without downloading it.

```bash
curl -I http://localhost/api/posts/1
```

### OPTIONS
Returns which methods are allowed on a resource. Used for CORS preflight requests.

```
OPTIONS /api/posts
Response: Allow: GET, POST, PUT, DELETE
```

### CONNECT, TRACE
Rarely used. Mostly for debugging and proxying.

---

## Common Mistakes

### Mistake 1: Using POST for Everything
```php
// ❌ Bad
POST /api/getUser
POST /api/updateUser
POST /api/deleteUser

// ✅ Good
GET    /api/users/5
PUT    /api/users/5
DELETE /api/users/5
```

### Mistake 2: Using GET to Modify Data
```php
// ❌ Bad - GET should be safe!
GET /api/users/5/delete
GET /api/posts/10/publish

// ✅ Good
DELETE /api/users/5
PATCH  /api/posts/10  { "status": "published" }
```

### Mistake 3: Not Checking Method
```php
// ❌ Bad - any method works
$post = Post::find($_GET['id']);
$post->delete();

// ✅ Good - check method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
```

### Mistake 4: Wrong Status Codes
```php
// ❌ Bad - everything is 200
http_response_code(200);
echo json_encode(['error' => 'Not found']);

// ✅ Good - proper status codes
http_response_code(404);
echo json_encode(['error' => 'Not found']);
```

---

## Quick Quiz

**Question 1:** Which method should you use to retrieve data?
<details>
<summary>Answer</summary>
GET - it's safe and idempotent, perfect for reading data.
</details>

**Question 2:** What's the difference between PUT and PATCH?
<details>
<summary>Answer</summary>
PUT replaces the entire resource (all fields must be sent). PATCH updates only specific fields (other fields remain unchanged).
</details>

**Question 3:** What status code should you return after successfully creating a resource?
<details>
<summary>Answer</summary>
201 Created
</details>

**Question 4:** Is DELETE idempotent?
<details>
<summary>Answer</summary>
Yes. Deleting the same resource multiple times has the same result - the resource doesn't exist.
</details>

**Question 5:** Should you send a request body with GET?
<details>
<summary>Answer</summary>
No. GET parameters should be in the URL query string. GET requests typically don't have a body.
</details>

---

## Summary

You learned:
- **GET**: Retrieve data (safe, idempotent)
- **POST**: Create new resource (not idempotent)
- **PUT**: Replace entire resource (idempotent)
- **PATCH**: Update specific fields (idempotent)
- **DELETE**: Remove resource (idempotent)
- How to implement each method in PHP
- Proper status codes for each operation
- How to test with curl
- Common mistakes to avoid

---

## Practice Exercise

Build a simple **Tasks API** with these endpoints:

```
GET    /api/tasks           - Get all tasks
GET    /api/tasks/1         - Get task 1
POST   /api/tasks           - Create task
PATCH  /api/tasks/1         - Update task 1
DELETE /api/tasks/1         - Delete task 1
```

Task fields: `id`, `title`, `completed` (boolean), `created_at`

Try implementing it using what you learned!

---

## Next Lesson

**03-json-basics.md** - Deep dive into JSON: encoding, decoding, and handling in PHP!
