# Lesson 03 - GET and POST Requests

**Duration**: 45-60 minutes
**Prerequisites**: Lesson 02 - Fetch API Basics

---

## HTTP Methods Refresher

Remember from Module 09 - APIs? HTTP methods define the **action** you want to perform:

| Method | Purpose | Has Body? | Idempotent? |
|--------|---------|-----------|-------------|
| **GET** | Retrieve data | No | Yes |
| **POST** | Create new resource | Yes | No |
| **PUT** | Update entire resource | Yes | Yes |
| **PATCH** | Update part of resource | Yes | Yes |
| **DELETE** | Delete resource | No | Yes |

**Idempotent** means calling it multiple times has the same effect as calling it once.

In this lesson, we'll focus on **GET** and **POST** - the most common methods.

---

## GET Requests: Fetching Data

GET is used to **retrieve** data from the server. It's the default method for `fetch()`.

### Basic GET Request

```javascript
// Simplest form (GET is default)
const response = await fetch('/api/posts');
const posts = await response.json();
```

### Explicit GET

```javascript
// Explicitly specify GET
const response = await fetch('/api/posts', {
    method: 'GET'
});
const posts = await response.json();
```

### GET with Query Parameters

Query parameters are part of the URL:

```javascript
// Manual URL construction
const response = await fetch('/api/posts?page=1&per_page=10');

// Using URLSearchParams (cleaner)
const params = new URLSearchParams({
    page: 1,
    per_page: 10,
    author: 'Alice'
});

const response = await fetch(`/api/posts?${params}`);
// Requests: /api/posts?page=1&per_page=10&author=Alice
```

---

## POST Requests: Sending Data

POST is used to **send data** to the server (create resources, submit forms, etc.).

### Basic POST with JSON

```javascript
const response = await fetch('/api/posts', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        title: 'My First Post',
        content: 'Hello, World!',
        author_id: 1
    })
});

const result = await response.json();
console.log('Created post:', result);
```

**Key parts:**
1. **method: 'POST'** - Use POST method
2. **Content-Type header** - Tell server we're sending JSON
3. **body** - The data (must be a string, use `JSON.stringify()`)

---

## Complete Examples

### Example 1: Load Blog Posts (GET)

**PHP API (api/posts.php):**
```php
<?php
header('Content-Type: application/json');

// Get query parameters
$page = $_GET['page'] ?? 1;
$perPage = $_GET['per_page'] ?? 10;

// In real app, fetch from database
$posts = [
    ['id' => 1, 'title' => 'First Post', 'excerpt' => 'Lorem ipsum...'],
    ['id' => 2, 'title' => 'Second Post', 'excerpt' => 'Dolor sit...'],
    ['id' => 3, 'title' => 'Third Post', 'excerpt' => 'Amet consectetur...']
];

echo json_encode([
    'success' => true,
    'data' => $posts,
    'page' => $page,
    'total' => count($posts)
]);
```

**JavaScript:**
```javascript
async function loadPosts(page = 1) {
    try {
        const response = await fetch(`/api/posts.php?page=${page}&per_page=10`);

        if (!response.ok) {
            throw new Error('Failed to load posts');
        }

        const result = await response.json();

        if (result.success) {
            displayPosts(result.data);
        }

    } catch (error) {
        console.error('Error:', error);
        showError('Failed to load posts');
    }
}

function displayPosts(posts) {
    const container = document.getElementById('posts');

    container.innerHTML = posts.map(post => `
        <article>
            <h2>${post.title}</h2>
            <p>${post.excerpt}</p>
        </article>
    `).join('');
}

// Load posts on page load
loadPosts();
```

---

### Example 2: Create New Post (POST)

**PHP API (api/posts.php):**
```php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate
    if (empty($input['title'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Title is required'
        ]);
        exit;
    }

    // In real app, save to database
    $newPost = [
        'id' => 123, // Would be auto-generated
        'title' => $input['title'],
        'content' => $input['content'],
        'created_at' => date('Y-m-d H:i:s')
    ];

    http_response_code(201); // Created
    echo json_encode([
        'success' => true,
        'data' => $newPost,
        'message' => 'Post created successfully'
    ]);
}
```

