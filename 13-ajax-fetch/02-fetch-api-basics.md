# Lesson 02 - Fetch API Basics

**Duration**: 40-50 minutes
**Prerequisites**: Lesson 01 - AJAX Introduction

---

## Introduction to Fetch API

The **Fetch API** is the modern way to make HTTP requests in JavaScript. It's built into all modern browsers and uses Promises for clean, readable asynchronous code.

### Why Fetch?

**Before Fetch (XMLHttpRequest):**
```javascript
// Old, verbose way
var xhr = new XMLHttpRequest();
xhr.open('GET', '/api/users');
xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        console.log(data);
    }
};
xhr.send();
```

**With Fetch:**
```javascript
// Modern, clean way
fetch('/api/users')
    .then(response => response.json())
    .then(data => console.log(data));
```

**Even better with async/await:**
```javascript
// Cleanest way (what we'll use!)
const response = await fetch('/api/users');
const data = await response.json();
console.log(data);
```

---

## Your First Fetch Request

Let's make our first API call!

### Basic Syntax

```javascript
fetch(url, options)
```

- **url**: The endpoint you want to fetch from
- **options**: Configuration object (method, headers, body, etc.)

### Simplest Possible Request

```javascript
// GET request to fetch data
const response = await fetch('/api/users');
console.log(response);
```

**What you get back:**
- A **Response object** (not the actual data yet!)
- Contains metadata: status code, headers, etc.
- The data is in the body (needs to be extracted)

---

## Understanding the Response Object

When you fetch, you get a **Response** object:

```javascript
const response = await fetch('/api/users');

console.log(response.status);      // 200
console.log(response.ok);          // true (if status 200-299)
console.log(response.statusText);  // "OK"
console.log(response.headers);     // Headers object
console.log(response.url);         // Full URL
```

### Response Properties

| Property | Type | Description |
|----------|------|-------------|
| `status` | number | HTTP status code (200, 404, 500, etc.) |
| `ok` | boolean | `true` if status is 200-299 |
| `statusText` | string | Status message ("OK", "Not Found", etc.) |
| `headers` | Headers | Response headers |
| `url` | string | Final URL (after redirects) |

---

## Getting the Actual Data

The Response object has several methods to extract data:

### 1. `response.json()` - For JSON Data

Most common - your PHP APIs return JSON:

```javascript
const response = await fetch('/api/posts');
const data = await response.json();

console.log(data); // { success: true, data: [...] }
```

### 2. `response.text()` - For Plain Text

```javascript
const response = await fetch('/api/message');
const text = await response.text();

console.log(text); // "Hello, World!"
```

### 3. `response.blob()` - For Files/Images

```javascript
const response = await fetch('/images/photo.jpg');
const imageBlob = await response.blob();

// Create object URL to display image
const imageUrl = URL.createObjectURL(imageBlob);
img.src = imageUrl;
```

### 4. `response.formData()` - For Form Data

Rarely used, but available:

```javascript
const response = await fetch('/api/form-data');
const formData = await response.formData();
```

---

## Complete Examples

### Example 1: Fetch Users and Display

```javascript
async function loadUsers() {
    // 1. Make the request
    const response = await fetch('/api/users');

    // 2. Check if successful
    if (!response.ok) {
        console.error('Failed to load users');
        return;
    }

    // 3. Extract JSON data
    const data = await response.json();

    // 4. Use the data
    console.log('Users:', data);

    // 5. Display in UI
    const userList = document.getElementById('users');
    data.forEach(user => {
        userList.innerHTML += `<li>${user.name}</li>`;
    });
}

// Call it
loadUsers();
```

### Example 2: Fetch and Handle Errors

```javascript
async function loadPost(postId) {
    try {
        const response = await fetch(`/api/posts/${postId}`);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const post = await response.json();

        // Display post
        document.getElementById('title').textContent = post.title;
        document.getElementById('content').textContent = post.content;

    } catch (error) {
        console.error('Error loading post:', error);
        alert('Failed to load post');
    }
}

loadPost(123);
```

