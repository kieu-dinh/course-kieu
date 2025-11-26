# Lesson 09 - Fetch API for AJAX Requests

**Duration**: 3-4 hours
**Prerequisites**: Lesson 08 - Async JavaScript

---

## What is Fetch API?

The **Fetch API** is a modern way to make HTTP requests (AJAX) from JavaScript. It replaces the old `XMLHttpRequest`.

**AJAX** = Asynchronous JavaScript and XML (but we use JSON now)
- Load data without refreshing the page
- Submit forms without page reload
- Update parts of page dynamically

### Compare to PHP

**PHP (Server-side):**
```php
<?php
// PHP makes HTTP request from server
$response = file_get_contents("https://api.example.com/users");
$users = json_decode($response);
?>
```

**JavaScript (Client-side):**
```javascript
// JavaScript makes HTTP request from browser
fetch("https://api.example.com/users")
    .then(response => response.json())
    .then(users => {
        console.log(users);
    });
```

**Key Difference**: PHP runs on your server, JavaScript runs in user's browser.

---

## Basic Fetch Syntax

### Simple GET Request

```javascript
fetch("https://api.example.com/users")
    .then((response) => {
        return response.json(); // Parse JSON
    })
    .then((data) => {
        console.log(data); // Use the data
    })
    .catch((error) => {
        console.error("Error:", error);
    });
```

### With async/await (Cleaner)

```javascript
async function getUsers() {
    try {
        const response = await fetch("https://api.example.com/users");
        const data = await response.json();
        console.log(data);
    } catch (error) {
        console.error("Error:", error);
    }
}

getUsers();
```

**I recommend async/await** - it's cleaner and easier to understand.

---

## Understanding the Response

### The Response Object

```javascript
const response = await fetch("https://api.example.com/users");

console.log(response.status);     // 200
console.log(response.statusText); // "OK"
console.log(response.ok);         // true (if status 200-299)
console.log(response.headers);    // Headers object
console.log(response.url);        // Full URL
```

### Parsing Response Body

```javascript
const response = await fetch(url);

// Parse as JSON (most common)
const data = await response.json();

// Parse as text
const text = await response.text();

// Parse as Blob (images, files)
const blob = await response.blob();

// Parse as FormData
const formData = await response.formData();
```

### Checking for Errors

**Important**: Fetch only rejects on network errors, not HTTP errors!

```javascript
// Bad: Doesn't handle HTTP errors (404, 500, etc.)
async function getUser(id) {
    const response = await fetch(`https://api.example.com/users/${id}`);
    const user = await response.json();
    return user;
}

// Good: Check response.ok
async function getUser(id) {
    const response = await fetch(`https://api.example.com/users/${id}`);

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const user = await response.json();
    return user;
}
```

---

## GET Requests

Retrieve data from server.

### Basic GET

```javascript
async function getUsers() {
    try {
        const response = await fetch("https://jsonplaceholder.typicode.com/users");

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const users = await response.json();
        console.log(users);
    } catch (error) {
        console.error("Failed to fetch users:", error);
    }
}
```

### GET with Query Parameters

```javascript
// Build URL with parameters
const params = new URLSearchParams({
    page: 1,
    limit: 10,
    sort: "name"
});

const url = `https://api.example.com/users?${params}`;
// Result: https://api.example.com/users?page=1&limit=10&sort=name

const response = await fetch(url);
const data = await response.json();
```

### Practical Example: Display Users

```html
<div id="app">
    <button id="load-users">Load Users</button>
    <div id="loading" style="display: none;">Loading...</div>
    <ul id="user-list"></ul>
</div>
```

```javascript
const loadBtn = document.querySelector("#load-users");
const loadingDiv = document.querySelector("#loading");
const userList = document.querySelector("#user-list");

