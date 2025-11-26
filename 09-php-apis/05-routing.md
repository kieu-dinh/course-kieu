# Lesson 05 - API Routing Without Frameworks

**Duration**: 60-75 minutes

---

## The Problem with Current Approach

In our first API (Lesson 04), routing was messy:

```php
// Bad: Long if-else chains
if ($method === 'GET' && $uri === '/api/books') {
    // ...
}

if ($method === 'GET' && preg_match('/\/api\/books\/(\d+)/', $uri, $matches)) {
    // ...
}

if ($method === 'POST' && $uri === '/api/books') {
    // ...
}

// 20 more if statements...
```

**Problems:**
- Hard to read
- Difficult to maintain
- Can't see all routes at a glance
- No organization
- Repeating validation code

**Solution:** Build a simple Router class!

---

## What We'll Build

A clean routing system like this:

```php
// Define routes (easy to read!)
$router = new Router();

$router->get('/api/books', [BookController::class, 'index']);
$router->get('/api/books/{id}', [BookController::class, 'show']);
$router->post('/api/books', [BookController::class, 'store']);
$router->patch('/api/books/{id}', [BookController::class, 'update']);
$router->delete('/api/books/{id}', [BookController::class, 'destroy']);

// Handle request
$router->dispatch();
```

Much cleaner!

---

## Step 1: The Router Class

Create `core/Router.php`:

```php
<?php
// core/Router.php

class Router {
    private $routes = [];

    /**
     * Register GET route
     */
    public function get($uri, $handler) {
        $this->addRoute('GET', $uri, $handler);
    }

    /**
     * Register POST route
     */
    public function post($uri, $handler) {
        $this->addRoute('POST', $uri, $handler);
    }

    /**
     * Register PUT route
     */
    public function put($uri, $handler) {
        $this->addRoute('PUT', $uri, $handler);
    }

    /**
     * Register PATCH route
     */
    public function patch($uri, $handler) {
        $this->addRoute('PATCH', $uri, $handler);
    }

    /**
     * Register DELETE route
     */
    public function delete($uri, $handler) {
        $this->addRoute('DELETE', $uri, $handler);
    }

    /**
     * Add route to routes array
     */
    private function addRoute($method, $uri, $handler) {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'handler' => $handler
        ];
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path (adjust for your setup)
        $basePath = '/books-api/public';
        $uri = str_replace($basePath, '', $uri);

        // Try to match a route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertToRegex($route['uri']);

            if (preg_match($pattern, $uri, $matches)) {
                // Remove full match, keep only parameters
                array_shift($matches);

                // Call handler with parameters
                return $this->callHandler($route['handler'], $matches);
            }
        }

        // No route matched
        Response::error('Route not found', 404);
    }

    /**
     * Convert route URI to regex pattern
     * Example: /api/books/{id} -> /^\/api\/books\/(\d+)$/
     */
    private function convertToRegex($uri) {
        // Escape forward slashes
        $pattern = str_replace('/', '\/', $uri);

        // Replace {id} with (\d+) for numeric IDs
        $pattern = preg_replace('/\{id\}/', '(\d+)', $pattern);

        // Replace {slug} with ([\w-]+) for slugs
        $pattern = preg_replace('/\{slug\}/', '([\w-]+)', $pattern);

        // Replace {any} with (.+) for any parameter
        $pattern = preg_replace('/\{any\}/', '(.+)', $pattern);

        return '/^' . $pattern . '$/';
    }

    /**
     * Call the route handler
     */
    private function callHandler($handler, $params) {
        if (is_callable($handler)) {
            // Handler is a closure/function
            return call_user_func_array($handler, $params);
        }

        if (is_array($handler)) {
            // Handler is [ControllerClass, 'methodName']
            [$class, $method] = $handler;

            if (!class_exists($class)) {
                Response::error("Controller {$class} not found", 500);
            }

            $controller = new $class();

            if (!method_exists($controller, $method)) {
                Response::error("Method {$method} not found in {$class}", 500);
            }

            return call_user_func_array([$controller, $method], $params);
        }

        Response::error('Invalid route handler', 500);
    }
}
```

---

## Step 2: Controllers

Instead of putting logic in routes, create controllers.

### BookController