### Example 3: Fetch with Loading State

```javascript
async function loadData() {
    const button = document.getElementById('loadBtn');
    const container = document.getElementById('data');

    // Show loading
    button.disabled = true;
    container.innerHTML = 'Loading...';

    try {
        const response = await fetch('/api/data');
        const data = await response.json();

        // Display data
        container.innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;

    } catch (error) {
        container.innerHTML = `<p class="error">Failed to load data</p>`;

    } finally {
        // Always re-enable button
        button.disabled = false;
    }
}
```

---

## Working with Different APIs

Let's connect to your PHP APIs from Module 09!

### Your PHP API Endpoint

```php
<?php
// /api/posts.php
header('Content-Type: application/json');

$posts = [
    ['id' => 1, 'title' => 'First Post', 'author' => 'Alice'],
    ['id' => 2, 'title' => 'Second Post', 'author' => 'Bob'],
    ['id' => 3, 'title' => 'Third Post', 'author' => 'Charlie']
];

echo json_encode([
    'success' => true,
    'data' => $posts
]);
```

### JavaScript to Consume It

```javascript
async function loadPosts() {
    const response = await fetch('/api/posts.php');
    const result = await response.json();

    if (result.success) {
        displayPosts(result.data);
    }
}

function displayPosts(posts) {
    const container = document.getElementById('posts');

    posts.forEach(post => {
        container.innerHTML += `
            <div class="post">
                <h3>${post.title}</h3>
                <p>By ${post.author}</p>
            </div>
        `;
    });
}

loadPosts();
```

---

## Fetch Options: Customizing Requests

The second parameter to `fetch()` is an options object:

```javascript
fetch(url, {
    method: 'GET',           // HTTP method
    headers: {},             // Custom headers
    body: null,              // Request body
    mode: 'cors',            // cors, no-cors, same-origin
    credentials: 'same-origin', // include, same-origin, omit
    cache: 'default',        // Cache mode
    redirect: 'follow',      // follow, manual, error
    referrer: 'client'       // Referrer header
})
```

### Most Common Options

```javascript
// GET with custom header
fetch('/api/data', {
    headers: {
        'Authorization': 'Bearer YOUR_TOKEN'
    }
});

// POST with JSON body
fetch('/api/posts', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        title: 'My Post',
        content: 'Hello World'
    })
});
```

We'll cover POST in detail in Lesson 03!

---

## Common Status Codes

Understanding HTTP status codes is crucial:

### 2xx - Success

```javascript
const response = await fetch('/api/users');

if (response.status === 200) {
    // OK - Request succeeded
    const data = await response.json();
}

if (response.status === 201) {
    // Created - New resource created (POST)
    const newItem = await response.json();
}

if (response.status === 204) {
    // No Content - Success but no data (DELETE)
    console.log('Deleted successfully');
}
```

### 4xx - Client Errors

```javascript
const response = await fetch('/api/posts/999');

if (response.status === 400) {
    // Bad Request - Invalid input
    console.error('Invalid request');
}

if (response.status === 401) {
    // Unauthorized - Not logged in
    console.error('Please login');
}

if (response.status === 403) {
    // Forbidden - No permission
    console.error('Access denied');
}

if (response.status === 404) {
    // Not Found - Resource doesn't exist
    console.error('Post not found');
}

if (response.status === 422) {
    // Unprocessable Entity - Validation errors
    const errors = await response.json();
    console.error('Validation errors:', errors);
}
```

### 5xx - Server Errors

```javascript
if (response.status === 500) {
    // Internal Server Error - Server bug
    console.error('Server error');
}

if (response.status === 503) {
    // Service Unavailable - Server down
    console.error('Service temporarily unavailable');
}
```

### Using `response.ok`

Instead of checking every status code:

```javascript
const response = await fetch('/api/data');

if (response.ok) {
    // Any 2xx status code
    const data = await response.json();
    console.log('Success!', data);
} else {
    // Any other status code
    console.error('Request failed:', response.status);
}
```

---

## Error Handling Strategies

### Strategy 1: Try-Catch