**HTML:**
```html
<form id="postForm">
    <input type="text" id="title" placeholder="Title" required>
    <textarea id="content" placeholder="Content" required></textarea>
    <button type="submit">Create Post</button>
</form>
<div id="message"></div>
```

**JavaScript:**
```javascript
const form = document.getElementById('postForm');
const messageDiv = document.getElementById('message');

form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent form submission

    // Get form data
    const title = document.getElementById('title').value;
    const content = document.getElementById('content').value;

    // Show loading
    messageDiv.textContent = 'Creating post...';

    try {
        const response = await fetch('/api/posts.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                title: title,
                content: content
            })
        });

        const result = await response.json();

        if (response.ok && result.success) {
            messageDiv.textContent = 'Post created successfully!';
            form.reset(); // Clear form
        } else {
            messageDiv.textContent = `Error: ${result.error}`;
        }

    } catch (error) {
        messageDiv.textContent = 'Failed to create post';
        console.error('Error:', error);
    }
});
```

---

## Understanding Request Bodies

### JSON Body (Most Common)

**When to use:** Sending structured data (objects, arrays)

```javascript
const response = await fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'Alice',
        email: 'alice@example.com',
        age: 25
    })
});
```

**PHP receives:**
```php
$input = json_decode(file_get_contents('php://input'), true);
// $input = ['name' => 'Alice', 'email' => 'alice@example.com', 'age' => 25]
```

### FormData Body (File Uploads)

**When to use:** Uploading files or multipart/form-data

```javascript
const formData = new FormData();
formData.append('name', 'Alice');
formData.append('avatar', fileInput.files[0]);

const response = await fetch('/api/upload', {
    method: 'POST',
    body: formData // Don't set Content-Type header!
});
```

**PHP receives:**
```php
$name = $_POST['name'];
$file = $_FILES['avatar'];
```

### URL-Encoded Body (Traditional Forms)

**When to use:** Simulating traditional form submission

```javascript
const params = new URLSearchParams();
params.append('name', 'Alice');
params.append('email', 'alice@example.com');

const response = await fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: params
});
```

**PHP receives:**
```php
$name = $_POST['name'];
$email = $_POST['email'];
```

---

## PUT and PATCH Requests

Similar to POST, but for **updating** existing resources.

### PUT - Replace Entire Resource

```javascript
// Update entire user object
const response = await fetch('/api/users/123', {
    method: 'PUT',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'Alice Updated',
        email: 'alice.new@example.com',
        age: 26
    })
});
```

**PHP:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $userId = 123; // From URL
    $input = json_decode(file_get_contents('php://input'), true);

    // Update entire record
    // UPDATE users SET name=?, email=?, age=? WHERE id=?
}
```

### PATCH - Update Specific Fields

```javascript
// Update only email
const response = await fetch('/api/users/123', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        email: 'alice.new@example.com'
    })
});
```

**PHP:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $userId = 123;
    $input = json_decode(file_get_contents('php://input'), true);

    // Update only provided fields
    if (isset($input['email'])) {
        // UPDATE users SET email=? WHERE id=?
    }
}
```

---

## DELETE Requests

Remove a resource:

```javascript
async function deletePost(postId) {
    if (!confirm('Are you sure?')) {
        return;
    }

    try {
        const response = await fetch(`/api/posts/${postId}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            alert('Post deleted');
            // Remove from UI
            document.getElementById(`post-${postId}`).remove();
        }

    } catch (error) {
        alert('Failed to delete post');
    }
}
```

**PHP:**
```php
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $postId = 123; // From URL

    // Delete from database
    // DELETE FROM posts WHERE id=?

    http_response_code(204); // No Content
    // No body needed for successful DELETE
}
```

---

## Complete CRUD Example

Let's build a complete Create-Read-Update-Delete interface:

### HTML

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Manager</title>
    <style>
        .task { border: 1px solid #ddd; padding: 10px; margin: 10px 0; }
        .task button { margin-left: 10px; }
    </style>
</head>
<body>
    <h1>Task Manager</h1>

    <!-- Create Form -->
    <form id="createForm">
        <input type="text" id="taskTitle" placeholder="New task..." required>
        <button type="submit">Add Task</button>
    </form>

    <!-- Task List -->
    <div id="tasks"></div>

    <script src="app.js"></script>
</body>
</html>
```

