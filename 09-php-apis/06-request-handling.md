# Lesson 06 - Request Handling and Validation

**Duration**: 60-75 minutes

---

## Understanding Requests

When a client makes an API request, they can send data in different ways:

**1. URL Parameters (Query String)**
```
GET /api/products?category=electronics&min_price=100&max_price=500
```

**2. URL Path Parameters**
```
GET /api/users/5
DELETE /api/posts/123
```

**3. Request Body (JSON)**
```
POST /api/users
Content-Type: application/json

{
  "name": "John",
  "email": "john@example.com"
}
```

**4. Headers**
```
Authorization: Bearer abc123xyz
Content-Type: application/json
X-API-Key: your-api-key
```

Let's learn to handle each type properly!

---

## Reading Query Parameters

Query parameters come after `?` in the URL.

### Basic Usage

```php
// GET /api/products?category=electronics&sort=price
$category = $_GET['category'] ?? null;  // 'electronics'
$sort = $_GET['sort'] ?? null;          // 'price'
```

### Request Helper Class

Create a better way to access request data:

```php
<?php
// core/Request.php

class Request {
    /**
     * Get query parameter
     */
    public static function query($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public static function queryAll() {
        return $_GET;
    }

    /**
     * Check if query parameter exists
     */
    public static function hasQuery($key) {
        return isset($_GET[$key]);
    }
}
```

**Usage:**
```php
$category = Request::query('category', 'all');
$page = Request::query('page', 1);
$perPage = Request::query('per_page', 10);
```

### Filtering Example

```php
// GET /api/products?category=electronics&min_price=100&max_price=500

public function index() {
    $category = Request::query('category');
    $minPrice = Request::query('min_price');
    $maxPrice = Request::query('max_price');

    // Build query
    $sql = "SELECT * FROM products WHERE 1=1";
    $params = [];

    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    if ($minPrice) {
        $sql .= " AND price >= ?";
        $params[] = $minPrice;
    }

    if ($maxPrice) {
        $sql .= " AND price <= ?";
        $params[] = $maxPrice;
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    Response::success($products);
}
```

### Pagination with Query Parameters

```php
// GET /api/products?page=2&per_page=20

public function index() {
    $page = Request::query('page', 1);
    $perPage = Request::query('per_page', 10);

    // Validate
    $page = max(1, (int)$page);
    $perPage = min(100, max(1, (int)$perPage)); // Max 100 per page

    $offset = ($page - 1) * $perPage;

    // Get products
    $stmt = $db->prepare("SELECT * FROM products LIMIT ? OFFSET ?");
    $stmt->execute([$perPage, $offset]);
    $products = $stmt->fetchAll();

    // Get total count
    $total = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();

    Response::success([
        'data' => $products,
        'meta' => [
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage)
        ]
    ]);
}
```

---

## Reading Path Parameters

Path parameters are part of the URL path.

```php
// GET /api/users/5
// The "5" is extracted by the router

public function show($id) {
    // $id is automatically passed by router
    $user = User::find($id);

    if (!$user) {
        Response::notFound('User not found');
    }

    Response::success($user);
}
```

### Multiple Parameters

```php
// GET /api/authors/5/books/10

$router->get('/api/authors/{authorId}/books/{bookId}',
    [BookController::class, 'showAuthorBook']
);

public function showAuthorBook($authorId, $bookId) {
    $author = Author::find($authorId);
    if (!$author) {
        Response::notFound('Author not found');
    }

    $book = Book::find($bookId);
    if (!$book || $book->author_id != $authorId) {
        Response::notFound('Book not found');
    }

    Response::success($book);
}
```

---

## Reading Request Body (JSON)

For POST, PUT, PATCH requests, data comes in the request body.

### Basic JSON Parsing

```php
public static function json() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        Response::error('Invalid JSON: ' . json_last_error_msg(), 400);
    }

    return $data ?? [];
}
```

### Full Request Class

```php
<?php
// core/Request.php

class Request {
    private static $jsonData = null;

    /**
     * Get JSON input from request body
     */
    public static function json() {
        if (self::$jsonData === null) {
            $json = file_get_contents('php://input');
            self::$jsonData = json_decode($json, true) ?? [];

            if (self::$jsonData === null && json_last_error() !== JSON_ERROR_NONE) {
                Response::error('Invalid JSON: ' . json_last_error_msg(), 400);
            }
        }

        return self::$jsonData;
    }

    /**
     * Get specific field from JSON input
     */
    public static function input($key, $default = null) {
        $data = self::json();
        return $data[$key] ?? $default;
    }

    /**
     * Get multiple fields from JSON input
     */
    public static function only($keys) {
        $data = self::json();
        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * Get all except specified fields
     */
    public static function except($keys) {
        $data = self::json();
        return array_diff_key($data, array_flip($keys));
    }

    /**
     * Check if field exists in JSON input
     */
    public static function has($key) {
        $data = self::json();
        return isset($data[$key]);
    }

    /**
     * Get query parameter
     */
    public static function query($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get all query parameters
     */
    public static function queryAll() {
        return $_GET;
    }

    /**
     * Get request method
     */
    public static function method() {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get request URI
     */
    public static function uri() {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * Get request header
     */
    public static function header($key, $default = null) {
        $headers = getallheaders();
        return $headers[$key] ?? $default;
    }

    /**
     * Get authorization bearer token
     */
    public static function bearerToken() {
        $header = self::header('Authorization', '');

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
```

