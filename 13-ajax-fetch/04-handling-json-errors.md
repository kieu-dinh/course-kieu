# Lesson 04 - Handling JSON Responses and Errors

**Duration**: 50-60 minutes
**Prerequisites**: Lesson 03 - GET and POST Requests

---

## Introduction

In real-world applications, things go wrong. Network failures, server errors, invalid data - you need to handle all of these gracefully. This lesson teaches you professional error handling and JSON manipulation.

**What you'll learn:**
- Parse and work with complex JSON structures
- Handle different types of errors
- Provide meaningful feedback to users
- Build robust, production-ready code

---

## Understanding JSON Responses

Remember from Module 09? Your PHP APIs return JSON in a specific structure.

### Standard Response Formats

**Success Response:**
```json
{
    "success": true,
    "data": {
        "id": 123,
        "title": "My Post",
        "author": "Alice"
    },
    "message": "Post created successfully"
}
```

**Error Response:**
```json
{
    "success": false,
    "error": "Post not found",
    "code": "POST_NOT_FOUND"
}
```

**List Response with Pagination:**
```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "page": 1,
        "per_page": 10,
        "total": 156,
        "total_pages": 16
    }
}
```

---

## Working with Complex JSON

### Nested Objects

```javascript
// API returns nested user data
const response = await fetch('/api/users/1');
const data = await response.json();

console.log(data);
/*
{
    "id": 1,
    "name": "Alice",
    "email": "alice@example.com",
    "profile": {
        "bio": "Developer",
        "avatar": "avatar.jpg",
        "location": {
            "city": "Paris",
            "country": "France"
        }
    },
    "posts": [
        { "id": 1, "title": "First Post" },
        { "id": 2, "title": "Second Post" }
    ]
}
*/

// Access nested data
console.log(data.name);                    // "Alice"
console.log(data.profile.bio);             // "Developer"
console.log(data.profile.location.city);   // "Paris"
console.log(data.posts[0].title);          // "First Post"
```

### Arrays of Objects

```javascript
// API returns list of posts
const response = await fetch('/api/posts');
const result = await response.json();

// Extract the array
const posts = result.data;

// Loop through posts
posts.forEach(post => {
    console.log(post.title);
    console.log(post.author.name); // Nested object
    console.log(post.tags.join(', ')); // Array of strings
});

// Map to extract specific fields
const titles = posts.map(post => post.title);
// ["First Post", "Second Post", "Third Post"]

// Filter by condition
const alicePosts = posts.filter(post => post.author.name === 'Alice');

// Find specific item
const post = posts.find(post => post.id === 5);
```

### Dealing with Null/Undefined Values

Always check for missing data:

```javascript
const user = await response.json();

// Bad - will crash if profile is null
console.log(user.profile.bio);

// Good - safe access
console.log(user.profile?.bio || 'No bio');

// Good - check before accessing
if (user.profile && user.profile.bio) {
    console.log(user.profile.bio);
}

// Good - with default
const bio = user.profile?.bio ?? 'No bio provided';
```

---

## Types of Errors to Handle

### 1. Network Errors

**When:** No internet, server down, DNS failure

```javascript
async function fetchData() {
    try {
        const response = await fetch('/api/data');
        const data = await response.json();
        return data;
    } catch (error) {
        // Network error occurred
        console.error('Network error:', error);
        return {
            error: true,
            message: 'Unable to connect to server. Check your internet connection.'
        };
    }
}
```

### 2. HTTP Status Errors

**When:** 404 Not Found, 500 Server Error, 401 Unauthorized

```javascript
async function fetchPost(id) {
    try {
        const response = await fetch(`/api/posts/${id}`);

        // Check HTTP status
        if (!response.ok) {
            // Try to get error details from API
            let errorMessage = `HTTP error! status: ${response.status}`;

            try {
                const errorData = await response.json();
                errorMessage = errorData.error || errorData.message || errorMessage;
            } catch {
                // Couldn't parse error as JSON
            }

            throw new Error(errorMessage);
        }

        const post = await response.json();
        return post;

    } catch (error) {
        console.error('Error fetching post:', error);
        throw error; // Re-throw for caller to handle
    }
}

// Usage
try {
    const post = await fetchPost(999);
    displayPost(post);
} catch (error) {
    showError(`Failed to load post: ${error.message}`);
}
```