```php
<?php
// controllers/BookController.php

require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../helpers/Response.php';

class BookController {
    private $bookModel;

    public function __construct() {
        $this->bookModel = new Book();
    }

    /**
     * GET /api/books
     * List all books
     */
    public function index() {
        $books = $this->bookModel->all();
        Response::success($books);
    }

    /**
     * GET /api/books/{id}
     * Get single book
     */
    public function show($id) {
        $book = $this->bookModel->find($id);

        if (!$book) {
            Response::notFound('Book not found');
        }

        Response::success($book);
    }

    /**
     * POST /api/books
     * Create new book
     */
    public function store() {
        $input = $this->getJsonInput();

        // Validate
        $errors = [];

        if (empty($input['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (empty($input['author'])) {
            $errors['author'] = 'Author is required';
        }

        if (!empty($input['isbn']) && $this->bookModel->isbnExists($input['isbn'])) {
            $errors['isbn'] = 'ISBN already exists';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Create book
        $book = $this->bookModel->create($input);

        Response::success($book, 201);
    }

    /**
     * PATCH /api/books/{id}
     * Update book
     */
    public function update($id) {
        $input = $this->getJsonInput();

        // Check if book exists
        $book = $this->bookModel->find($id);
        if (!$book) {
            Response::notFound('Book not found');
        }

        // Validate
        $errors = [];

        if (isset($input['isbn']) && $this->bookModel->isbnExists($input['isbn'], $id)) {
            $errors['isbn'] = 'ISBN already exists';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Update
        $updated = $this->bookModel->update($id, $input);

        Response::success($updated);
    }

    /**
     * DELETE /api/books/{id}
     * Delete book
     */
    public function destroy($id) {
        // Check if book exists
        $book = $this->bookModel->find($id);
        if (!$book) {
            Response::notFound('Book not found');
        }

        // Delete
        $this->bookModel->delete($id);

        http_response_code(204);
        exit;
    }

    /**
     * Helper to get JSON input
     */
    private function getJsonInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON: ' . json_last_error_msg(), 400);
        }

        return $data ?? [];
    }
}
```

---

## Step 3: Routes File

Create a dedicated file for routes:

```php
<?php
// routes/api.php

require_once __DIR__ . '/../controllers/BookController.php';
require_once __DIR__ . '/../controllers/AuthorController.php';

// Define all routes here
function registerRoutes($router) {
    // Books routes
    $router->get('/api/books', [BookController::class, 'index']);
    $router->get('/api/books/{id}', [BookController::class, 'show']);
    $router->post('/api/books', [BookController::class, 'store']);
    $router->patch('/api/books/{id}', [BookController::class, 'update']);
    $router->delete('/api/books/{id}', [BookController::class, 'destroy']);

    // Authors routes (example)
    $router->get('/api/authors', [AuthorController::class, 'index']);
    $router->get('/api/authors/{id}', [AuthorController::class, 'show']);
    $router->post('/api/authors', [AuthorController::class, 'store']);

    // Closure route (for simple logic)
    $router->get('/api/health', function() {
        Response::success(['status' => 'healthy', 'timestamp' => time()]);
    });
}
```

---

## Step 4: Entry Point

Simplify `public/index.php`:

```php
<?php
// public/index.php

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Autoload dependencies
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../routes/api.php';

// Create router
$router = new Router();

// Register routes
registerRoutes($router);

// Dispatch request
$router->dispatch();
```

**Much cleaner!** 🎉

---

## Advanced Routing Features

### 1. Route Groups

Add prefix to multiple routes:

```php
// core/Router.php - add this method

public function group($prefix, $callback) {
    $originalRoutes = $this->routes;

    // Call the callback to register routes
    $callback($this);

    // Add prefix to new routes
    $newRoutes = array_slice($this->routes, count($originalRoutes));

    foreach ($newRoutes as &$route) {
        $route['uri'] = $prefix . $route['uri'];
    }

    // Replace new routes
    array_splice($this->routes, count($originalRoutes), count($newRoutes), $newRoutes);
}
```

**Usage:**
```php
$router->group('/api', function($router) {
    // These become /api/books, /api/books/{id}, etc.
    $router->get('/books', [BookController::class, 'index']);
    $router->get('/books/{id}', [BookController::class, 'show']);
    $router->post('/books', [BookController::class, 'store']);

    $router->get('/authors', [AuthorController::class, 'index']);
});
```

### 2. Middleware Support

Add authentication/validation before routes:

