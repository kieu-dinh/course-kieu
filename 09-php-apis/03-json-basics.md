# Lesson 03 - Working with JSON in PHP

**Duration**: 45-60 minutes

---

## What is JSON?

**JSON** = **J**ava**S**cript **O**bject **N**otation

A lightweight, text-based format for storing and exchanging data.

**Why JSON?**
- Human-readable
- Language-independent (works in PHP, JavaScript, Python, etc.)
- Lightweight (smaller than XML)
- Native support in JavaScript
- Standard for REST APIs

---

## JSON Syntax

### Basic Structure

JSON consists of:
- **Objects** (key-value pairs in curly braces)
- **Arrays** (lists in square brackets)
- **Values** (strings, numbers, booleans, null)

**Example:**
```json
{
  "name": "John Doe",
  "age": 30,
  "email": "john@example.com",
  "active": true,
  "balance": 99.99,
  "hobbies": ["coding", "reading", "gaming"],
  "address": {
    "street": "123 Main St",
    "city": "Paris"
  },
  "notes": null
}
```

### Data Types

JSON supports 6 data types:

**1. String** (double quotes required):
```json
{
  "name": "John",
  "message": "Hello, world!"
}
```

**2. Number** (integer or float):
```json
{
  "age": 30,
  "price": 19.99,
  "temperature": -5.5
}
```

**3. Boolean**:
```json
{
  "active": true,
  "verified": false
}
```

**4. Null**:
```json
{
  "middleName": null,
  "deletedAt": null
}
```

**5. Array**:
```json
{
  "colors": ["red", "green", "blue"],
  "scores": [85, 90, 78],
  "mixed": [1, "hello", true, null]
}
```

**6. Object**:
```json
{
  "user": {
    "id": 1,
    "name": "John"
  },
  "metadata": {
    "created": "2024-01-15",
    "updated": "2024-01-20"
  }
}
```

### Rules

**Must follow these rules:**
- Keys must be strings in double quotes
- No trailing commas
- No comments allowed
- Use double quotes (not single)

**Valid JSON:**
```json
{
  "name": "John",
  "age": 30
}
```

**Invalid JSON:**
```json
{
  'name': 'John',      // ❌ Single quotes
  "age": 30,           // ❌ Trailing comma
  // This is a comment // ❌ Comments not allowed
}
```

---

## PHP to JSON: json_encode()

Convert PHP arrays/objects to JSON strings.

### Basic Usage

```php
$user = [
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'active' => true
];

$json = json_encode($user);

echo $json;
// Output: {"id":1,"name":"John Doe","email":"john@example.com","active":true}
```

### Associative Arrays → JSON Objects

```php
$person = [
    'name' => 'Alice',
    'age' => 25,
    'city' => 'Paris'
];

echo json_encode($person);
// Output: {"name":"Alice","age":25,"city":"Paris"}
```

### Indexed Arrays → JSON Arrays

```php
$colors = ['red', 'green', 'blue'];

echo json_encode($colors);
// Output: ["red","green","blue"]
```

### Nested Structures

```php
$data = [
    'user' => [
        'id' => 1,
        'name' => 'John'
    ],
    'posts' => [
        ['id' => 1, 'title' => 'First Post'],
        ['id' => 2, 'title' => 'Second Post']
    ],
    'settings' => [
        'theme' => 'dark',
        'notifications' => true
    ]
];

echo json_encode($data);
/*
{
  "user": {"id":1,"name":"John"},
  "posts": [
    {"id":1,"title":"First Post"},
    {"id":2,"title":"Second Post"}
  ],
  "settings": {"theme":"dark","notifications":true}
}
*/
```

### Pretty Print (Formatted Output)

```php
$data = ['name' => 'John', 'age' => 30];

// Default (compact)
echo json_encode($data);
// Output: {"name":"John","age":30}

// Pretty print (readable)
echo json_encode($data, JSON_PRETTY_PRINT);
/*
Output:
{
    "name": "John",
    "age": 30
}
*/
```

### Common Options