### 3. JSON Parsing Errors

**When:** Server returns invalid JSON or HTML error page

```javascript
async function fetchData() {
    try {
        const response = await fetch('/api/data');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Try to parse JSON
        try {
            const data = await response.json();
            return data;
        } catch (jsonError) {
            console.error('Invalid JSON response');
            // Maybe server returned HTML error page
            const text = await response.text();
            console.error('Response was:', text);
            throw new Error('Server returned invalid data');
        }

    } catch (error) {
        console.error('Fetch error:', error);
        throw error;
    }
}
```

### 4. Validation Errors

**When:** Server rejects input (422 Unprocessable Entity)

```javascript
async function createPost(postData) {
    try {
        const response = await fetch('/api/posts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(postData)
        });

        const result = await response.json();

        if (response.status === 422) {
            // Validation errors
            return {
                success: false,
                errors: result.errors
                // { title: ['Title is required'], content: ['Too short'] }
            };
        }

        if (!response.ok) {
            throw new Error(result.error || 'Failed to create post');
        }

        return {
            success: true,
            data: result.data
        };

    } catch (error) {
        return {
            success: false,
            error: error.message
        };
    }
}

// Usage
const result = await createPost({ title: '', content: 'Hi' });

if (!result.success) {
    if (result.errors) {
        // Show validation errors
        Object.entries(result.errors).forEach(([field, messages]) => {
            showFieldError(field, messages[0]);
        });
    } else {
        // Show general error
        showError(result.error);
    }
}
```

### 5. Authentication Errors

**When:** Token expired, not logged in (401/403)

```javascript
async function fetchProtectedData() {
    try {
        const token = localStorage.getItem('auth_token');

        if (!token) {
            throw new Error('Not authenticated');
        }

        const response = await fetch('/api/protected', {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });

        if (response.status === 401) {
            // Token expired or invalid
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
            return;
        }

        if (response.status === 403) {
            throw new Error('You do not have permission to access this resource');
        }

        if (!response.ok) {
            throw new Error('Failed to fetch data');
        }

        return await response.json();

    } catch (error) {
        console.error('Auth error:', error);
        throw error;
    }
}
```

---

## Building a Robust API Client

Let's create a reusable API client with complete error handling:

```javascript
class ApiClient {
    constructor(baseUrl = '/api') {
        this.baseUrl = baseUrl;
        this.defaultHeaders = {
            'Content-Type': 'application/json'
        };
    }

    // Set auth token
    setToken(token) {
        this.defaultHeaders['Authorization'] = `Bearer ${token}`;
    }

    // Remove auth token
    clearToken() {
        delete this.defaultHeaders['Authorization'];
    }

    // Generic request method
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;

        const config = {
            ...options,
            headers: {
                ...this.defaultHeaders,
                ...options.headers
            }
        };

        try {
            const response = await fetch(url, config);

            // Handle different status codes
            if (response.status === 401) {
                // Unauthorized - redirect to login
                this.clearToken();
                window.location.href = '/login';
                throw new Error('Session expired. Please login again.');
            }

            if (response.status === 403) {
                throw new Error('You do not have permission to perform this action');
            }

            if (response.status === 404) {
                throw new Error('Resource not found');
            }

            // Try to parse JSON
            let data;
            const contentType = response.headers.get('content-type');

            if (contentType && contentType.includes('application/json')) {
                data = await response.json();
            } else {
                data = await response.text();
            }

            // Check if request was successful
            if (!response.ok) {
                const errorMessage = data.error || data.message || `HTTP error! status: ${response.status}`;
                throw new Error(errorMessage);
            }

            return data;

        } catch (error) {
            // Network error or other issue
            console.error('API request failed:', error);
            throw error;
        }
    }

    // Convenience methods
    async get(endpoint, params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        return this.request(url, { method: 'GET' });
    }

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, {
            method: 'DELETE'
        });
    }
}

// Create instance
const api = new ApiClient('/api');

// Usage examples
try {
    // GET request
    const posts = await api.get('/posts', { page: 1, per_page: 10 });
    console.log('Posts:', posts);

    // POST request
    const newPost = await api.post('/posts', {
        title: 'My Post',
        content: 'Hello World'
    });
    console.log('Created:', newPost);

    // PUT request
    const updated = await api.put('/posts/123', {
        title: 'Updated Title'
    });
    console.log('Updated:', updated);

    // DELETE request
    await api.delete('/posts/123');
    console.log('Deleted successfully');

} catch (error) {
    console.error('Error:', error.message);
    showUserError(error.message);
}

// Set authentication
api.setToken('your-jwt-token-here');
```