```javascript
async function fetchData() {
    try {
        const response = await fetch('/api/data');

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;

    } catch (error) {
        console.error('Fetch error:', error);
        return null;
    }
}
```

### Strategy 2: Check and Return

```javascript
async function fetchData() {
    const response = await fetch('/api/data');

    if (!response.ok) {
        console.error('Failed:', response.status);
        return null;
    }

    return await response.json();
}
```

### Strategy 3: Detailed Error Info

```javascript
async function fetchData() {
    try {
        const response = await fetch('/api/data');

        if (!response.ok) {
            // Try to get error message from API
            const errorData = await response.json();
            throw new Error(errorData.message || 'Request failed');
        }

        return await response.json();

    } catch (error) {
        console.error('Error:', error.message);
        throw error; // Re-throw for caller to handle
    }
}
```

---

## Practical Example: User Profile Loader

Let's build a complete example:

### HTML

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <style>
        .loading { color: gray; }
        .error { color: red; }
        .profile { border: 1px solid #ddd; padding: 20px; }
    </style>
</head>
<body>
    <div id="app">
        <button id="loadBtn">Load Profile</button>
        <div id="profile"></div>
    </div>

    <script src="app.js"></script>
</body>
</html>
```

### JavaScript (app.js)

```javascript
// Get elements
const loadBtn = document.getElementById('loadBtn');
const profileDiv = document.getElementById('profile');

// Add click handler
loadBtn.addEventListener('click', loadProfile);

// Main function
async function loadProfile() {
    // Show loading
    profileDiv.className = 'loading';
    profileDiv.textContent = 'Loading...';
    loadBtn.disabled = true;

    try {
        // Fetch data
        const response = await fetch('/api/users/1');

        // Check status
        if (!response.ok) {
            throw new Error(`Error: ${response.status}`);
        }

        // Parse JSON
        const user = await response.json();

        // Display profile
        displayProfile(user);

    } catch (error) {
        // Show error
        profileDiv.className = 'error';
        profileDiv.textContent = `Failed to load profile: ${error.message}`;

    } finally {
        // Re-enable button
        loadBtn.disabled = false;
    }
}

// Display function
function displayProfile(user) {
    profileDiv.className = 'profile';
    profileDiv.innerHTML = `
        <h2>${user.name}</h2>
        <p><strong>Email:</strong> ${user.email}</p>
        <p><strong>Role:</strong> ${user.role}</p>
    `;
}
```

### PHP API (/api/users/1.php)

```php
<?php
header('Content-Type: application/json');

// Simulate database lookup
$user = [
    'id' => 1,
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com',
    'role' => 'Admin'
];

// Return JSON
echo json_encode($user);
```

---

## Fetch vs XMLHttpRequest

For comparison, here's the same request both ways:

### With Fetch (Modern)

```javascript
async function getUser() {
    const response = await fetch('/api/users/1');
    const user = await response.json();
    console.log(user);
}
```

**Lines of code:** 3
**Readability:** Excellent
**Error handling:** Natural with try-catch

### With XMLHttpRequest (Old)

```javascript
function getUser() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/api/users/1');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                const user = JSON.parse(xhr.responseText);
                console.log(user);
            }
        }
    };
    xhr.send();
}
```

**Lines of code:** 11
**Readability:** Poor
**Error handling:** Awkward with callbacks

**Winner:** Fetch API! Modern, clean, and powerful.

---

## Important Concepts

### 1. Fetch Returns a Promise

```javascript
const promise = fetch('/api/data');
// promise is pending...

promise.then(response => {
    console.log('Request completed!');
});
```

### 2. You Must Extract the Body

```javascript
// Wrong - response is not the data
const response = await fetch('/api/users');
console.log(response); // Response object, not user data

// Right - extract JSON
const response = await fetch('/api/users');
const users = await response.json();
console.log(users); // Actual user data
```

### 3. Body Can Only Be Read Once

```javascript
const response = await fetch('/api/data');

const data1 = await response.json(); // Works
const data2 = await response.json(); // ERROR! Already consumed