### JavaScript (app.js)

```javascript
const API_URL = '/api/tasks.php';

// Load tasks on page load
document.addEventListener('DOMContentLoaded', loadTasks);

// Create task
document.getElementById('createForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const title = document.getElementById('taskTitle').value;

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title })
        });

        if (response.ok) {
            document.getElementById('taskTitle').value = '';
            loadTasks(); // Refresh list
        }

    } catch (error) {
        console.error('Error creating task:', error);
    }
});

// Read tasks
async function loadTasks() {
    try {
        const response = await fetch(API_URL);
        const result = await response.json();

        if (result.success) {
            displayTasks(result.data);
        }

    } catch (error) {
        console.error('Error loading tasks:', error);
    }
}

// Display tasks
function displayTasks(tasks) {
    const container = document.getElementById('tasks');

    if (tasks.length === 0) {
        container.innerHTML = '<p>No tasks yet</p>';
        return;
    }

    container.innerHTML = tasks.map(task => `
        <div class="task" id="task-${task.id}">
            <span>${task.title}</span>
            <button onclick="editTask(${task.id}, '${task.title}')">Edit</button>
            <button onclick="deleteTask(${task.id})">Delete</button>
        </div>
    `).join('');
}

// Update task
async function editTask(id, currentTitle) {
    const newTitle = prompt('Edit task:', currentTitle);

    if (!newTitle || newTitle === currentTitle) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title: newTitle })
        });

        if (response.ok) {
            loadTasks(); // Refresh list
        }

    } catch (error) {
        console.error('Error updating task:', error);
    }
}

// Delete task
async function deleteTask(id) {
    if (!confirm('Delete this task?')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });

        if (response.ok) {
            document.getElementById(`task-${id}`).remove();
        }

    } catch (error) {
        console.error('Error deleting task:', error);
    }
}
```

### PHP API (api/tasks.php)

```php
<?php
header('Content-Type: application/json');

// Simple file-based storage (in production, use a database)
$dataFile = 'tasks.json';

// Helper functions
function getTasks() {
    global $dataFile;
    if (!file_exists($dataFile)) {
        return [];
    }
    return json_decode(file_get_contents($dataFile), true);
}

function saveTasks($tasks) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($tasks));
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Routes
switch ($method) {
    case 'GET':
        // Read all tasks
        $tasks = getTasks();
        echo json_encode([
            'success' => true,
            'data' => $tasks
        ]);
        break;

    case 'POST':
        // Create new task
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Title required']);
            exit;
        }

        $tasks = getTasks();
        $newTask = [
            'id' => time(), // Simple ID generation
            'title' => $input['title'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        $tasks[] = $newTask;
        saveTasks($tasks);

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => $newTask]);
        break;

    case 'PUT':
        // Update task
        $id = $_GET['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$id || empty($input['title'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }

        $tasks = getTasks();
        foreach ($tasks as &$task) {
            if ($task['id'] == $id) {
                $task['title'] = $input['title'];
                break;
            }
        }
        saveTasks($tasks);

        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        // Delete task
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID required']);
            exit;
        }

        $tasks = getTasks();
        $tasks = array_filter($tasks, fn($task) => $task['id'] != $id);
        saveTasks($tasks);

        http_response_code(204); // No Content
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(['error' => 'Method not allowed']);
}
```

---

## Headers Explained

Headers provide metadata about the request/response.

### Common Request Headers

