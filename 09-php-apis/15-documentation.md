# Lesson 15 - API Documentation

**Duration**: 45-60 minutes

---

## Why Document Your API?

**Without documentation:**
- Developers can't use your API
- You forget how your own API works
- Support requests increase
- Integration takes forever

**With documentation:**
- Self-service integration
- Fewer support questions
- Faster adoption
- Professional image

**Good documentation = More users!**

---

## What to Document

### 1. Overview
- What does the API do?
- Base URL
- Authentication method
- Rate limits
- Supported versions

### 2. Authentication
- How to get API keys/tokens
- How to send authentication
- Example requests

### 3. Endpoints
- URL and HTTP method
- Description
- Path/query parameters
- Request body (with examples)
- Response format (with examples)
- Status codes
- Error responses

### 4. Examples
- Full request examples (curl, JavaScript, etc.)
- Full response examples
- Common use cases

### 5. Error Codes
- List of all error codes
- What each means
- How to fix

---

## Simple Markdown Documentation

Create `docs/API.md`:

```markdown
# Blog API Documentation

Base URL: `https://api.example.com`

Version: `v1`

## Authentication

All requests require an API key or Bearer token.

### API Key (Header)

```
X-API-Key: your-api-key-here
```

### Bearer Token (Header)

```
Authorization: Bearer your-token-here
```

## Endpoints

### Posts

#### List All Posts

**GET** `/v1/api/posts`

Get a paginated list of posts.

**Query Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| page | integer | No | Page number (default: 1) |
| per_page | integer | No | Items per page (default: 10, max: 100) |
| author | integer | No | Filter by author ID |
| category | string | No | Filter by category |
| search | string | No | Search in title and content |

**Example Request:**

```bash
curl -X GET "https://api.example.com/v1/api/posts?page=1&per_page=10" \
  -H "Authorization: Bearer your-token"
```

**Example Response (200 OK):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "My First Post",
      "content": "This is the content...",
      "author_id": 5,
      "category": "tech",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "pagination": {
      "total": 50,
      "per_page": 10,
      "current_page": 1,
      "total_pages": 5
    }
  }
}
```

#### Get Single Post

**GET** `/v1/api/posts/{id}`

Get a specific post by ID.

**Path Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| id | integer | Yes | Post ID |

**Example Request:**

```bash
curl -X GET "https://api.example.com/v1/api/posts/1" \
  -H "Authorization: Bearer your-token"
```

**Example Response (200 OK):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "My First Post",
    "content": "This is the content...",
    "author": {
      "id": 5,
      "name": "John Doe"
    },
    "comments_count": 10,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Error Response (404 Not Found):**

```json
{
  "success": false,
  "error": "Post not found",
  "code": "NOT_FOUND"
}
```

#### Create Post

**POST** `/v1/api/posts`

Create a new post.

**Authentication:** Required

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| title | string | Yes | Post title (min: 5, max: 200) |
| content | string | Yes | Post content (min: 100) |
| category | string | No | Post category |
| published | boolean | No | Publish immediately (default: false) |

**Example Request:**

```bash
curl -X POST "https://api.example.com/v1/api/posts" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My New Post",
    "content": "This is a long post content...",
    "category": "tech",
    "published": true
  }'
```

**Example Response (201 Created):**

```json
{
  "success": true,
  "data": {
    "id": 51,
    "title": "My New Post",
    "content": "This is a long post content...",
    "author_id": 5,
    "category": "tech",
    "published": true,
    "created_at": "2024-01-20T15:45:00Z"
  }
}
```

**Error Response (422 Validation Error):**

```json
{
  "success": false,
  "error": "Validation failed",
  "code": "VALIDATION_ERROR",
  "errors": {
    "title": "Title must be at least 5 characters",
    "content": "Content is required"
  }
}
```

## Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| BAD_REQUEST | 400 | Invalid request syntax |
| UNAUTHORIZED | 401 | Missing or invalid authentication |
| FORBIDDEN | 403 | Not allowed to access resource |
| NOT_FOUND | 404 | Resource doesn't exist |
| VALIDATION_ERROR | 422 | Request validation failed |
| TOO_MANY_REQUESTS | 429 | Rate limit exceeded |
| INTERNAL_ERROR | 500 | Server error |

## Rate Limits

- **Free tier:** 100 requests per hour
- **Premium tier:** 1000 requests per hour

Rate limit info is returned in response headers:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1610123456
```

## Examples

### JavaScript (Fetch)