loadBtn.addEventListener("click", async () => {
    try {
        // Show loading
        loadingDiv.style.display = "block";
        userList.innerHTML = "";

        // Fetch users
        const response = await fetch("https://jsonplaceholder.typicode.com/users");

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const users = await response.json();

        // Hide loading
        loadingDiv.style.display = "none";

        // Display users
        users.forEach((user) => {
            const li = document.createElement("li");
            li.textContent = `${user.name} (${user.email})`;
            userList.appendChild(li);
        });
    } catch (error) {
        loadingDiv.style.display = "none";
        userList.innerHTML = `<li style="color: red;">Error: ${error.message}</li>`;
    }
});
```

---

## POST Requests

Send data to server.

### Basic POST

```javascript
async function createUser(userData) {
    try {
        const response = await fetch("https://jsonplaceholder.typicode.com/users", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(userData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const newUser = await response.json();
        console.log("User created:", newUser);
        return newUser;
    } catch (error) {
        console.error("Failed to create user:", error);
    }
}

// Usage
createUser({
    name: "Kieu Nguyen",
    email: "kieu@example.com",
    age: 25
});
```

### Form Submission Example

```html
<form id="user-form">
    <input type="text" id="name" placeholder="Name" required>
    <input type="email" id="email" placeholder="Email" required>
    <button type="submit">Create User</button>
</form>
<div id="result"></div>
```

```javascript
const form = document.querySelector("#user-form");
const resultDiv = document.querySelector("#result");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.querySelector("#name").value;
    const email = document.querySelector("#email").value;

    try {
        resultDiv.textContent = "Creating user...";

        const response = await fetch("https://jsonplaceholder.typicode.com/users", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ name, email })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const user = await response.json();

        resultDiv.textContent = `User created! ID: ${user.id}`;
        resultDiv.style.color = "green";

        // Clear form
        form.reset();
    } catch (error) {
        resultDiv.textContent = `Error: ${error.message}`;
        resultDiv.style.color = "red";
    }
});
```

---

## PUT and PATCH Requests

Update existing data.

### PUT - Replace Entire Resource

```javascript
async function updateUser(userId, userData) {
    const response = await fetch(`https://api.example.com/users/${userId}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(userData)
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}

// Usage
updateUser(1, {
    name: "Kieu Nguyen",
    email: "kieu@example.com",
    age: 26
});
```

### PATCH - Update Specific Fields

```javascript
async function updateUserEmail(userId, newEmail) {
    const response = await fetch(`https://api.example.com/users/${userId}`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ email: newEmail })
    });

    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
}

// Usage
updateUserEmail(1, "newemail@example.com");
```

---

## DELETE Requests

Delete data from server.

```javascript
async function deleteUser(userId) {
    try {
        const response = await fetch(`https://api.example.com/users/${userId}`, {
            method: "DELETE"
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        console.log(`User ${userId} deleted`);
    } catch (error) {
        console.error("Failed to delete user:", error);
    }
}

// Usage
deleteUser(1);
```

### Practical Example: Todo List with API

```html
<div id="app">
    <form id="todo-form">
        <input type="text" id="todo-input" placeholder="New todo" required>
        <button type="submit">Add</button>
    </form>
    <ul id="todo-list"></ul>
</div>
```

```javascript
const API_URL = "https://jsonplaceholder.typicode.com/todos";
const form = document.querySelector("#todo-form");
const input = document.querySelector("#todo-input");
const list = document.querySelector("#todo-list");

// Load todos
async function loadTodos() {
    const response = await fetch(`${API_URL}?_limit=5`);
    const todos = await response.json();

    list.innerHTML = "";
    todos.forEach((todo) => {
        addTodoToDOM(todo);
    });
}

// Add todo to DOM
function addTodoToDOM(todo) {
    const li = document.createElement("li");
    li.innerHTML = `
        <span style="${todo.completed ? 'text-decoration: line-through;' : ''}">${todo.title}</span>
        <button class="delete-btn" data-id="${todo.id}">Delete</button>
    `;
    list.appendChild(li);
}

// Create todo
async function createTodo(title) {
    const response = await fetch(API_URL, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            title: title,
            completed: false,
            userId: 1
        })
    });

    return await response.json();
}

// Delete todo
async function deleteTodo(id) {
    await fetch(`${API_URL}/${id}`, {
        method: "DELETE"
    });
}

// Form submit
form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const todo = await createTodo(input.value);
    addTodoToDOM(todo);

    input.value = "";
});

// Delete button (event delegation)
list.addEventListener("click", async (e) => {
    if (e.target.classList.contains("delete-btn")) {
        const id = e.target.dataset.id;
        await deleteTodo(id);
        e.target.parentElement.remove();
    }
});

// Load on page load
loadTodos();
```

---

## Headers and Authentication

### Custom Headers

```javascript
const response = await fetch(url, {
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Custom-Header": "value"
    }
});
```

### Authorization Header

```javascript
// Bearer token (common for APIs)
const response = await fetch(url, {
    headers: {
        "Authorization": `Bearer ${token}`
    }
});

// Basic auth
const response = await fetch(url, {
    headers: {
        "Authorization": `Basic ${btoa(`${username}:${password}`)}`
    }
});
```

### API Key Authentication

```javascript
const API_KEY = "your-api-key";

const response = await fetch(`https://api.example.com/data?api_key=${API_KEY}`);

// Or in header
const response = await fetch("https://api.example.com/data", {
    headers: {
        "X-API-Key": API_KEY
    }
});
```

---

## Working with JSON

### Sending JSON

```javascript
const data = {
    name: "Kieu",
    age: 25,
    hobbies: ["coding", "reading"]
};

