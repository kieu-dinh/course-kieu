# Lesson 01 - What are APIs and REST?

**Duration**: 45-60 minutes

---

## What is an API?

**API** stands for **Application Programming Interface**.

Think of it as a **waiter in a restaurant**:
- You (the customer) want food
- The kitchen has the food
- But you can't go into the kitchen directly
- The waiter is the **interface** between you and the kitchen

In software:
- **Frontend** (website, mobile app) wants data
- **Backend** (database, business logic) has the data
- **API** is the interface between them

### Real-World Example

When you use a weather app on your phone:
1. App sends request: "What's the weather in Paris?"
2. API receives request, checks database/services
3. API responds: "20°C, sunny"
4. App displays the information

```
[Mobile App] --request--> [Weather API] --query--> [Database]
[Mobile App] <--response- [Weather API] <--data--- [Database]
```

---

## Why Do We Need APIs?

### 1. Separation of Frontend and Backend

**Traditional Website** (everything together):
```php
<?php
// Mixed HTML and PHP
$posts = getPosts();
?>
<html>
  <body>
    <?php foreach ($posts as $post): ?>
      <h2><?= $post['title'] ?></h2>
    <?php endforeach; ?>
  </body>
</html>
```

**Modern Approach** (API + Frontend):
```php
// Backend: API returns only data
// GET /api/posts
header('Content-Type: application/json');
echo json_encode(['data' => $posts]);
```

```javascript
// Frontend: JavaScript fetches and displays
fetch('/api/posts')
  .then(response => response.json())
  .then(data => {
    data.forEach(post => {
      // Display post in HTML
    });
  });
```

**Benefits:**
- Frontend developers work independently
- Backend developers work independently
- Can change frontend without touching backend
- Can build multiple frontends (web, iOS, Android) using same API

### 2. Mobile Apps

Your website might work on desktop, but mobile apps need APIs:

```
[Website] ----\
[iOS App] ------> [Your API] --> [Database]
[Android App] -/
```

Same API serves all platforms!

### 3. Third-Party Integration

Other developers can use your service:

```javascript
// Someone's app using your API
fetch('https://yoursite.com/api/products')
  .then(response => response.json())
  .then(products => {
    // Use your products in their app
  });
```

**Examples:**
- Stripe API - Accept payments
- SendGrid API - Send emails
- Google Maps API - Show maps
- Twitter API - Post tweets

### 4. Microservices

Big companies split applications into small services:

```
[User Service API] --> [User Database]
[Product Service API] --> [Product Database]
[Order Service API] --> [Order Database]
```

Each service has its own API and database.

---

## What is REST?

**REST** = **Representational State Transfer**

It's not a technology or library. It's an **architectural style** - a set of rules for designing APIs.

Created by Roy Fielding in 2000 in his PhD dissertation.

### The Core Idea

Everything is a **resource** that you can interact with using standard **HTTP methods**.

**Resource**: A "thing" in your application
- User
- Post
- Product
- Order
- Comment

**HTTP Methods**: Actions you perform on resources
- GET - Read
- POST - Create
- PUT/PATCH - Update
- DELETE - Delete

---

## REST Principles

### 1. Resource-Based URLs

URLs represent **resources** (nouns), not **actions** (verbs).

**Good (RESTful):**
```
GET    /api/users           → Get all users
GET    /api/users/5         → Get user with ID 5
POST   /api/users           → Create new user
PUT    /api/users/5         → Update user 5
DELETE /api/users/5         → Delete user 5
```

**Bad (Not RESTful):**
```
GET    /api/getUsers
GET    /api/getUserById?id=5
POST   /api/createUser
POST   /api/updateUser
POST   /api/deleteUser
```

Why bad? URL names are verbs, everything uses GET/POST.

### 2. HTTP Methods Have Meaning

Each HTTP method has a specific purpose:

