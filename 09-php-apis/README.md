# Module 09 - APIs in Pure PHP

**Duration**: 2-3 weeks
**Prerequisites**: Module 08 - Security & Validation

---

## Learning Objectives

By the end of this module, you will be able to:
- Understand REST principles and API design
- Build RESTful APIs in pure PHP
- Handle JSON requests and responses
- Implement API authentication (API keys, tokens)
- Version your APIs properly
- Handle CORS (Cross-Origin Resource Sharing)
- Document your APIs
- Consume external APIs
- Understand Laravel API features later

---

## Why Learn APIs?

Modern applications are often split into:
- **Frontend** (React, Vue, mobile app) - User interface
- **Backend API** (PHP) - Data and business logic

**APIs allow:**
- Mobile apps to use your backend
- Other developers to integrate with your service
- Frontend and backend separation
- Third-party integrations (payment, email, etc.)

---

## What is REST?

**REST** (Representational State Transfer) is an architectural style for APIs.

### REST Principles:
1. **Resources** - Everything is a resource (user, post, product)
2. **HTTP Methods** - Use verbs correctly
   - GET - Retrieve data
   - POST - Create new resource
   - PUT/PATCH - Update resource
   - DELETE - Delete resource
3. **Stateless** - Each request is independent
4. **JSON** - Standard format for data exchange

### Example:
```
GET    /api/posts          → Get all posts
GET    /api/posts/123      → Get post with ID 123
POST   /api/posts          → Create new post
PUT    /api/posts/123      → Update post 123
DELETE /api/posts/123      → Delete post 123
```

---

## What You'll Learn

### 1. REST Fundamentals
- What is an API?
- REST principles
- Resource naming
- HTTP methods (GET, POST, PUT, DELETE)
- HTTP status codes (200, 201, 404, 500, etc.)

### 2. Building APIs in PHP
- Routing requests
- Parsing JSON input
- Sending JSON responses
- Proper status codes
- Error handling

### 3. API Authentication
- API Keys
- Bearer Tokens
- JWT (JSON Web Tokens) basics
- Securing endpoints
- Rate limiting per API key

### 4. Advanced API Concepts
- Pagination
- Filtering and sorting
- Versioning (v1, v2)
- CORS handling
- Content negotiation

### 5. Consuming External APIs
- Using cURL in PHP
- Handling API responses
- Error handling
- API wrappers/SDKs
- Working with popular APIs (payment, email, etc.)

### 6. API Documentation
- Why documentation matters
- OpenAPI/Swagger basics
- Postman collections
- Examples and use cases

---

## Lessons

1. **01-api-intro.md** - What are APIs and REST?
2. **02-http-methods.md** - GET, POST, PUT, DELETE explained
3. **03-json-basics.md** - Working with JSON in PHP
4. **04-first-api.md** - Build your first API endpoint
5. **05-routing.md** - API routing without frameworks
6. **06-request-handling.md** - Parse JSON requests
7. **07-response-formatting.md** - JSON responses and status codes
8. **08-authentication.md** - API keys and tokens
9. **09-jwt-basics.md** - JSON Web Tokens
10. **10-pagination.md** - Handling large datasets
11. **11-cors.md** - Cross-Origin Resource Sharing
12. **12-consuming-apis.md** - Using external APIs with cURL
13. **13-error-handling.md** - Proper API errors
14. **14-versioning.md** - API versioning strategies
15. **15-documentation.md** - Documenting your API

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 9.1 | Simple JSON API | Return users as JSON | 1-2 hours |
| 9.2 | CRUD API | Full CRUD for posts resource | 3-4 hours |
| 9.3 | API Authentication | Implement API key authentication | 3 hours |
| 9.4 | JWT Authentication | Token-based auth with JWT | 4 hours |
| 9.5 | Consume External API | Fetch data from public API | 2 hours |
| 9.6 | Complete API | Blog API with all features | 6-8 hours |

---

## API Structure Example

