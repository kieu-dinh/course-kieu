# Lesson 04 - Build Your First API Endpoint

**Duration**: 60-90 minutes

---

## What We're Building

A simple **Books API** with these features:
- List all books
- Get single book
- Add new book
- Update book
- Delete book

All using pure PHP (no frameworks)!

---

## Project Structure

Let's organize our API properly:

```
books-api/
├── config/
│   └── database.php       # Database connection
├── models/
│   └── Book.php           # Book model
├── helpers/
│   └── Response.php       # JSON response helpers
├── public/
│   └── index.php          # Entry point (API routes)
├── database/
│   └── books.sql          # Database schema
└── .htaccess              # URL rewriting
```

---

## Step 1: Database Setup

### Create Database

```sql
-- database/books.sql

CREATE DATABASE books_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE books_api;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    year INT,
    pages INT,
    available BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO books (title, author, isbn, year, pages, available) VALUES
('Clean Code', 'Robert Martin', '978-0132350884', 2008, 464, true),
('The Pragmatic Programmer', 'Andy Hunt', '978-0201616224', 1999, 352, true),
('Design Patterns', 'Gang of Four', '978-0201633612', 1994, 395, false);
```

Run this in TablePlus or command line:
```bash
mysql -u root < database/books.sql
```

---

## Step 2: Database Configuration

Create database connection file:

```php
<?php
// config/database.php

class Database {
    private static $instance = null;
    private $connection;

    private $host = 'localhost';
    private $database = 'books_api';
    private $username = 'root';
    private $password = '';

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Database connection failed'
            ]);
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}
```

**Why Singleton Pattern?**
- Only one database connection for entire request
- Saves resources
- Prevents multiple connections

---

## Step 3: Response Helper

Create helper for consistent JSON responses:

```php
<?php
// helpers/Response.php

class Response {
    /**
     * Send success response
     */
    public static function success($data = null, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);

        $response = ['success' => true];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     */
    public static function error($message, $statusCode = 400, $errors = []) {
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

    /**
     * Send validation error
     */
    public static function validationError($errors) {
        self::error('Validation failed', 422, $errors);
    }

    /**
     * Send not found error
     */
    public static function notFound($message = 'Resource not found') {
        self::error($message, 404);
    }

    /**
     * Send method not allowed
     */
    public static function methodNotAllowed() {
        self::error('Method not allowed', 405);
    }
}
```

---

## Step 4: Book Model

Create model to interact with database:

```php
<?php
// models/Book.php

require_once __DIR__ . '/../config/database.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all books
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM books ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find book by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new book
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO books (title, author, isbn, year, pages, available)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['title'],
            $data['author'],
            $data['isbn'] ?? null,
            $data['year'] ?? null,
            $data['pages'] ?? null,
            $data['available'] ?? true
        ]);

        // Return created book
        return $this->find($this->db->lastInsertId());
    }

    /**
     * Update book
     */
    public function update($id, $data) {
        // Build update query dynamically based on provided fields
        $fields = [];
        $values = [];

        if (isset($data['title'])) {
            $fields[] = 'title = ?';
            $values[] = $data['title'];
        }

        if (isset($data['author'])) {
            $fields[] = 'author = ?';
            $values[] = $data['author'];
        }

        if (isset($data['isbn'])) {
            $fields[] = 'isbn = ?';
            $values[] = $data['isbn'];
        }

        if (isset($data['year'])) {
            $fields[] = 'year = ?';
            $values[] = $data['year'];
        }

        if (isset($data['pages'])) {
            $fields[] = 'pages = ?';
            $values[] = $data['pages'];
        }

        if (isset($data['available'])) {
            $fields[] = 'available = ?';
            $values[] = $data['available'];
        }

        if (empty($fields)) {
            return false; // Nothing to update
        }

        $values[] = $id; // Add ID for WHERE clause

        $sql = "UPDATE books SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->find($id);
    }

    /**
     * Delete book
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Check if ISBN already exists
     */
    public function isbnExists($isbn, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM books WHERE isbn = ? AND id != ?");
            $stmt->execute([$isbn, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM books WHERE isbn = ?");
            $stmt->execute([$isbn]);
        }

        return $stmt->fetch() !== false;
    }
}
```