| Method | Purpose | Safe? | Idempotent? |
|--------|---------|-------|-------------|
| GET | Retrieve data | Yes | Yes |
| POST | Create new resource | No | No |
| PUT | Update entire resource | No | Yes |
| PATCH | Update part of resource | No | Yes |
| DELETE | Delete resource | No | Yes |

**Safe**: Doesn't modify data (read-only)
**Idempotent**: Multiple identical requests have same effect as single request

**Example: Idempotent**
```
DELETE /api/users/5   → User 5 deleted
DELETE /api/users/5   → User 5 already deleted (same result)
DELETE /api/users/5   → Still deleted (no change)
```

**Example: Not Idempotent**
```
POST /api/orders   → Creates order #101
POST /api/orders   → Creates order #102 (different result!)
POST /api/orders   → Creates order #103 (creates new resource each time)
```

### 3. Stateless

Each request is **independent** and contains **all information needed**.

**Stateless (Good):**
```
GET /api/users/5
Authorization: Bearer abc123xyz

Response: User data
```

Every request includes authentication token. Server doesn't remember previous requests.

**Stateful (Bad):**
```
POST /api/login
  → Server stores: "User X is logged in"

GET /api/users/5
  → Server checks: "Is user logged in?"
```

Server remembers who's logged in using sessions. Not RESTful.

**Why Stateless is Better:**
- Easier to scale (any server can handle any request)
- No session management needed
- Simpler architecture
- Works better with load balancers

### 4. JSON Format

REST APIs typically use **JSON** (JavaScript Object Notation) for data exchange.

**JSON Example:**
```json
{
  "id": 5,
  "name": "John Doe",
  "email": "john@example.com",
  "active": true,
  "roles": ["admin", "editor"]
}
```

**Why JSON?**
- Human-readable
- Lightweight
- Supported by all programming languages
- Native to JavaScript (web apps)

**Alternative**: XML (older, more verbose)
```xml
<user>
  <id>5</id>
  <name>John Doe</name>
  <email>john@example.com</email>
</user>
```

JSON is now the standard.

### 5. Use HTTP Status Codes

Status codes tell the client what happened:

**Success Codes (2xx):**
```
200 OK          → Request succeeded
201 Created     → New resource created
204 No Content  → Success but no data returned
```

**Client Error Codes (4xx):**
```
400 Bad Request   → Invalid data sent
401 Unauthorized  → Missing authentication
403 Forbidden     → Not allowed to access
404 Not Found     → Resource doesn't exist
422 Unprocessable → Validation failed
```

**Server Error Codes (5xx):**
```
500 Internal Server Error → Bug in your code
502 Bad Gateway          → Upstream service failed
503 Service Unavailable  → Server overloaded
```

**Example:**
```php
// User not found
http_response_code(404);
echo json_encode(['error' => 'User not found']);
```

---

## RESTful API Example

Let's design a blog API following REST principles:

### Resources

We have three resources:
1. Posts
2. Comments
3. Users

### Endpoints

**Posts:**
```
GET    /api/posts              → List all posts
GET    /api/posts/10           → Get post #10
POST   /api/posts              → Create new post
PUT    /api/posts/10           → Update post #10 (full update)
PATCH  /api/posts/10           → Update post #10 (partial update)
DELETE /api/posts/10           → Delete post #10
```

**Comments:**
```
GET    /api/posts/10/comments  → Get all comments for post #10
POST   /api/posts/10/comments  → Create comment on post #10
DELETE /api/comments/55         → Delete comment #55
```

**Users:**
```
GET    /api/users              → List all users
GET    /api/users/5            → Get user #5
GET    /api/users/5/posts      → Get all posts by user #5
```

### Request/Response Examples

**1. Get All Posts**
```
GET /api/posts
Authorization: Bearer abc123

Response (200 OK):
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "First Post",
      "content": "Hello world",
      "author_id": 5,
      "created_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 2,
      "title": "Second Post",
      "content": "More content",
      "author_id": 5,
      "created_at": "2024-01-16T14:20:00Z"
    }
  ],
  "meta": {
    "total": 2,
    "page": 1,
    "per_page": 10
  }
}
```