```
api/
├── config/
│   ├── database.php
│   └── cors.php
├── controllers/
│   ├── PostController.php
│   ├── UserController.php
│   └── AuthController.php
├── middleware/
│   ├── AuthMiddleware.php
│   └── CorsMiddleware.php
├── models/
│   ├── Post.php
│   └── User.php
├── routes/
│   └── api.php
├── public/
│   └── index.php          (Entry point)
└── helpers/
    ├── Response.php        (JSON response helper)
    └── Request.php         (JSON request parser)
```

---

## Code Examples

### Simple GET Endpoint
```php
// GET /api/posts
header('Content-Type: application/json');

$posts = Post::all(); // From your OOP knowledge

echo json_encode([
    'success' => true,
    'data' => $posts
]);
```

### POST Endpoint with Validation
```php
// POST /api/posts
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['title'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Title is required']);
    exit;
}

$post = Post::create($input);

http_response_code(201);
echo json_encode([
    'success' => true,
    'data' => $post
]);
```

### API Key Authentication
```php
function requireApiKey() {
    $headers = getallheaders();
    $apiKey = $headers['X-API-Key'] ?? null;

    if (!isValidApiKey($apiKey)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid API key']);
        exit;
    }
}
```

### Consuming External API
```php
$ch = curl_init('https://api.example.com/data');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_TOKEN',
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);

if (curl_errno($ch)) {
    // Handle error
}

curl_close($ch);
```

---

## HTTP Status Codes You'll Use

| Code | Meaning | When to Use |
|------|---------|-------------|
| 200 | OK | Successful GET, PUT, PATCH, DELETE |
| 201 | Created | Successful POST (resource created) |
| 204 | No Content | Successful DELETE (no body returned) |
| 400 | Bad Request | Invalid input from client |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | User doesn't have permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Unprocessable | Validation errors |
| 500 | Internal Error | Server error (fix your code!) |

---

## Project: Blog API

Build a complete REST API for a blog:

**Features:**
- User registration and login (JWT tokens)
- CRUD for posts
- CRUD for comments
- Pagination (10 posts per page)
- Filtering (by author, category)
- Sorting (by date, likes)
- API key authentication for admin endpoints
- Rate limiting (100 requests per hour)
- Proper error responses
- CORS headers
- API documentation

**Endpoints:**
```
POST   /api/auth/register
POST   /api/auth/login
GET    /api/posts?page=1&per_page=10&author=5
GET    /api/posts/{id}
POST   /api/posts
PUT    /api/posts/{id}
DELETE /api/posts/{id}
GET    /api/posts/{id}/comments
POST   /api/posts/{id}/comments
```

---

## Testing Your API

Tools you'll use:
- **Postman** - API testing and documentation
- **curl** - Command-line testing
- **Browser DevTools** - Inspect requests/responses

Example curl command:
```bash
curl -X POST http://localhost:8000/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"title":"My Post","content":"Hello World"}'
```

---

## Comparison: Now vs Laravel Later

**What you build now (Pure PHP):**
```php
// Routing
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/\/api\/posts\/(\d+)/', $uri, $matches)) {
    $id = $matches[1];
    // Handle GET /api/posts/{id}
}

// JSON Response
header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['data' => $posts]);
```

**What Laravel does (Module 18):**
```php
// Routing
Route::get('/api/posts/{id}', [PostController::class, 'show']);

// JSON Response
return response()->json(['data' => $posts]);
```

**But now you understand routing, HTTP methods, and JSON handling!**

---

## Resources

- [REST API Tutorial](https://restfulapi.net/)
- [HTTP Status Codes](https://httpstatuses.com/)
- [JWT.io](https://jwt.io/) - JWT documentation
- [Postman Learning](https://learning.postman.com/)
- [PHP cURL Documentation](https://www.php.net/manual/en/book.curl.php)

---

## Next Module

**Module 10 - E-Commerce Project**: Put ALL your PHP knowledge together in a large, real-world project!