```php
$data = [
    'name' => 'Jean-François',
    'url' => 'https://example.com/path?a=1&b=2',
    'html' => '<div>Hello</div>'
];

// Unicode characters (accents)
echo json_encode($data, JSON_UNESCAPED_UNICODE);
// Output: {"name":"Jean-François",...}

// Don't escape slashes
echo json_encode($data, JSON_UNESCAPED_SLASHES);
// Output: {...,"url":"https://example.com/path?a=1&b=2"}

// Don't escape HTML tags
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// Combine multiple options
echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
```

**Common Options:**
- `JSON_PRETTY_PRINT` - Format with indentation
- `JSON_UNESCAPED_UNICODE` - Don't escape Unicode characters (è, ñ, etc.)
- `JSON_UNESCAPED_SLASHES` - Don't escape slashes in URLs
- `JSON_NUMERIC_CHECK` - Convert numeric strings to numbers
- `JSON_FORCE_OBJECT` - Force array to be encoded as object

### Handling Encoding Errors

```php
$data = [
    'name' => 'John',
    'invalid' => "\xB1\x31" // Invalid UTF-8
];

$json = json_encode($data);

if ($json === false) {
    echo "JSON encoding error: " . json_last_error_msg();
    // Output: JSON encoding error: Malformed UTF-8 characters
}
```

---

## JSON to PHP: json_decode()

Convert JSON strings to PHP arrays/objects.

### Basic Usage

```php
$json = '{"name":"John","age":30,"active":true}';

// Decode as associative array (default: object)
$data = json_decode($json, true);

print_r($data);
/*
Array
(
    [name] => John
    [age] => 30
    [active] => 1
)
*/

echo $data['name']; // John
echo $data['age'];  // 30
```

### Decode as Object (default)

```php
$json = '{"name":"John","age":30}';

// Without second parameter, returns object
$obj = json_decode($json);

echo $obj->name; // John
echo $obj->age;  // 30
```

**When to use what:**
- Use `json_decode($json, true)` for **associative arrays** (easier to work with)
- Use `json_decode($json)` for **objects** (if you prefer object notation)

### Decoding Arrays

```php
$json = '["red", "green", "blue"]';

$colors = json_decode($json, true);

print_r($colors);
/*
Array
(
    [0] => red
    [1] => green
    [2] => blue
)
*/
```

### Nested Structures

```php
$json = '{
  "user": {
    "id": 1,
    "name": "John"
  },
  "posts": [
    {"id": 1, "title": "First Post"},
    {"id": 2, "title": "Second Post"}
  ]
}';

$data = json_decode($json, true);

echo $data['user']['name'];           // John
echo $data['posts'][0]['title'];      // First Post
echo count($data['posts']);           // 2
```

### Handling Decoding Errors

```php
$json = '{"name": "John", invalid}'; // Invalid JSON

$data = json_decode($json, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON decoding error: " . json_last_error_msg();
    // Output: JSON decoding error: Syntax error
}
```

**Common Errors:**
- `JSON_ERROR_SYNTAX` - Syntax error in JSON
- `JSON_ERROR_UTF8` - Invalid UTF-8 characters
- `JSON_ERROR_DEPTH` - Maximum stack depth exceeded
- `JSON_ERROR_CTRL_CHAR` - Unexpected control character

### Depth Limit

```php
$json = '{"level1":{"level2":{"level3":{"level4":"value"}}}}';

// Limit depth to 3 levels
$data = json_decode($json, true, 3);

if ($data === null) {
    echo "Error: " . json_last_error_msg();
    // Output: Error: Maximum stack depth exceeded
}
```

---

## API Response Format

### Standard Success Response

```php
function sendSuccess($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);

    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}

// Usage
$user = User::find(5);
sendSuccess($user);

/*
Output:
{
    "success": true,
    "data": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
*/
```

### Standard Error Response

```php
function sendError($message, $statusCode = 400, $errors = []) {
    header('Content-Type: application/json');
    http_response_code($statusCode);

    $response = [
        'success' => false,
        'error' => $message
    ];

    if (!empty($errors)) {
        $response['errors'] = $errors;
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Usage
sendError('Validation failed', 422, [
    'email' => 'Invalid email format',
    'password' => 'Password must be at least 8 characters'
]);

/*
Output:
{
    "success": false,
    "error": "Validation failed",
    "errors": {
        "email": "Invalid email format",
        "password": "Password must be at least 8 characters"
    }
}
*/
```