**2. Create New Post**
```
POST /api/posts
Content-Type: application/json
Authorization: Bearer abc123

Body:
{
  "title": "My New Post",
  "content": "This is the content"
}

Response (201 Created):
{
  "success": true,
  "data": {
    "id": 3,
    "title": "My New Post",
    "content": "This is the content",
    "author_id": 5,
    "created_at": "2024-01-17T09:15:00Z"
  }
}
```

**3. Update Post**
```
PUT /api/posts/3
Content-Type: application/json
Authorization: Bearer abc123

Body:
{
  "title": "Updated Title",
  "content": "Updated content"
}

Response (200 OK):
{
  "success": true,
  "data": {
    "id": 3,
    "title": "Updated Title",
    "content": "Updated content",
    "author_id": 5,
    "updated_at": "2024-01-17T10:00:00Z"
  }
}
```

**4. Delete Post**
```
DELETE /api/posts/3
Authorization: Bearer abc123

Response (204 No Content):
(No response body)
```

**5. Validation Error**
```
POST /api/posts
Content-Type: application/json
Authorization: Bearer abc123

Body:
{
  "title": "",
  "content": "Content without title"
}

Response (422 Unprocessable Entity):
{
  "success": false,
  "errors": {
    "title": ["The title field is required"]
  }
}
```

---

## REST vs Other API Styles

### REST vs SOAP

**SOAP** (older technology):
- Uses XML only
- Very strict rules
- More complex
- Heavier (more bandwidth)
- Used in enterprise/banking

**REST**:
- Uses JSON (usually)
- Flexible
- Simpler
- Lighter
- Used everywhere now

### REST vs GraphQL

**GraphQL** (newer technology):
- Client specifies exactly what data it wants
- Single endpoint for everything
- More complex to implement
- Better for complex data requirements

**REST**:
- Server decides what data to return
- Multiple endpoints (one per resource)
- Simpler to implement
- Good for most use cases

**Example GraphQL:**
```graphql
{
  user(id: 5) {
    name
    email
    posts {
      title
      comments {
        content
      }
    }
  }
}
```

You get exactly what you ask for in one request.

**Example REST:**
```
GET /api/users/5
GET /api/users/5/posts
GET /api/posts/1/comments
```

Multiple requests, get all data from each endpoint.

**When to use what:**
- **REST**: 90% of projects, especially when learning
- **GraphQL**: Complex apps with lots of relationships (Facebook, GitHub)
- **SOAP**: Enterprise/legacy systems

---

## Benefits of RESTful APIs

### 1. Scalability
Stateless nature makes it easy to scale:
```
[Load Balancer]
     ├─> [API Server 1]
     ├─> [API Server 2]
     └─> [API Server 3]
```

Any server can handle any request.

### 2. Flexibility
Same API serves multiple clients:
```
[API] --> [Web App]
      --> [iOS App]
      --> [Android App]
      --> [Third-party developers]
```

### 3. Maintainability
Separation of concerns:
- Frontend team works on UI
- Backend team works on API
- Changes don't affect each other

### 4. Testability
Easy to test with tools like Postman or curl:
```bash
curl -X GET http://localhost/api/posts
```

### 5. Documentation
Tools can auto-generate documentation (OpenAPI/Swagger).

---

## Common Misconceptions

### "REST means JSON"
Not quite. REST can use XML, JSON, or other formats. But JSON is standard today.

### "Any API that returns JSON is RESTful"
No! Must follow REST principles:
- Resource-based URLs
- Proper HTTP methods
- Stateless
- HTTP status codes

### "PUT and PATCH are the same"
No:
- **PUT**: Replace entire resource
- **PATCH**: Update specific fields