### Usage Examples

```php
// POST /api/users
// Body: {"name":"John","email":"john@example.com","age":30}

public function store() {
    // Get all data
    $data = Request::json();
    // ['name' => 'John', 'email' => 'john@example.com', 'age' => 30]

    // Get specific field
    $name = Request::input('name');
    $email = Request::input('email');

    // Get with default value
    $age = Request::input('age', 18);

    // Get only specific fields
    $userData = Request::only(['name', 'email']);
    // ['name' => 'John', 'email' => 'john@example.com']

    // Get all except password
    $safeData = Request::except(['password']);

    // Check if field exists
    if (Request::has('newsletter')) {
        // Subscribe to newsletter
    }
}
```

---

## Validation

Always validate input before using it!

### Simple Validation

```php
public function store() {
    $data = Request::json();

    $errors = [];

    // Required fields
    if (empty($data['name'])) {
        $errors['name'] = 'Name is required';
    }

    if (empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($data['password']) < 8) {
        $errors['password'] = 'Password must be at least 8 characters';
    }

    if (!empty($errors)) {
        Response::validationError($errors);
    }

    // Validation passed, create user
    $user = User::create($data);
    Response::success($user, 201);
}
```

### Validator Class

Create a reusable validator:

```php
<?php
// core/Validator.php

class Validator {
    private $data = [];
    private $rules = [];
    private $errors = [];

    public function __construct($data, $rules) {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Validate data against rules
     */
    public function validate() {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Get validation errors
     */
    public function errors() {
        return $this->errors;
    }

    /**
     * Apply a single validation rule
     */
    private function applyRule($field, $rule) {
        $value = $this->data[$field] ?? null;

        // Parse rule (e.g., "min:8" -> rule="min", param="8")
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->errors[$field] = ucfirst($field) . ' is required';
                }
                break;

            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = 'Invalid email format';
                }
                break;

            case 'min':
                if ($value && strlen($value) < $param) {
                    $this->errors[$field] = ucfirst($field) . " must be at least {$param} characters";
                }
                break;

            case 'max':
                if ($value && strlen($value) > $param) {
                    $this->errors[$field] = ucfirst($field) . " must not exceed {$param} characters";
                }
                break;

            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must be a number';
                }
                break;

            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->errors[$field] = ucfirst($field) . ' must be an integer';
                }
                break;

            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->errors[$field] = 'Invalid URL format';
                }
                break;

            case 'in':
                $allowed = explode(',', $param);
                if ($value && !in_array($value, $allowed)) {
                    $this->errors[$field] = ucfirst($field) . ' must be one of: ' . $param;
                }
                break;

            case 'alpha':
                if ($value && !ctype_alpha($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must contain only letters';
                }
                break;

            case 'alphanumeric':
                if ($value && !ctype_alnum($value)) {
                    $this->errors[$field] = ucfirst($field) . ' must contain only letters and numbers';
                }
                break;

            case 'boolean':
                if ($value !== null && !is_bool($value) && !in_array($value, [0, 1, '0', '1', true, false])) {
                    $this->errors[$field] = ucfirst($field) . ' must be true or false';
                }
                break;
        }
    }
}
```

### Using Validator

```php
public function store() {
    $data = Request::json();

    // Define validation rules
    $validator = new Validator($data, [
        'name' => 'required|min:3|max:100',
        'email' => 'required|email',
        'password' => 'required|min:8',
        'age' => 'numeric',
        'website' => 'url',
        'role' => 'required|in:user,admin,editor'
    ]);

    // Validate
    if (!$validator->validate()) {
        Response::validationError($validator->errors());
    }

    // Validation passed
    $user = User::create($data);
    Response::success($user, 201);
}
```

### Custom Validation Rules

Add to Validator class:

```php
/**
 * Add custom validation rule
 */
public function addRule($ruleName, $callback) {
    $this->customRules[$ruleName] = $callback;
}

// In applyRule(), add at the end:
if (isset($this->customRules[$ruleName])) {
    $error = $this->customRules[$ruleName]($value, $param, $this->data);
    if ($error) {
        $this->errors[$field] = $error;
    }
}
```

**Usage:**
```php
$validator = new Validator($data, [
    'email' => 'required|email|unique_email'
]);

// Add custom rule
$validator->addRule('unique_email', function($value) {
    if (User::findByEmail($value)) {
        return 'Email already exists';
    }
    return null;
});

$validator->validate();
```