```javascript
fetch('/api/data', {
    headers: {
        // Tell server what format we're sending
        'Content-Type': 'application/json',

        // Authentication
        'Authorization': 'Bearer YOUR_TOKEN_HERE',

        // API key
        'X-API-Key': 'your-api-key',

        // Custom headers
        'X-Custom-Header': 'custom-value'
    }
});
```

### Content-Type Values

| Value | When to Use |
|-------|-------------|
| `application/json` | Sending JSON data (most common) |
| `application/x-www-form-urlencoded` | Traditional form data |
| `multipart/form-data` | File uploads (use FormData) |
| `text/plain` | Plain text |

### Reading Response Headers

```javascript
const response = await fetch('/api/data');

console.log(response.headers.get('Content-Type'));
console.log(response.headers.get('X-Custom-Header'));

// Iterate all headers
for (let [key, value] of response.headers) {
    console.log(`${key}: ${value}`);
}
```

---

## Authentication with Fetch

### Bearer Token Authentication

```javascript
// Store token after login
const token = 'YOUR_JWT_TOKEN';

// Include in requests
const response = await fetch('/api/posts', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});
```

**PHP:**
```php
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';

if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
    // Verify token
}
```

### API Key Authentication

```javascript
const response = await fetch('/api/data', {
    headers: {
        'X-API-Key': 'your-api-key-here'
    }
});
```

**PHP:**
```php
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

if (!isValidApiKey($apiKey)) {
    http_response_code(401);
    exit;
}
```

---

## Best Practices

### 1. Always Use Try-Catch

```javascript
try {
    const response = await fetch('/api/data');
    const data = await response.json();
} catch (error) {
    console.error('Request failed:', error);
    // Show user-friendly error
}
```

### 2. Check Response Status

```javascript
const response = await fetch('/api/data');

if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Request failed');
}

const data = await response.json();
```

### 3. Validate Before Sending

```javascript
function createPost(title, content) {
    // Validate client-side first
    if (!title || title.length < 3) {
        alert('Title must be at least 3 characters');
        return;
    }

    // Then send to server
    fetch('/api/posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, content })
    });
}
```

### 4. Use Constants for URLs

```javascript
const API_BASE = '/api';
const ENDPOINTS = {
    posts: `${API_BASE}/posts.php`,
    users: `${API_BASE}/users.php`,
    auth: `${API_BASE}/auth.php`
};

// Use
fetch(ENDPOINTS.posts);
```

### 5. Create Reusable Functions

```javascript
async function apiRequest(endpoint, options = {}) {
    const defaults = {
        headers: {
            'Content-Type': 'application/json'
        }
    };

    const config = { ...defaults, ...options };

    const response = await fetch(endpoint, config);

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}

// Use
const posts = await apiRequest('/api/posts');
const newPost = await apiRequest('/api/posts', {
    method: 'POST',
    body: JSON.stringify({ title: 'Test' })
});
```

---

## Summary

**GET = Retrieve data (no body)**
```javascript
const data = await fetch('/api/posts').then(r => r.json());
```

**POST = Create resource (with body)**
```javascript
await fetch('/api/posts', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newPost)
});
```

**PUT = Update entire resource**
```javascript
await fetch('/api/posts/123', {
    method: 'PUT',
    body: JSON.stringify(updatedPost)
});
```

**DELETE = Remove resource**
```javascript
await fetch('/api/posts/123', { method: 'DELETE' });
```

**Key Concepts:**
- GET for reading, POST for creating, PUT for updating, DELETE for removing
- POST/PUT require `Content-Type: application/json` header
- Always use `JSON.stringify()` for body data
- Always check `response.ok` before parsing
- Use try-catch for error handling

---

## Practice Exercises

**Exercise 1:** Build a user registration form that POSTs to an API

**Exercise 2:** Create a product list that loads with GET and allows editing with PUT

**Exercise 3:** Implement a comment system with CREATE (POST) and DELETE

---

**Next up:** Lesson 04 - Handling JSON Responses - Master data manipulation!