const response = await fetch(url, {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify(data) // Convert object to JSON string
});
```

### Receiving JSON

```javascript
const response = await fetch(url);
const data = await response.json(); // Parse JSON string to object
console.log(data);
```

### Handling Nested JSON

```javascript
const response = await fetch("https://jsonplaceholder.typicode.com/users/1");
const user = await response.json();

console.log(user.name);           // "Leanne Graham"
console.log(user.address.city);   // "Gwenborough"
console.log(user.company.name);   // "Romaguera-Crona"
```

---

## Error Handling

### Network Errors

```javascript
try {
    const response = await fetch(url);
    const data = await response.json();
} catch (error) {
    // Network error (no internet, DNS failed, etc.)
    console.error("Network error:", error);
}
```

### HTTP Errors

```javascript
try {
    const response = await fetch(url);

    if (!response.ok) {
        // HTTP error (404, 500, etc.)
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
} catch (error) {
    console.error("Error:", error.message);
}
```

### Complete Error Handling

```javascript
async function fetchWithErrorHandling(url) {
    try {
        const response = await fetch(url);

        // Check HTTP status
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
        }

        // Parse JSON
        const data = await response.json();
        return data;
    } catch (error) {
        if (error.name === "TypeError") {
            // Network error
            console.error("Network error:", error.message);
        } else {
            // HTTP or parsing error
            console.error("Error:", error.message);
        }
        throw error; // Re-throw for caller to handle
    }
}
```

---

## Fetch Options

Full fetch configuration:

```javascript
const response = await fetch(url, {
    method: "POST",              // GET, POST, PUT, PATCH, DELETE
    headers: {                   // Custom headers
        "Content-Type": "application/json",
        "Authorization": "Bearer token"
    },
    body: JSON.stringify(data),  // Request body
    mode: "cors",                // cors, no-cors, same-origin
    credentials: "same-origin",  // include, same-origin, omit
    cache: "default",            // default, no-cache, reload, force-cache
    redirect: "follow",          // follow, manual, error
    referrer: "client",          // client, no-referrer, URL
    signal: abortController.signal // For aborting request
});
```

### Aborting Requests

```javascript
const controller = new AbortController();

// Start request
fetch(url, {
    signal: controller.signal
})
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => {
        if (error.name === "AbortError") {
            console.log("Request aborted");
        }
    });

// Abort after 5 seconds
setTimeout(() => {
    controller.abort();
}, 5000);
```

### Timeout Implementation

```javascript
async function fetchWithTimeout(url, timeout = 5000) {
    const controller = new AbortController();

    const timeoutId = setTimeout(() => {
        controller.abort();
    }, timeout);

    try {
        const response = await fetch(url, {
            signal: controller.signal
        });
        clearTimeout(timeoutId);
        return await response.json();
    } catch (error) {
        clearTimeout(timeoutId);
        if (error.name === "AbortError") {
            throw new Error("Request timeout");
        }
        throw error;
    }
}

// Usage
try {
    const data = await fetchWithTimeout("https://api.example.com/data", 3000);
    console.log(data);
} catch (error) {
    console.error(error.message);
}
```

---

## CORS (Cross-Origin Resource Sharing)

### What is CORS?

Browsers block requests to different domains for security:

```javascript
// Your site: https://mysite.com
// API: https://api.example.com

// This will fail if API doesn't allow CORS
fetch("https://api.example.com/data");
```

**Error**: `No 'Access-Control-Allow-Origin' header`

### Solutions

1. **API must allow CORS** (server-side configuration)
2. **Use a proxy** (your server makes the request)
3. **JSONP** (old technique, rarely used now)

**If you control the API** (PHP example):
```php
<?php
// Allow CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Your API code
$data = ["message" => "Hello from API"];
echo json_encode($data);
?>
```

---

## Practical Examples

### Example 1: Search with Debounce

```html
<input type="text" id="search" placeholder="Search users...">
<ul id="results"></ul>
```

```javascript
const searchInput = document.querySelector("#search");
const resultsList = document.querySelector("#results");

let searchTimeout;