```javascript
// Get posts
fetch('https://api.example.com/v1/api/posts', {
  headers: {
    'Authorization': 'Bearer your-token'
  }
})
.then(response => response.json())
.then(data => console.log(data));

// Create post
fetch('https://api.example.com/v1/api/posts', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer your-token',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    title: 'My Post',
    content: 'Post content here...'
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

### PHP

```php
// Get posts
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.example.com/v1/api/posts');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer your-token'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);
```

## Support

Questions? Contact: api-support@example.com
```

---

## Interactive Documentation with HTML

Create `docs/index.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog API Documentation</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 300px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: #2d3748;
            color: #fff;
            padding: 2rem;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .sidebar nav ul {
            list-style: none;
        }

        .sidebar nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar nav a {
            color: #cbd5e0;
            text-decoration: none;
            display: block;
            padding: 0.5rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .sidebar nav a:hover {
            background: #4a5568;
            color: #fff;
        }

        .content {
            padding: 2rem;
            background: #f7fafc;
        }

        .endpoint {
            background: #fff;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .method {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.875rem;
            margin-right: 1rem;
        }

        .method.get { background: #48bb78; color: white; }
        .method.post { background: #4299e1; color: white; }
        .method.put { background: #ed8936; color: white; }
        .method.delete { background: #f56565; color: white; }

        .endpoint-url {
            font-family: 'Courier New', monospace;
            font-size: 1.125rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        th, td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #edf2f7;
            font-weight: 600;
        }

        pre {
            background: #2d3748;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 4px;
            overflow-x: auto;
            margin: 1rem 0;
        }

        code {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }

        .try-it {
            background: #ebf8ff;
            border: 1px solid #4299e1;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .try-it button {
            background: #4299e1;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .try-it button:hover {
            background: #3182ce;
        }

        .response {
            margin-top: 1rem;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 4px;
            display: none;
        }

        .response.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h2>Blog API</h2>
            <nav>
                <ul>
                    <li><a href="#overview">Overview</a></li>
                    <li><a href="#auth">Authentication</a></li>
                    <li><a href="#posts">Posts</a>
                        <ul style="margin-left: 1rem;">
                            <li><a href="#posts-list">List Posts</a></li>
                            <li><a href="#posts-get">Get Post</a></li>
                            <li><a href="#posts-create">Create Post</a></li>
                        </ul>
                    </li>
                    <li><a href="#errors">Errors</a></li>
                    <li><a href="#rate-limits">Rate Limits</a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <section id="overview">
                <h1>Blog API Documentation</h1>
                <p>Base URL: <code>https://api.example.com</code></p>
                <p>Version: <code>v1</code></p>
            </section>

            <section id="auth" style="margin-top: 2rem;">
                <h2>Authentication</h2>
                <p>All requests require authentication using Bearer tokens:</p>
                <pre><code>Authorization: Bearer your-token-here</code></pre>
            </section>

            <section id="posts" style="margin-top: 2rem;">
                <h2>Posts</h2>

                <div class="endpoint" id="posts-list">
                    <div>
                        <span class="method get">GET</span>
                        <span class="endpoint-url">/v1/api/posts</span>
                    </div>

                    <h3 style="margin-top: 1rem;">Get all posts</h3>
                    <p>Returns a paginated list of posts.</p>

                    <h4>Query Parameters</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Parameter</th>
                                <th>Type</th>
                                <th>Required</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>page</td>
                                <td>integer</td>
                                <td>No</td>
                                <td>Page number (default: 1)</td>
                            </tr>
                            <tr>
                                <td>per_page</td>
                                <td>integer</td>
                                <td>No</td>
                                <td>Items per page (default: 10)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Example Response</h4>
                    <pre><code>{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "My First Post",
      "content": "Post content...",
      "created_at": "2024-01-15T10:30:00Z"
    }
  ]
}</code></pre>

                    <div class="try-it">
                        <h4>Try it out</h4>
                        <input type="text" id="token-list" placeholder="Your token" style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #cbd5e0; border-radius: 4px;">
                        <button onclick="tryListPosts()">Send Request</button>
                        <div id="response-list" class="response"></div>
                    </div>
                </div>

                <div class="endpoint" id="posts-create">
                    <div>
                        <span class="method post">POST</span>
                        <span class="endpoint-url">/v1/api/posts</span>
                    </div>

                    <h3 style="margin-top: 1rem;">Create a post</h3>

                    <h4>Request Body</h4>
                    <pre><code>{
  "title": "My Post",
  "content": "Post content..."
}</code></pre>

                    <div class="try-it">
                        <h4>Try it out</h4>
                        <input type="text" id="token-create" placeholder="Your token" style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #cbd5e0; border-radius: 4px;">
                        <textarea id="body-create" rows="5" style="width: 100%; padding: 0.5rem; margin-bottom: 0.5rem; border: 1px solid #cbd5e0; border-radius: 4px;">{"title": "Test Post", "content": "This is a test post content..."}</textarea>
                        <button onclick="tryCreatePost()">Send Request</button>
                        <div id="response-create" class="response"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        async function tryListPosts() {
            const token = document.getElementById('token-list').value;
            const responseDiv = document.getElementById('response-list');

            if (!token) {
                alert('Please enter your token');
                return;
            }

            responseDiv.textContent = 'Loading...';
            responseDiv.classList.add('show');

            try {
                const response = await fetch('https://api.example.com/v1/api/posts', {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                const data = await response.json();
                responseDiv.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                responseDiv.textContent = 'Error: ' + error.message;
            }
        }

        async function tryCreatePost() {
            const token = document.getElementById('token-create').value;
            const body = document.getElementById('body-create').value;
            const responseDiv = document.getElementById('response-create');

            if (!token) {
                alert('Please enter your token');
                return;
            }

            responseDiv.textContent = 'Loading...';
            responseDiv.classList.add('show');

            try {
                const response = await fetch('https://api.example.com/v1/api/posts', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: body
                });

                const data = await response.json();
                responseDiv.textContent = JSON.stringify(data, null, 2);
            } catch (error) {
                responseDiv.textContent = 'Error: ' + error.message;
            }
        }
    </script>
</body>
</html>
```