---

## Sanitization

Clean user input before saving:

```php
class Sanitizer {
    /**
     * Sanitize string
     */
    public static function string($value) {
        return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Sanitize email
     */
    public static function email($value) {
        return filter_var($value, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize integer
     */
    public static function int($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function float($value) {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize URL
     */
    public static function url($value) {
        return filter_var($value, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize array recursively
     */
    public static function array($data) {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = self::array($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = self::string($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
```

**Usage:**
```php
public function store() {
    $data = Request::json();

    // Sanitize input
    $name = Sanitizer::string($data['name']);
    $email = Sanitizer::email($data['email']);
    $age = Sanitizer::int($data['age']);

    // Or sanitize entire array
    $cleanData = Sanitizer::array($data);

    // Now safe to use
    $user = User::create($cleanData);
    Response::success($user, 201);
}
```

---

## Complete Example: Product API with Validation

```php
<?php
// controllers/ProductController.php

class ProductController {
    public function index() {
        // Get query parameters
        $category = Request::query('category');
        $minPrice = Request::query('min_price');
        $maxPrice = Request::query('max_price');
        $search = Request::query('search');
        $sort = Request::query('sort', 'created_at');
        $order = Request::query('order', 'DESC');
        $page = Request::query('page', 1);
        $perPage = Request::query('per_page', 10);

        // Build query
        $sql = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }

        if ($minPrice) {
            $sql .= " AND price >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice) {
            $sql .= " AND price <= ?";
            $params[] = $maxPrice;
        }

        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Sorting (whitelist!)
        $allowedSort = ['name', 'price', 'created_at', 'stock'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'created_at';
        }

        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';
        $sql .= " ORDER BY {$sort} {$order}";

        // Pagination
        $page = max(1, (int)$page);
        $perPage = min(100, max(1, (int)$perPage));
        $offset = ($page - 1) * $perPage;

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        // Execute
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        // Get total count
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $countSql = preg_replace('/ORDER BY.*/', '', $countSql);
        $countSql = preg_replace('/LIMIT.*/', '', $countSql);
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();

        Response::success([
            'data' => $products,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }

    public function store() {
        $data = Request::json();

        // Validate
        $validator = new Validator($data, [
            'name' => 'required|min:3|max:200',
            'description' => 'required',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'category' => 'required|in:electronics,clothing,books,home',
            'sku' => 'required|alphanumeric',
            'image_url' => 'url'
        ]);

        if (!$validator->validate()) {
            Response::validationError($validator->errors());
        }

        // Sanitize
        $cleanData = Sanitizer::array($data);

        // Create product
        $product = Product::create($cleanData);

        Response::success($product, 201);
    }

    public function update($id) {
        $product = Product::find($id);
        if (!$product) {
            Response::notFound('Product not found');
        }

        $data = Request::json();

        // Validate (all fields optional for PATCH)
        $rules = [];
        if (Request::has('name')) $rules['name'] = 'min:3|max:200';
        if (Request::has('price')) $rules['price'] = 'numeric';
        if (Request::has('stock')) $rules['stock'] = 'integer';
        if (Request::has('category')) $rules['category'] = 'in:electronics,clothing,books,home';

        if (!empty($rules)) {
            $validator = new Validator($data, $rules);
            if (!$validator->validate()) {
                Response::validationError($validator->errors());
            }
        }

        // Sanitize and update
        $cleanData = Sanitizer::array($data);
        $product->update($cleanData);

        Response::success($product);
    }
}
```

---

## Quick Quiz

**Question 1:** How do you read query parameters in PHP?
<details>
<summary>Answer</summary>
Using `$_GET['parameter_name']` or better: `Request::query('parameter_name', $default)`
</details>

**Question 2:** How do you read JSON from request body?
<details>
<summary>Answer</summary>
```php
$json = file_get_contents('php://input');
$data = json_decode($json, true);
```
</details>

**Question 3:** Why should you sanitize user input?
<details>
<summary>Answer</summary>
To prevent XSS attacks, SQL injection, and other security issues. Clean input before storing or displaying.
</details>

**Question 4:** What's the difference between validation and sanitization?
<details>
<summary>Answer</summary>
Validation checks if data is correct/acceptable. Sanitization cleans/modifies data to make it safe. Do both!
</details>

**Question 5:** Why whitelist sort fields instead of accepting any field?
<details>
<summary>Answer</summary>
Security - prevent SQL injection. Users could try to sort by fields that don't exist or inject malicious SQL.
</details>

---

## Summary

You learned:
- Reading query parameters from URLs
- Extracting path parameters via routing
- Parsing JSON request bodies
- Building a Request helper class
- Validation strategies and reusable Validator
- Sanitizing user input
- Handling complex filtering, sorting, and pagination
- Security best practices for input handling

---

## Next Lesson

**07-response-formatting.md** - Learn to format API responses properly with consistent structure, status codes, and error handling!