```php
// core/Router.php - modify route structure

private function addRoute($method, $uri, $handler, $middleware = []) {
    $this->routes[] = [
        'method' => $method,
        'uri' => $uri,
        'handler' => $handler,
        'middleware' => $middleware
    ];
}

public function get($uri, $handler, $middleware = []) {
    $this->addRoute('GET', $uri, $handler, $middleware);
}

// In dispatch() before calling handler
foreach ($route['middleware'] as $middlewareClass) {
    $middleware = new $middlewareClass();
    $middleware->handle();
}
```

**Usage:**
```php
// Protected route - requires authentication
$router->post('/api/books', [BookController::class, 'store'], [
    AuthMiddleware::class
]);

// Multiple middleware
$router->delete('/api/books/{id}', [BookController::class, 'destroy'], [
    AuthMiddleware::class,
    AdminMiddleware::class
]);
```

### 3. Route Parameters

Support multiple parameter types:

```php
// Numeric ID
$router->get('/api/books/{id}', [BookController::class, 'show']);

// String slug
$router->get('/api/books/{slug}', [BookController::class, 'showBySlug']);

// Multiple parameters
$router->get('/api/authors/{id}/books/{bookId}', [BookController::class, 'showAuthorBook']);
```

### 4. Route Names

Name routes for easier reference:

```php
// core/Router.php

private $namedRoutes = [];

public function get($uri, $handler, $middleware = [], $name = null) {
    $this->addRoute('GET', $uri, $handler, $middleware);

    if ($name) {
        $this->namedRoutes[$name] = $uri;
    }
}

public function url($name, $params = []) {
    if (!isset($this->namedRoutes[$name])) {
        return null;
    }

    $uri = $this->namedRoutes[$name];

    // Replace parameters
    foreach ($params as $key => $value) {
        $uri = str_replace('{' . $key . '}', $value, $uri);
    }

    return $uri;
}
```

**Usage:**
```php
// Define named route
$router->get('/api/books/{id}', [BookController::class, 'show'], [], 'books.show');

// Generate URL
$url = $router->url('books.show', ['id' => 5]);
// Result: /api/books/5
```

---

## Complete Example with Multiple Resources

```php
<?php
// routes/api.php

function registerRoutes($router) {
    // Public routes (no authentication)
    $router->group('/api', function($router) {
        // Health check
        $router->get('/health', function() {
            Response::success(['status' => 'ok']);
        });

        // Authentication
        $router->post('/auth/register', [AuthController::class, 'register']);
        $router->post('/auth/login', [AuthController::class, 'login']);

        // Public books listing
        $router->get('/books', [BookController::class, 'index']);
        $router->get('/books/{id}', [BookController::class, 'show']);

        // Public authors
        $router->get('/authors', [AuthorController::class, 'index']);
        $router->get('/authors/{id}', [AuthorController::class, 'show']);
        $router->get('/authors/{id}/books', [AuthorController::class, 'books']);
    });

    // Protected routes (require authentication)
    $router->group('/api', function($router) {
        // Books management (authenticated users)
        $router->post('/books', [BookController::class, 'store'], [AuthMiddleware::class]);
        $router->patch('/books/{id}', [BookController::class, 'update'], [AuthMiddleware::class]);
        $router->delete('/books/{id}', [BookController::class, 'destroy'], [AuthMiddleware::class]);

        // User profile
        $router->get('/me', [UserController::class, 'profile'], [AuthMiddleware::class]);
        $router->patch('/me', [UserController::class, 'updateProfile'], [AuthMiddleware::class]);

        // Admin only routes
        $router->get('/admin/users', [AdminController::class, 'users'], [
            AuthMiddleware::class,
            AdminMiddleware::class
        ]);
    });
}
```

---

## Request Class (Optional Enhancement)

Create a Request class to easily access request data:

```php
<?php
// core/Request.php

class Request {
    private $data = [];

    public function __construct() {
        $this->data = $this->getJsonInput();
    }

    /**
     * Get all input data
     */
    public function all() {
        return $this->data;
    }

    /**
     * Get specific input value
     */
    public function get($key, $default = null) {
        return $this->data[$key] ?? $default;
    }

    /**
     * Check if key exists in input
     */
    public function has($key) {
        return isset($this->data[$key]);
    }

    /**
     * Get only specified keys
     */
    public function only($keys) {
        return array_intersect_key(
            $this->data,
            array_flip($keys)
        );
    }

    /**
     * Get all except specified keys
     */
    public function except($keys) {
        return array_diff_key(
            $this->data,
            array_flip($keys)
        );
    }

    /**
     * Get query parameter
     */
    public function query($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request method
     */
    public function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get request URI
     */
    public function uri() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $data ?? [];
    }
}
```