Example:
```
User: { id: 5, name: "John", email: "john@example.com", age: 30 }

PUT /api/users/5
{ "name": "John Doe" }
→ Result: { id: 5, name: "John Doe", email: null, age: null }
  (Other fields cleared!)

PATCH /api/users/5
{ "name": "John Doe" }
→ Result: { id: 5, name: "John Doe", email: "john@example.com", age: 30 }
  (Only name updated)
```

### "REST is a protocol"
No, it's an architectural style. HTTP is the protocol.

---

## Best Practices for REST APIs

### 1. Use Nouns, Not Verbs
```
✅ GET /api/products
❌ GET /api/getProducts

✅ DELETE /api/products/5
❌ POST /api/deleteProduct/5
```

### 2. Use Plural Nouns
```
✅ /api/products
❌ /api/product

✅ /api/users/5/orders
❌ /api/user/5/order
```

### 3. Use Hierarchies for Relationships
```
GET /api/users/5/posts          → Posts by user 5
GET /api/posts/10/comments      → Comments on post 10
GET /api/categories/3/products  → Products in category 3
```

### 4. Use Query Parameters for Filtering
```
GET /api/products?category=electronics
GET /api/products?min_price=100&max_price=500
GET /api/posts?author=5&published=true
```

### 5. Provide Pagination
```
GET /api/products?page=2&per_page=20
```

### 6. Version Your API
```
GET /api/v1/products
GET /api/v2/products
```

More on this in Lesson 14!

---

## Your First API Request

Let's make a real API request to see how it works.

### Using Browser

Open this URL in your browser:
```
https://jsonplaceholder.typicode.com/posts/1
```

You'll see JSON data:
```json
{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere...",
  "body": "quia et suscipit..."
}
```

This is a fake REST API for testing!

### Using curl

Open your terminal:
```bash
curl https://jsonplaceholder.typicode.com/posts/1
```

Same JSON data appears.

### Try Different Endpoints

**Get all posts:**
```bash
curl https://jsonplaceholder.typicode.com/posts
```

**Get user:**
```bash
curl https://jsonplaceholder.typicode.com/users/1
```

**Get comments on a post:**
```bash
curl https://jsonplaceholder.typicode.com/posts/1/comments
```

See how the URL structure follows REST principles?

---

## Quick Quiz

**Question 1:** What does API stand for?
<details>
<summary>Answer</summary>
Application Programming Interface - an interface that allows different software to communicate.
</details>

**Question 2:** Is this URL RESTful? `/api/createUser`
<details>
<summary>Answer</summary>
No. It uses a verb in the URL. Should be `POST /api/users` instead.
</details>

**Question 3:** Which HTTP method should you use to update a resource?
<details>
<summary>Answer</summary>
PUT (for full update) or PATCH (for partial update).
</details>

**Question 4:** What status code should you return when a resource is created?
<details>
<summary>Answer</summary>
201 Created
</details>

**Question 5:** What makes REST stateless?
<details>
<summary>Answer</summary>
Each request contains all information needed. Server doesn't remember previous requests or maintain sessions.
</details>

---

## Summary

You learned:
- **API** = Interface between software applications
- **REST** = Architectural style using resources and HTTP methods
- REST principles: resource-based URLs, HTTP methods, stateless, JSON, status codes
- **Resources** are nouns (users, posts, products)
- **HTTP methods** are verbs (GET, POST, PUT, DELETE)
- RESTful APIs are scalable, flexible, and maintainable
- JSON is the standard format
- Status codes communicate success/failure

---

## Next Lesson

**02-http-methods.md** - Deep dive into GET, POST, PUT, PATCH, DELETE and when to use each one!

---

## Resources

- [REST API Tutorial](https://restfulapi.net/)
- [Roy Fielding's REST Dissertation](https://www.ics.uci.edu/~fielding/pubs/dissertation/rest_arch_style.htm) (original source)
- [JSONPlaceholder](https://jsonplaceholder.typicode.com/) - Free fake API for testing
- [HTTP Status Codes](https://httpstatuses.com/)