### Collection Response with Meta

```php
function sendCollection($items, $total, $page = 1, $perPage = 10) {
    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode([
        'success' => true,
        'data' => $items,
        'meta' => [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ]
    ], JSON_PRETTY_PRINT);

    exit;
}

// Usage
$posts = Post::paginate(1, 10); // Page 1, 10 per page
$total = Post::count();

sendCollection($posts, $total, 1, 10);

/*
Output:
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
*/
```

---

## Reading JSON from Request

### Reading JSON POST Data

```php
// Client sends JSON
/*
POST /api/users
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123"
}
*/

// Server reads JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate
if ($data === null) {
    sendError('Invalid JSON', 400);
}

// Access data
$name = $data['name'] ?? null;
$email = $data['email'] ?? null;
$password = $data['password'] ?? null;

// Validate required fields
if (empty($name) || empty($email) || empty($password)) {
    sendError('Missing required fields', 400);
}

// Create user
$user = User::create([
    'name' => $name,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_BCRYPT)
]);

sendSuccess($user, 201);
```

**Important:** `php://input` is a read-only stream for raw POST data. It's the way to read JSON sent in the request body.

### Helper Function

```php
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    return $data;
}

// Usage
$input = getJsonInput();
$name = $input['name'] ?? null;
```

---

## Complete Example: User API

```php
<?php
// api/users.php

header('Content-Type: application/json');

// Helper functions
function sendSuccess($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_PRETTY_PRINT);
    exit;
}

function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    return $data ?? [];
}

// Routes
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// GET /api/users - List all users
if ($method === 'GET' && $uri === '/api/users') {
    $users = User::all();
    sendSuccess($users);
}

// GET /api/users/5 - Get single user
if ($method === 'GET' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $user = User::find($id);

    if (!$user) {
        sendError('User not found', 404);
    }

    sendSuccess($user);
}

// POST /api/users - Create user
if ($method === 'POST' && $uri === '/api/users') {
    $input = getJsonInput();

    // Validate
    $errors = [];
    if (empty($input['name'])) $errors['name'] = 'Name is required';
    if (empty($input['email'])) $errors['email'] = 'Email is required';
    if (empty($input['password'])) $errors['password'] = 'Password is required';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'error' => 'Validation failed',
            'errors' => $errors
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Create
    $user = User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => password_hash($input['password'], PASSWORD_BCRYPT)
    ]);

    sendSuccess($user, 201);
}

// PATCH /api/users/5 - Update user
if ($method === 'PATCH' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    $input = getJsonInput();

    $user = User::find($id);
    if (!$user) {
        sendError('User not found', 404);
    }

    // Update only provided fields
    $updateData = [];
    if (isset($input['name'])) $updateData['name'] = $input['name'];
    if (isset($input['email'])) $updateData['email'] = $input['email'];

    $user->update($updateData);

    sendSuccess($user);
}

// DELETE /api/users/5 - Delete user
if ($method === 'DELETE' && preg_match('/\/api\/users\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];

    $user = User::find($id);
    if (!$user) {
        sendError('User not found', 404);
    }

    $user->delete();

    http_response_code(204);
    exit;
}

// No route matched
sendError('Route not found', 404);
```

---

## Testing JSON APIs

### Using curl

**GET request:**
```bash
curl http://localhost/api/users
```

**POST request with JSON:**
```bash
curl -X POST http://localhost/api/users \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123"
  }'
```

**PATCH request:**
```bash
curl -X PATCH http://localhost/api/users/5 \
  -H "Content-Type: application/json" \
  -d '{"name": "Jane Doe"}'
```

**Pretty print response:**
```bash
curl http://localhost/api/users | jq
```

(requires `jq` - JSON processor)

### Using Postman

1. Open Postman
2. Create new request
3. Set method (GET, POST, etc.)
4. Enter URL: `http://localhost/api/users`
5. For POST/PATCH:
   - Go to "Body" tab
   - Select "raw"
   - Choose "JSON" from dropdown
   - Enter JSON data
6. Click "Send"

---

## Common JSON Issues

### Issue 1: Unicode Characters