**Usage in Controller:**
```php
public function store() {
    $request = new Request();

    // Get all data
    $data = $request->all();

    // Get specific field
    $title = $request->get('title');
    $author = $request->get('author', 'Unknown'); // with default

    // Get only certain fields
    $bookData = $request->only(['title', 'author', 'isbn']);

    // Check if field exists
    if ($request->has('isbn')) {
        // ...
    }
}
```

---

## Comparison: Before vs After

### Before (Lesson 04):

```php
// 200 lines of if-else statements in index.php
if ($method === 'GET' && $uri === '/api/books') { /* ... */ }
if ($method === 'GET' && preg_match('/\/api\/books\/(\d+)/', $uri, $matches)) { /* ... */ }
if ($method === 'POST' && $uri === '/api/books') { /* ... */ }
// ... many more
```

### After (Lesson 05):

```php
// Clean, organized routes
$router->get('/api/books', [BookController::class, 'index']);
$router->get('/api/books/{id}', [BookController::class, 'show']);
$router->post('/api/books', [BookController::class, 'store']);
$router->patch('/api/books/{id}', [BookController::class, 'update']);
$router->delete('/api/books/{id}', [BookController::class, 'destroy']);

$router->dispatch();
```

**Benefits:**
- ✅ Easy to read and understand
- ✅ All routes in one place
- ✅ Logic separated into controllers
- ✅ Easy to add new routes
- ✅ Support for middleware
- ✅ Support for route groups
- ✅ Similar to Laravel routing!

---

## Laravel Comparison

### Pure PHP (What you built):
```php
$router->get('/api/books', [BookController::class, 'index']);
$router->post('/api/books', [BookController::class, 'store']);
```

### Laravel:
```php
Route::get('/api/books', [BookController::class, 'index']);
Route::post('/api/books', [BookController::class, 'store']);
```

**Almost identical!** You now understand how Laravel routing works under the hood.

---

## Quick Quiz

**Question 1:** What's the main advantage of using a Router class?
<details>
<summary>Answer</summary>
Clean, organized route definitions instead of long if-else chains. Makes code easier to read, maintain, and scale.
</details>

**Question 2:** What's the purpose of the `convertToRegex()` method?
<details>
<summary>Answer</summary>
Converts route patterns like `/api/books/{id}` into regex patterns like `/^\/api\/books\/(\d+)$/` to match URLs and extract parameters.
</details>

**Question 3:** Why use controllers instead of putting logic in routes?
<details>
<summary>Answer</summary>
Separation of concerns - keeps routing logic separate from business logic. Makes code more organized and testable.
</details>

**Question 4:** What's a route group useful for?
<details>
<summary>Answer</summary>
Applying a common prefix (like `/api`) or middleware to multiple routes without repeating code.
</details>

**Question 5:** How does middleware work in routing?
<details>
<summary>Answer</summary>
Middleware runs before the route handler, performing checks like authentication or validation. If checks fail, request is rejected before reaching the controller.
</details>

---

## Summary

You learned:
- Why routing systems are better than if-else chains
- How to build a Router class from scratch
- Converting route patterns to regex for matching
- Creating controllers to organize logic
- Separating routes into dedicated files
- Advanced features: groups, middleware, named routes
- Request class for easier input handling
- How Laravel routing works (you built a similar system!)

---

## Practice Exercise

**Build a Task API with routing:**

1. Create `TaskController` with methods:
   - `index()` - List tasks
   - `show($id)` - Get single task
   - `store()` - Create task
   - `update($id)` - Update task
   - `destroy($id)` - Delete task
   - `complete($id)` - Mark task as complete

2. Define routes:
```php
$router->get('/api/tasks', [TaskController::class, 'index']);
$router->get('/api/tasks/{id}', [TaskController::class, 'show']);
$router->post('/api/tasks', [TaskController::class, 'store']);
$router->patch('/api/tasks/{id}', [TaskController::class, 'update']);
$router->delete('/api/tasks/{id}', [TaskController::class, 'destroy']);
$router->patch('/api/tasks/{id}/complete', [TaskController::class, 'complete']);
```

Try implementing it!

---

## Next Lesson

**06-request-handling.md** - Deep dive into handling different types of requests, parsing data, and validation!