---

## Postman Collection

Export your API as Postman collection:

```json
{
  "info": {
    "name": "Blog API",
    "description": "API for blog application",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Posts",
      "item": [
        {
          "name": "List Posts",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}",
                "type": "text"
              }
            ],
            "url": {
              "raw": "{{base_url}}/v1/api/posts?page=1&per_page=10",
              "host": ["{{base_url}}"],
              "path": ["v1", "api", "posts"],
              "query": [
                {"key": "page", "value": "1"},
                {"key": "per_page", "value": "10"}
              ]
            }
          }
        },
        {
          "name": "Get Post",
          "request": {
            "method": "GET",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}",
                "type": "text"
              }
            ],
            "url": {
              "raw": "{{base_url}}/v1/api/posts/:id",
              "host": ["{{base_url}}"],
              "path": ["v1", "api", "posts", ":id"],
              "variable": [
                {"key": "id", "value": "1"}
              ]
            }
          }
        },
        {
          "name": "Create Post",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Authorization",
                "value": "Bearer {{token}}",
                "type": "text"
              },
              {
                "key": "Content-Type",
                "value": "application/json",
                "type": "text"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"title\": \"My Post\",\n  \"content\": \"Post content...\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/v1/api/posts",
              "host": ["{{base_url}}"],
              "path": ["v1", "api", "posts"]
            }
          }
        }
      ]
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "https://api.example.com"
    },
    {
      "key": "token",
      "value": "your-token-here"
    }
  ]
}
```

Import this file in Postman to test your API!

---

## OpenAPI/Swagger (Industry Standard)

OpenAPI is the standard format for API documentation:

```yaml
# docs/openapi.yaml

openapi: 3.0.0
info:
  title: Blog API
  description: API for blog application
  version: 1.0.0
  contact:
    email: api-support@example.com

servers:
  - url: https://api.example.com/v1
    description: Production server
  - url: http://localhost:8000/v1
    description: Development server

security:
  - bearerAuth: []

paths:
  /api/posts:
    get:
      summary: List all posts
      description: Get a paginated list of posts
      tags:
        - Posts
      parameters:
        - name: page
          in: query
          description: Page number
          required: false
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          description: Items per page
          required: false
          schema:
            type: integer
            default: 10
            maximum: 100
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    type: array
                    items:
                      $ref: '#/components/schemas/Post'

    post:
      summary: Create a post
      description: Create a new blog post
      tags:
        - Posts
      requestBody:
        required: true
        content:
          application/json:
            schema:
              type: object
              required:
                - title
                - content
              properties:
                title:
                  type: string
                  minLength: 5
                  maxLength: 200
                content:
                  type: string
                  minLength: 100
      responses:
        '201':
          description: Post created
          content:
            application/json:
              schema:
                type: object
                properties:
                  success:
                    type: boolean
                  data:
                    $ref: '#/components/schemas/Post'
        '422':
          description: Validation error
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/ValidationError'

components:
  securitySchemes:
    bearerAuth:
      type: http
      scheme: bearer

  schemas:
    Post:
      type: object
      properties:
        id:
          type: integer
        title:
          type: string
        content:
          type: string
        author_id:
          type: integer
        created_at:
          type: string
          format: date-time

    ValidationError:
      type: object
      properties:
        success:
          type: boolean
        error:
          type: string
        code:
          type: string
        errors:
          type: object
```