```php
$data = ['name' => 'François'];

// Bad - escapes unicode
echo json_encode($data);
// Output: {"name":"Fran\u00e7ois"}

// Good - preserves unicode
echo json_encode($data, JSON_UNESCAPED_UNICODE);
// Output: {"name":"François"}
```

### Issue 2: Numbers as Strings

```php
$data = [
    'id' => '123',      // String
    'age' => '30'       // String
];

// Without JSON_NUMERIC_CHECK
echo json_encode($data);
// Output: {"id":"123","age":"30"}

// With JSON_NUMERIC_CHECK
echo json_encode($data, JSON_NUMERIC_CHECK);
// Output: {"id":123,"age":30}
```

### Issue 3: Empty Arrays vs Objects

```php
$emptyArray = [];

// Becomes array
echo json_encode($emptyArray);
// Output: []

// Force as object
echo json_encode($emptyArray, JSON_FORCE_OBJECT);
// Output: {}
```

### Issue 4: Floating Point Precision

```php
$price = 19.99999999;

echo json_encode(['price' => $price]);
// Output: {"price":19.99999999}

// Round before encoding
echo json_encode(['price' => round($price, 2)]);
// Output: {"price":19.99}
```

### Issue 5: NULL vs Missing Key

```php
$user = [
    'name' => 'John',
    'middleName' => null
];

echo json_encode($user);
// Output: {"name":"John","middleName":null}

// If you want to omit null values
$filtered = array_filter($user, fn($v) => $v !== null);
echo json_encode($filtered);
// Output: {"name":"John"}
```

---

## Best Practices

### 1. Always Set Content-Type Header

```php
header('Content-Type: application/json');
```

Without this, browsers might not parse JSON correctly.

### 2. Always Check JSON Errors

```php
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    sendError('Invalid JSON: ' . json_last_error_msg(), 400);
}
```

### 3. Use Consistent Response Format

Always return same structure:
```json
{
  "success": true,
  "data": {...}
}
```

Or for errors:
```json
{
  "success": false,
  "error": "Error message"
}
```

### 4. Sanitize Output

```php
// Remove sensitive fields
unset($user['password']);
unset($user['api_key']);

sendSuccess($user);
```

### 5. Use Pretty Print in Development

```php
$options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

if (getenv('APP_ENV') === 'development') {
    echo json_encode($data, $options);
} else {
    echo json_encode($data); // Compact in production
}
```

---

## Quick Quiz

**Question 1:** How do you convert a PHP array to JSON?
<details>
<summary>Answer</summary>
Use `json_encode($array)`.
</details>

**Question 2:** How do you convert JSON to a PHP array?
<details>
<summary>Answer</summary>
Use `json_decode($json, true)`. The `true` parameter makes it return an associative array instead of an object.
</details>

**Question 3:** How do you read JSON from a POST request body?
<details>
<summary>Answer</summary>
```php
$json = file_get_contents('php://input');
$data = json_decode($json, true);
```
</details>

**Question 4:** What header should you always set for JSON responses?
<details>
<summary>Answer</summary>
`Content-Type: application/json`
</details>

**Question 5:** How do you check if JSON encoding/decoding failed?
<details>
<summary>Answer</summary>
Check if result is `null` and use `json_last_error()` or `json_last_error_msg()` to get the error.
</details>

---

## Summary

You learned:
- JSON structure and syntax
- `json_encode()` - Convert PHP to JSON
- `json_decode()` - Convert JSON to PHP
- JSON encoding options (pretty print, unicode, etc.)
- Reading JSON from request body with `php://input`
- Standard API response formats
- Helper functions for sending JSON responses
- Common JSON issues and how to handle them
- Best practices for JSON in APIs

---

## Practice Exercise

Create a simple **Products API** that works with JSON:

**Requirements:**
- GET /api/products - Return all products as JSON
- POST /api/products - Create product from JSON
- Response format: `{ "success": true, "data": {...} }`
- Handle JSON validation errors
- Use pretty print for readability

**Sample Product:**
```json
{
  "id": 1,
  "name": "Laptop",
  "price": 999.99,
  "inStock": true
}
```

---

## Next Lesson

**04-first-api.md** - Put everything together and build your first complete API endpoint from scratch!