---

## Error Handling Patterns

### Pattern 1: Try-Catch with User Feedback

```javascript
async function loadPosts() {
    const loadingDiv = document.getElementById('loading');
    const postsDiv = document.getElementById('posts');
    const errorDiv = document.getElementById('error');

    // Show loading
    loadingDiv.style.display = 'block';
    errorDiv.style.display = 'none';
    postsDiv.innerHTML = '';

    try {
        const response = await fetch('/api/posts');

        if (!response.ok) {
            throw new Error('Failed to load posts');
        }

        const result = await response.json();

        // Hide loading
        loadingDiv.style.display = 'none';

        // Show posts
        displayPosts(result.data);

    } catch (error) {
        // Hide loading
        loadingDiv.style.display = 'none';

        // Show error
        errorDiv.style.display = 'block';
        errorDiv.textContent = `Error: ${error.message}. Please try again.`;

        // Optional: retry button
        errorDiv.innerHTML += '<button onclick="loadPosts()">Retry</button>';
    }
}
```

### Pattern 2: Return Result Object

```javascript
async function fetchData(url) {
    try {
        const response = await fetch(url);

        if (!response.ok) {
            return {
                success: false,
                error: `HTTP error! status: ${response.status}`
            };
        }

        const data = await response.json();

        return {
            success: true,
            data: data
        };

    } catch (error) {
        return {
            success: false,
            error: error.message
        };
    }
}

// Usage
const result = await fetchData('/api/posts');

if (result.success) {
    console.log('Data:', result.data);
} else {
    console.error('Error:', result.error);
}
```

### Pattern 3: Promises with .catch()

```javascript
fetch('/api/posts')
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        displayPosts(data);
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Failed to load posts');
    })
    .finally(() => {
        hideLoading();
    });
```

---

## Practical Example: Blog Post Viewer with Full Error Handling

### HTML

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog Viewer</title>
    <style>
        .loading { color: gray; font-style: italic; }
        .error {
            color: #d32f2f;
            background: #ffebee;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .post {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 10px 0;
            border-radius: 4px;
        }
        button {
            padding: 8px 16px;
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div id="app">
        <h1>Blog Posts</h1>

        <div id="loading" class="loading" style="display: none;">
            Loading posts...
        </div>

        <div id="error" class="error" style="display: none;"></div>

        <div id="posts"></div>
    </div>

    <script src="blog.js"></script>
</body>
</html>
```

### JavaScript (blog.js)

```javascript
// Elements
const loadingDiv = document.getElementById('loading');
const errorDiv = document.getElementById('error');
const postsDiv = document.getElementById('posts');

// State
let retryCount = 0;
const MAX_RETRIES = 3;

// Load posts on page load
document.addEventListener('DOMContentLoaded', () => {
    loadPosts();
});

// Main function
async function loadPosts() {
    showLoading();
    hideError();
    postsDiv.innerHTML = '';

    try {
        const posts = await fetchPosts();
        displayPosts(posts);
        retryCount = 0; // Reset on success

    } catch (error) {
        console.error('Error loading posts:', error);
        showError(error.message, error.retryable);
    } finally {
        hideLoading();
    }
}

// Fetch posts with complete error handling
async function fetchPosts() {
    try {
        const response = await fetch('/api/posts.php', {
            // Add timeout using AbortController
            signal: AbortSignal.timeout(10000) // 10 second timeout
        });

        // Handle specific HTTP errors
        if (response.status === 404) {
            throw new Error('API endpoint not found. Please check the server configuration.');
        }

        if (response.status === 500) {
            const error = new Error('Server error. Please try again later.');
            error.retryable = true;
            throw error;
        }

        if (response.status === 503) {
            const error = new Error('Service temporarily unavailable. Please try again in a few minutes.');
            error.retryable = true;
            throw error;
        }

        if (!response.ok) {
            throw new Error(`Failed to load posts (HTTP ${response.status})`);
        }

        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned invalid data format');
        }

        // Parse JSON
        let data;
        try {
            data = await response.json();
        } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            throw new Error('Server returned invalid JSON');
        }

        // Validate response structure
        if (!data.success) {
            throw new Error(data.error || 'Request failed');
        }

        if (!Array.isArray(data.data)) {
            throw new Error('Invalid data structure received from server');
        }

        return data.data;

    } catch (error) {
        // Handle different error types
        if (error.name === 'AbortError' || error.name === 'TimeoutError') {
            const timeoutError = new Error('Request timed out. Please check your connection.');
            timeoutError.retryable = true;
            throw timeoutError;
        }

        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            // Network error
            const networkError = new Error('Cannot connect to server. Please check your internet connection.');
            networkError.retryable = true;
            throw networkError;
        }

        // Re-throw with context
        throw error;
    }
}