**Use Swagger UI to view:**
1. Go to [editor.swagger.io](https://editor.swagger.io)
2. Paste your OpenAPI YAML
3. See beautiful interactive documentation!

---

## Documentation Best Practices

### 1. Keep It Up to Date

Update docs when you change API:

```php
// ❌ Bad - API changed but docs didn't
// Docs say: POST /api/users with "name" field
// API requires: "first_name" and "last_name"

// ✅ Good - docs match reality
```

### 2. Provide Examples

Always include:
- Example requests (curl, JavaScript, PHP)
- Example responses (success and errors)
- Common use cases

### 3. Document Errors

List all possible errors:
```markdown
**Possible Errors:**
- 401: Invalid token
- 404: Post not found
- 422: Validation failed (title too short)
```

### 4. Show Status Codes

Every endpoint should show:
- 200/201/204 for success
- 400/401/403/404/422 for errors
- 500 for server errors

### 5. Include Rate Limits

Tell users about limits:
```markdown
**Rate Limit:** 100 requests per hour

Check headers for limit info:
- X-RateLimit-Limit: 100
- X-RateLimit-Remaining: 95
```

---

## Tools for Documentation

### 1. Postman
- Create collections
- Export as documentation
- Share with team

### 2. Swagger UI
- Interactive documentation
- Try API in browser
- Auto-generated from OpenAPI

### 3. Slate
- Beautiful static docs
- Markdown-based
- GitHub Pages deployment

### 4. API Blueprint
- Simple markdown format
- Generates docs and mocks
- Good for design-first

### 5. ReadMe.io
- Hosted documentation
- Analytics
- Support portal

---

## Laravel API Documentation (Preview)

Laravel has packages for auto-generating documentation:

```php
// Install Scribe
composer require knuckleswtf/scribe

// Generate docs
php artisan scribe:generate
```

Scribe reads your code and generates beautiful documentation automatically!

---

## Quick Quiz

**Question 1:** What's the most important thing to document?
<details>
<summary>Answer</summary>
Everything! But especially: authentication, request/response examples, error codes, and rate limits.
</details>

**Question 2:** Should you update docs after changing API?
<details>
<summary>Answer</summary>
YES! Outdated docs are worse than no docs. Keep them in sync with code.
</details>

**Question 3:** What format is standard for API documentation?
<details>
<summary>Answer</summary>
OpenAPI/Swagger - industry standard, supported by many tools.
</details>

**Question 4:** Why include example requests?
<details>
<summary>Answer</summary>
Developers can copy-paste and get started immediately. Examples are the fastest way to learn an API.
</details>

**Question 5:** What should every endpoint documentation include?
<details>
<summary>Answer</summary>
URL, method, parameters, request body, response format, status codes, example request/response, possible errors.
</details>

---

## Summary

You learned:
- Why API documentation is critical
- What to include in documentation
- Creating Markdown documentation
- Building interactive HTML docs
- Postman collections for testing
- OpenAPI/Swagger standard format
- Documentation best practices
- Tools for generating docs
- Keeping docs up to date

---

## Module Complete!

Congratulations! You've completed Module 09 - APIs in Pure PHP!

You now know:
1. REST principles and API design
2. HTTP methods (GET, POST, PUT, PATCH, DELETE)
3. Working with JSON in PHP
4. Building complete APIs from scratch
5. Routing systems without frameworks
6. Request handling and validation
7. Response formatting and status codes
8. Authentication (API keys, tokens, JWT)
9. Pagination for large datasets
10. CORS for cross-origin requests
11. Consuming external APIs with cURL
12. Error handling and logging
13. API versioning strategies
14. Documentation best practices

**Next:** Module 10 - Put all your PHP knowledge together in a large e-commerce project!

---

## Further Learning

### Books
- "RESTful Web APIs" by Leonard Richardson
- "API Design Patterns" by JJ Geewax

### Online Resources
- [REST API Tutorial](https://restfulapi.net/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [HTTP Status Codes](https://httpstatuses.com/)
- [JWT.io](https://jwt.io/)

### Real APIs to Study
- [Stripe API](https://stripe.com/docs/api) - Excellent documentation
- [GitHub API](https://docs.github.com/en/rest) - RESTful design
- [Twitter API](https://developer.twitter.com/en/docs) - Good examples

Practice by building your own APIs and integrating with external services!