---

## Step 5: URL Rewriting

Create `.htaccess` file for clean URLs:

```apache
# .htaccess

# Enable rewrite engine
RewriteEngine On

# Redirect all requests to public/index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ public/index.php [QSA,L]
```

This allows URLs like:
- `/api/books` instead of `/public/index.php?url=api/books`
- `/api/books/1` instead of `/public/index.php?url=api/books&id=1`

---

## Step 6: API Routes (Main Entry Point)

Create the main API file:

```php
<?php
// public/index.php

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set CORS headers (allow all origins for development)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../models/Book.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if API is in subdirectory
// Adjust this based on your setup
$uri = str_replace('/books-api/public', '', $uri);

// Helper to get JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        Response::error('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    return $data ?? [];
}

// Instantiate model
$bookModel = new Book();

// ============================================
// ROUTES
// ============================================

// GET /api/books - List all books
if ($method === 'GET' && $uri === '/api/books') {
    $books = $bookModel->all();
    Response::success($books);
}

// GET /api/books/{id} - Get single book
if ($method === 'GET' && preg_match('/^\/api\/books\/(\d+)$/', $uri, $matches)) {
    $id = $matches[1];
    $book = $bookModel->find($id);

    if (!$book) {
        Response::notFound('Book not found');
    }

    Response::success($book);
}

// POST /api/books - Create new book
if ($method === 'POST' && $uri === '/api/books') {
    $input = getJsonInput();

    // Validate required fields
    $errors = [];

    if (empty($input['title'])) {
        $errors['title'] = 'Title is required';
    }

    if (empty($input['author'])) {
        $errors['author'] = 'Author is required';
    }

    if (!empty($input['isbn']) && $bookModel->isbnExists($input['isbn'])) {
        $errors['isbn'] = 'ISBN already exists';
    }

    if (!empty($input['year']) && ($input['year'] < 1000 || $input['year'] > date('Y'))) {
        $errors['year'] = 'Invalid year';
    }

    if (!empty($input['pages']) && $input['pages'] < 1) {
        $errors['pages'] = 'Pages must be positive number';
    }

    if (!empty($errors)) {
        Response::validationError($errors);
    }

    // Create book
    $book = $bookModel->create($input);

    Response::success($book, 201);
}

// PUT /api/books/{id} - Update entire book (replace)
if ($method === 'PUT' && preg_match('/^\/api\/books\/(\d+)$/', $uri, $matches)) {
    $id = $matches[1];
    $input = getJsonInput();

    // Check if book exists
    $book = $bookModel->find($id);
    if (!$book) {
        Response::notFound('Book not found');
    }

    // Validate (PUT requires all fields)
    $errors = [];

    if (empty($input['title'])) {
        $errors['title'] = 'Title is required';
    }

    if (empty($input['author'])) {
        $errors['author'] = 'Author is required';
    }

    if (!empty($errors)) {
        Response::validationError($errors);
    }

    // Update book
    $updated = $bookModel->update($id, $input);

    Response::success($updated);
}

// PATCH /api/books/{id} - Update specific fields
if ($method === 'PATCH' && preg_match('/^\/api\/books\/(\d+)$/', $uri, $matches)) {
    $id = $matches[1];
    $input = getJsonInput();

    // Check if book exists
    $book = $bookModel->find($id);
    if (!$book) {
        Response::notFound('Book not found');
    }

    // Validate provided fields
    $errors = [];

    if (isset($input['year']) && ($input['year'] < 1000 || $input['year'] > date('Y'))) {
        $errors['year'] = 'Invalid year';
    }

    if (isset($input['pages']) && $input['pages'] < 1) {
        $errors['pages'] = 'Pages must be positive number';
    }

    if (isset($input['isbn']) && $bookModel->isbnExists($input['isbn'], $id)) {
        $errors['isbn'] = 'ISBN already exists';
    }

    if (!empty($errors)) {
        Response::validationError($errors);
    }

    // Update book
    $updated = $bookModel->update($id, $input);

    Response::success($updated);
}

// DELETE /api/books/{id} - Delete book
if ($method === 'DELETE' && preg_match('/^\/api\/books\/(\d+)$/', $uri, $matches)) {
    $id = $matches[1];

    // Check if book exists
    $book = $bookModel->find($id);
    if (!$book) {
        Response::notFound('Book not found');
    }

    // Delete book
    $bookModel->delete($id);

    // Return 204 No Content
    http_response_code(204);
    exit;
}

// No route matched
Response::error('Endpoint not found', 404);
```