// Display posts
function displayPosts(posts) {
    if (posts.length === 0) {
        postsDiv.innerHTML = '<p>No posts available.</p>';
        return;
    }

    postsDiv.innerHTML = posts.map(post => `
        <article class="post">
            <h2>${escapeHtml(post.title)}</h2>
            <p class="meta">
                By ${escapeHtml(post.author?.name || 'Unknown')}
                on ${formatDate(post.created_at)}
            </p>
            <p>${escapeHtml(post.excerpt || '')}</p>
            <a href="#" onclick="viewPost(${post.id}); return false;">Read more</a>
        </article>
    `).join('');
}

// View single post with error handling
async function viewPost(postId) {
    try {
        showLoading();

        const response = await fetch(`/api/posts.php?id=${postId}`);

        if (response.status === 404) {
            throw new Error('Post not found');
        }

        if (!response.ok) {
            throw new Error('Failed to load post');
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Failed to load post');
        }

        // Show post in modal or navigate
        alert(`Post: ${result.data.title}\n\n${result.data.content}`);

    } catch (error) {
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// UI helpers
function showLoading() {
    loadingDiv.style.display = 'block';
}

function hideLoading() {
    loadingDiv.style.display = 'none';
}

function showError(message, retryable = false) {
    errorDiv.style.display = 'block';
    errorDiv.innerHTML = `
        <strong>Error:</strong> ${escapeHtml(message)}
        ${retryable && retryCount < MAX_RETRIES ?
            '<button onclick="retryLoad()">Retry</button>' :
            '<button onclick="hideError()">Dismiss</button>'
        }
    `;
}

function hideError() {
    errorDiv.style.display = 'none';
}

function retryLoad() {
    retryCount++;
    console.log(`Retry attempt ${retryCount} of ${MAX_RETRIES}`);
    loadPosts();
}

// Utility functions
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(dateString) {
    if (!dateString) return 'Unknown date';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch {
        return dateString;
    }
}
```

---

## Handling Validation Errors

When creating or updating resources, the server might return validation errors:

### PHP API Response

```php
<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $errors = [];

    if (empty($input['title'])) {
        $errors['title'][] = 'Title is required';
    } elseif (strlen($input['title']) < 3) {
        $errors['title'][] = 'Title must be at least 3 characters';
    }

    if (empty($input['content'])) {
        $errors['content'][] = 'Content is required';
    } elseif (strlen($input['content']) < 10) {
        $errors['content'][] = 'Content must be at least 10 characters';
    }

    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
        exit;
    }

    // Save post...
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data' => ['id' => 123, 'title' => $input['title']]
    ]);
}
```

### JavaScript Handling

```javascript
async function submitForm() {
    const form = document.getElementById('postForm');
    const formData = {
        title: form.title.value,
        content: form.content.value
    };

    // Clear previous errors
    clearFormErrors();

    try {
        const response = await fetch('/api/posts.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (response.status === 422) {
            // Validation errors
            displayValidationErrors(result.errors);
            return;
        }

        if (!response.ok) {
            throw new Error(result.error || 'Failed to create post');
        }

        // Success!
        alert('Post created successfully!');
        form.reset();

    } catch (error) {
        console.error('Error:', error);
        showError(error.message);
    }
}