searchInput.addEventListener("input", (e) => {
    const query = e.target.value;

    // Clear previous timeout
    clearTimeout(searchTimeout);

    if (query.length < 3) {
        resultsList.innerHTML = "";
        return;
    }

    // Debounce: wait 500ms after user stops typing
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`https://jsonplaceholder.typicode.com/users?name_like=${query}`);
            const users = await response.json();

            resultsList.innerHTML = "";

            if (users.length === 0) {
                resultsList.innerHTML = "<li>No results found</li>";
                return;
            }

            users.forEach((user) => {
                const li = document.createElement("li");
                li.textContent = user.name;
                resultsList.appendChild(li);
            });
        } catch (error) {
            resultsList.innerHTML = `<li>Error: ${error.message}</li>`;
        }
    }, 500);
});
```

### Example 2: Pagination

```html
<div id="app">
    <ul id="post-list"></ul>
    <div id="pagination">
        <button id="prev">Previous</button>
        <span id="page-info"></span>
        <button id="next">Next</button>
    </div>
</div>
```

```javascript
const postList = document.querySelector("#post-list");
const prevBtn = document.querySelector("#prev");
const nextBtn = document.querySelector("#next");
const pageInfo = document.querySelector("#page-info");

let currentPage = 1;
const postsPerPage = 10;

async function loadPosts(page) {
    try {
        const response = await fetch(
            `https://jsonplaceholder.typicode.com/posts?_page=${page}&_limit=${postsPerPage}`
        );

        const posts = await response.json();

        postList.innerHTML = "";

        posts.forEach((post) => {
            const li = document.createElement("li");
            li.innerHTML = `<strong>${post.title}</strong><br>${post.body}`;
            postList.appendChild(li);
        });

        pageInfo.textContent = `Page ${page}`;

        prevBtn.disabled = page === 1;
    } catch (error) {
        postList.innerHTML = `<li>Error: ${error.message}</li>`;
    }
}

prevBtn.addEventListener("click", () => {
    currentPage--;
    loadPosts(currentPage);
});

nextBtn.addEventListener("click", () => {
    currentPage++;
    loadPosts(currentPage);
});

// Load first page
loadPosts(currentPage);
```

### Example 3: File Upload

```html
<form id="upload-form">
    <input type="file" id="file-input" required>
    <button type="submit">Upload</button>
</form>
<div id="status"></div>
```

```javascript
const form = document.querySelector("#upload-form");
const fileInput = document.querySelector("#file-input");
const statusDiv = document.querySelector("#status");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const file = fileInput.files[0];

    if (!file) {
        alert("Please select a file");
        return;
    }

    // Create FormData
    const formData = new FormData();
    formData.append("file", file);
    formData.append("title", file.name);

    try {
        statusDiv.textContent = "Uploading...";

        const response = await fetch("https://api.example.com/upload", {
            method: "POST",
            body: formData // Don't set Content-Type header!
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        statusDiv.textContent = "Upload successful!";
        statusDiv.style.color = "green";
    } catch (error) {
        statusDiv.textContent = `Upload failed: ${error.message}`;
        statusDiv.style.color = "red";
    }
});
```

---

## Practice Exercises

### Exercise 1: User List

Fetch and display users from: `https://jsonplaceholder.typicode.com/users`

Show: name, email, company name

### Exercise 2: Post Details

Create a page that:
1. Loads all posts
2. When you click a post, show its details and comments
3. Use: `/posts` and `/posts/{id}/comments`

### Exercise 3: CRUD Todo App

Create a todo app with all CRUD operations:
- Create (POST)
- Read (GET)
- Update (PUT/PATCH)
- Delete (DELETE)

Use: `https://jsonplaceholder.typicode.com/todos`

### Exercise 4: Live Search

Create a search that:
- Searches as you type (debounced)
- Shows loading indicator
- Displays results
- Handles errors

Use: `https://jsonplaceholder.typicode.com/users`

### Exercise 5: Photo Gallery

Fetch photos and create a gallery:
- Load from: `https://jsonplaceholder.typicode.com/photos?_limit=20`
- Show thumbnails
- Click thumbnail to see full image

---

## Quick Reference

### Basic Fetch
```javascript
const response = await fetch(url);
const data = await response.json();
```

### POST Request
```javascript
await fetch(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(data)
});
```

### Error Handling
```javascript
if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
}
```

### Headers
```javascript
headers: {
    "Content-Type": "application/json",
    "Authorization": `Bearer ${token}`
}
```

---

## Key Takeaways

1. **Fetch returns a Promise** - use async/await
2. **Always check response.ok** for HTTP errors
3. **Parse response** with `.json()`, `.text()`, etc.
4. **Use try/catch** for error handling
5. **POST/PUT** need method, headers, and body
6. **JSON.stringify()** to send, `.json()` to receive
7. **CORS** is a browser security feature
8. **Debounce search** to avoid too many requests

---

## Next Lesson

In Lesson 10, we'll learn about:
- Local Storage and Session Storage
- Storing data in the browser
- Persisting user preferences
- Building offline-capable apps
- Storage limitations and best practices

Time to save data in the browser!