// If you need it twice, store it:
const response = await fetch('/api/data');
const data = await response.json();
console.log(data); // Use as many times as needed
console.log(data);
```

### 4. Fetch Only Rejects on Network Failure

```javascript
// This does NOT throw an error!
const response = await fetch('/api/not-found'); // 404
console.log(response.ok); // false

// You must check response.ok:
if (!response.ok) {
    throw new Error('Request failed');
}
```

---

## Testing Your Fetch Requests

### Method 1: Browser Console

```javascript
// Open any webpage
// Open DevTools (F12)
// Go to Console tab

fetch('https://jsonplaceholder.typicode.com/users/1')
    .then(r => r.json())
    .then(data => console.log(data));
```

### Method 2: Local PHP File

Create `test.php`:
```php
<?php
header('Content-Type: application/json');
echo json_encode(['message' => 'Hello from PHP!']);
```

Create `test.html`:
```html
<!DOCTYPE html>
<html>
<body>
    <script>
        fetch('test.php')
            .then(r => r.json())
            .then(data => alert(data.message));
    </script>
</body>
</html>
```

Open `test.html` in browser via Herd!

### Method 3: Public APIs

Test with free public APIs:

```javascript
// JSONPlaceholder - Fake REST API
fetch('https://jsonplaceholder.typicode.com/posts/1')
    .then(r => r.json())
    .then(post => console.log(post));

// Star Wars API
fetch('https://swapi.dev/api/people/1')
    .then(r => r.json())
    .then(person => console.log(person));
```

---

## Common Mistakes to Avoid

### Mistake 1: Forgetting `await`

```javascript
// Wrong - doesn't wait
const data = fetch('/api/users').json();
console.log(data); // Promise, not data!

// Right
const response = await fetch('/api/users');
const data = await response.json();
console.log(data); // Actual data
```

### Mistake 2: Not Checking `response.ok`

```javascript
// Wrong - might try to parse error page as JSON
const response = await fetch('/api/data');
const data = await response.json(); // Could fail!

// Right
const response = await fetch('/api/data');
if (response.ok) {
    const data = await response.json();
}
```

### Mistake 3: Not Using Try-Catch

```javascript
// Wrong - unhandled errors crash your app
const response = await fetch('/api/data');
const data = await response.json();

// Right
try {
    const response = await fetch('/api/data');
    const data = await response.json();
} catch (error) {
    console.error('Error:', error);
}
```

---

## Summary

**Fetch API = Modern way to make HTTP requests**

**Basic syntax:**
```javascript
const response = await fetch(url, options);
const data = await response.json();
```

**Key points:**
1. Fetch returns a Response object (not the data directly)
2. Use `response.json()` to extract JSON data
3. Check `response.ok` before parsing
4. Use try-catch for error handling
5. Fetch works perfectly with async/await

**Remember:**
- You built APIs in Module 09 (PHP side)
- Now you're consuming them (JavaScript side)
- Fetch connects your frontend to your backend

---

## Practice Exercises

Try these before moving to Lesson 03:

**Exercise 1:** Fetch and display a list of posts
```javascript
// Fetch from: https://jsonplaceholder.typicode.com/posts
// Display titles in a list
```

**Exercise 2:** Fetch user details and show in a card
```javascript
// Fetch from: https://jsonplaceholder.typicode.com/users/1
// Show name, email, phone in HTML
```

**Exercise 3:** Add error handling
```javascript
// Try fetching a non-existent resource
// Show a user-friendly error message
```

---

## Quick Quiz

1. What does `fetch()` return?
2. How do you extract JSON from a response?
3. What does `response.ok` mean?
4. What status codes are considered "ok"?
5. Can you read `response.json()` twice? Why or why not?

**Answers:**
1. A Promise that resolves to a Response object
2. `await response.json()`
3. `true` if status code is 200-299 (success)
4. 200-299 (2xx status codes)
5. No - the body stream can only be consumed once

---

**Next up:** Lesson 03 - GET and POST Requests - Let's send data to the server!