function displayValidationErrors(errors) {
    Object.entries(errors).forEach(([field, messages]) => {
        const input = document.getElementById(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = messages[0]; // Show first error

        // Insert error message after input
        input.parentNode.insertBefore(errorDiv, input.nextSibling);

        // Add error class to input
        input.classList.add('error');
    });
}

function clearFormErrors() {
    document.querySelectorAll('.field-error').forEach(el => el.remove());
    document.querySelectorAll('input.error, textarea.error').forEach(el => {
        el.classList.remove('error');
    });
}
```

---

## Timeout Handling

Prevent requests from hanging forever:

```javascript
// Modern way - AbortSignal.timeout (Chrome 103+)
async function fetchWithTimeout(url, timeoutMs = 5000) {
    try {
        const response = await fetch(url, {
            signal: AbortSignal.timeout(timeoutMs)
        });
        return await response.json();
    } catch (error) {
        if (error.name === 'TimeoutError') {
            throw new Error('Request timed out');
        }
        throw error;
    }
}

// Manual AbortController (more browser support)
async function fetchWithManualTimeout(url, timeoutMs = 5000) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), timeoutMs);

    try {
        const response = await fetch(url, {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        return await response.json();
    } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            throw new Error('Request timed out');
        }
        throw error;
    }
}

// Usage
try {
    const data = await fetchWithTimeout('/api/slow-endpoint', 3000);
    console.log('Data:', data);
} catch (error) {
    console.error('Error:', error.message);
}
```

---

## Best Practices Summary

### 1. Always Validate Response

```javascript
const response = await fetch('/api/data');

// Check status
if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
}

// Check content type
const contentType = response.headers.get('content-type');
if (!contentType || !contentType.includes('application/json')) {
    throw new Error('Invalid response type');
}

// Parse JSON
const data = await response.json();

// Validate structure
if (!data.success) {
    throw new Error(data.error || 'Request failed');
}
```

### 2. Provide User-Friendly Messages

```javascript
// Bad
catch (error) {
    alert(error); // Shows "TypeError: Failed to fetch"
}

// Good
catch (error) {
    let message = 'An error occurred. Please try again.';

    if (error.message.includes('fetch')) {
        message = 'Cannot connect to server. Check your internet connection.';
    } else if (error.message.includes('404')) {
        message = 'The requested resource was not found.';
    }

    showUserFriendlyError(message);
}
```

### 3. Log Detailed Errors

```javascript
try {
    const data = await fetchData();
} catch (error) {
    // Detailed log for developers
    console.error('Fetch failed:', {
        error: error,
        message: error.message,
        stack: error.stack,
        url: '/api/data',
        timestamp: new Date().toISOString()
    });

    // Simple message for users
    showError('Failed to load data. Please try again.');
}
```

### 4. Handle Edge Cases

```javascript
// Check for empty/null data
if (!data || !data.data || data.data.length === 0) {
    showMessage('No data available');
    return;
}

// Handle missing nested properties
const userName = user?.profile?.name || 'Unknown User';

// Validate before using
if (typeof post.id !== 'number') {
    throw new Error('Invalid post ID');
}
```

---

## Summary

**Key Points:**
1. Always check `response.ok` before parsing JSON
2. Use try-catch blocks for all fetch requests
3. Handle different error types (network, HTTP, validation)
4. Provide user-friendly error messages
5. Validate JSON structure before using data
6. Use optional chaining (?.) for nested properties
7. Build reusable API client classes
8. Add timeouts to prevent hanging requests

**Error Handling Checklist:**
- [ ] Check HTTP status code
- [ ] Verify content type is JSON
- [ ] Parse JSON safely (try-catch)
- [ ] Validate response structure
- [ ] Show user-friendly messages
- [ ] Log detailed errors for debugging
- [ ] Handle validation errors (422)
- [ ] Handle authentication errors (401/403)
- [ ] Add timeout for long requests
- [ ] Provide retry mechanism

---

## Practice Exercises

**Exercise 1:** Create a user registration form that handles all validation errors individually

**Exercise 2:** Build a data loader with retry logic (max 3 attempts with exponential backoff)

**Exercise 3:** Implement a robust search feature that handles empty results, network errors, and invalid queries

---

**Next up:** Lesson 05 - Loading States and User Experience - Make your app feel professional!