---

## Testing the API

### 1. List All Books

```bash
curl http://localhost/books-api/public/api/books
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Clean Code",
            "author": "Robert Martin",
            "isbn": "978-0132350884",
            "year": 2008,
            "pages": 464,
            "available": true,
            "created_at": "2024-01-15 10:30:00",
            "updated_at": "2024-01-15 10:30:00"
        }
    ]
}
```

### 2. Get Single Book

```bash
curl http://localhost/books-api/public/api/books/1
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Clean Code",
        "author": "Robert Martin",
        "isbn": "978-0132350884",
        "year": 2008,
        "pages": 464,
        "available": true
    }
}
```

### 3. Create New Book

```bash
curl -X POST http://localhost/books-api/public/api/books \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Laravel Up and Running",
    "author": "Matt Stauffer",
    "isbn": "978-1492041207",
    "year": 2019,
    "pages": 472,
    "available": true
  }'
```

**Response (201 Created):**
```json
{
    "success": true,
    "data": {
        "id": 4,
        "title": "Laravel Up and Running",
        "author": "Matt Stauffer",
        "isbn": "978-1492041207",
        "year": 2019,
        "pages": 472,
        "available": true,
        "created_at": "2024-01-15 11:00:00",
        "updated_at": "2024-01-15 11:00:00"
    }
}
```

### 4. Update Book (PATCH - partial)

```bash
curl -X PATCH http://localhost/books-api/public/api/books/4 \
  -H "Content-Type: application/json" \
  -d '{
    "pages": 480,
    "available": false
  }'
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 4,
        "title": "Laravel Up and Running",
        "author": "Matt Stauffer",
        "pages": 480,
        "available": false
    }
}
```

### 5. Delete Book

```bash
curl -X DELETE http://localhost/books-api/public/api/books/4
```

**Response:** 204 No Content (empty response)

### 6. Validation Error

```bash
curl -X POST http://localhost/books-api/public/api/books \
  -H "Content-Type: application/json" \
  -d '{
    "title": "",
    "author": ""
  }'
```

**Response (422 Unprocessable):**
```json
{
    "success": false,
    "error": "Validation failed",
    "errors": {
        "title": "Title is required",
        "author": "Author is required"
    }
}
```

### 7. Not Found Error

```bash
curl http://localhost/books-api/public/api/books/999
```

**Response (404 Not Found):**
```json
{
    "success": false,
    "error": "Book not found"
}
```

---

## Testing with Postman

### Import as Collection

Create a Postman collection:

1. **Get All Books**
   - Method: GET
   - URL: `http://localhost/books-api/public/api/books`

2. **Get Single Book**
   - Method: GET
   - URL: `http://localhost/books-api/public/api/books/1`

3. **Create Book**
   - Method: POST
   - URL: `http://localhost/books-api/public/api/books`
   - Body (JSON):
   ```json
   {
     "title": "New Book",
     "author": "Author Name",
     "isbn": "978-1234567890",
     "year": 2024,
     "pages": 300
   }
   ```

4. **Update Book**
   - Method: PATCH
   - URL: `http://localhost/books-api/public/api/books/1`
   - Body (JSON):
   ```json
   {
     "available": false
   }
   ```

5. **Delete Book**
   - Method: DELETE
   - URL: `http://localhost/books-api/public/api/books/1`

---

## What You Built

Congratulations! You built a complete REST API with:

✅ **CRUD operations** (Create, Read, Update, Delete)
✅ **RESTful URLs** (`/api/books`, `/api/books/{id}`)
✅ **HTTP methods** (GET, POST, PATCH, DELETE)
✅ **JSON requests/responses**
✅ **Validation** with error messages
✅ **Proper status codes** (200, 201, 204, 404, 422)
✅ **Database integration** with PDO
✅ **Clean architecture** (Model, Response helper, Routes)
✅ **Error handling**

---

## Improvements to Consider

### 1. Filtering and Search

```php
// GET /api/books?author=Martin&available=true
if ($method === 'GET' && $uri === '/api/books') {
    $author = $_GET['author'] ?? null;
    $available = $_GET['available'] ?? null;

    // Build query with filters
    $sql = "SELECT * FROM books WHERE 1=1";
    $params = [];

    if ($author) {
        $sql .= " AND author LIKE ?";
        $params[] = "%{$author}%";
    }

    if ($available !== null) {
        $sql .= " AND available = ?";
        $params[] = $available === 'true' ? 1 : 0;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll();

    Response::success($books);
}
```

### 2. Pagination

```php
// GET /api/books?page=2&per_page=10
$page = $_GET['page'] ?? 1;
$perPage = $_GET['per_page'] ?? 10;
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT * FROM books LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$books = $stmt->fetchAll();

// Get total count
$total = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();

Response::success([
    'items' => $books,
    'meta' => [
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'last_page' => ceil($total / $perPage)
    ]
]);
```

### 3. Sorting

```php
// GET /api/books?sort=year&order=desc
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

// Whitelist allowed sort fields (security!)
$allowedSort = ['title', 'author', 'year', 'pages', 'created_at'];
if (!in_array($sort, $allowedSort)) {
    $sort = 'created_at';
}

$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$stmt = $db->query("SELECT * FROM books ORDER BY {$sort} {$order}");
$books = $stmt->fetchAll();

Response::success($books);
```

---

## Quick Quiz

**Question 1:** What's the benefit of using a Response helper class?
<details>
<summary>Answer</summary>
Consistent response format across all endpoints, less code duplication, easier to maintain and modify response structure.
</details>

**Question 2:** Why use PDO prepared statements instead of direct queries?
<details>
<summary>Answer</summary>
Security - prevents SQL injection attacks. Also better performance with repeated queries.
</details>

**Question 3:** What status code should you return when successfully creating a resource?
<details>
<summary>Answer</summary>
201 Created (not 200 OK)
</details>

**Question 4:** When should you use PUT vs PATCH?
<details>
<summary>Answer</summary>
PUT - replace entire resource (all fields required). PATCH - update specific fields only.
</details>

**Question 5:** What does `.htaccess` do in this API?
<details>
<summary>Answer</summary>
Rewrites URLs to route all requests through index.php, enabling clean URLs like `/api/books` instead of `/index.php?url=api/books`.
</details>

---

## Summary

You learned:
- How to structure an API project
- Database connection with singleton pattern
- Creating a reusable Response helper
- Building a complete Book model with CRUD operations
- Implementing all HTTP methods (GET, POST, PUT, PATCH, DELETE)
- Routing requests based on URI and method
- Validating input data
- Proper error handling and status codes
- Testing APIs with curl and Postman

---

## Challenge

Extend the Books API with:
1. **Categories**: Books can belong to categories (Fiction, Non-Fiction, etc.)
2. **Authors Endpoint**: Separate `/api/authors` with author details
3. **Borrowing**: Track who borrowed which book and when
4. **Reviews**: Add `/api/books/{id}/reviews` endpoint

Try implementing these features using what you learned!

---

## Next Lesson

**05-routing.md** - Build a proper router system to organize routes better and scale your API!
