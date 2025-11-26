# Lesson 01 - Introduction to Sessions

**Duration**: 45 minutes
**Objectives**: Understand what sessions are, why we need them, and how they work in web applications

---

## The Problem: HTTP is Stateless

Imagine you walk into a store, tell the cashier your name, walk around for 5 minutes, then come back to buy something. The cashier says: "Who are you? I've never seen you before!"

That's how the web works by default. **HTTP is stateless** - every request is completely independent. The server doesn't remember you between page loads.

### Why is HTTP Stateless?

When Tim Berners-Lee invented the web in 1989, it was designed for simple document sharing:
1. You request a page
2. Server sends it
3. Connection closes
4. Server forgets you existed

This works great for static documents, but terrible for modern web applications where you need to:
- Stay logged in while browsing multiple pages
- Keep items in a shopping cart
- Remember user preferences
- Track what step you're on in a multi-step form

### The Problem in Code

```php
<?php
// page1.php
$username = "John";
echo "Hello, $username!";
// You click a link to page2.php
?>
```

```php
<?php
// page2.php
echo "Welcome back, $username!"; // ERROR: Undefined variable $username
// The server has no idea who you are!
?>
```

Each PHP script runs independently. When page1.php finishes, all its variables disappear. Page2.php starts fresh with no memory of page1.php.

---

## The Solution: Sessions

**A session is a way to preserve data across multiple page requests for a specific user.**

Think of it like the store giving you a numbered ticket when you walk in. Every time you approach the cashier, you show your ticket, and they look up your information in their system.

### How Sessions Work (The Full Picture)

Here's what happens behind the scenes when you use sessions:

#### Step 1: User Visits Your Site

```php
<?php
// login.php
session_start(); // "Hey PHP, I want to use sessions!"

$_SESSION['username'] = 'John';
$_SESSION['user_id'] = 42;
$_SESSION['is_admin'] = false;

echo "Login successful!";
?>
```

**What PHP does internally:**

1. **Generates a unique Session ID** - A random string like: `a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`
2. **Creates a file on the server** - Stores your data in `/tmp/sess_a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`
3. **Sends a cookie to the browser** - `Set-Cookie: PHPSESSID=a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`

The session file contains:
```
username|s:4:"John";user_id|i:42;is_admin|b:0;
```

#### Step 2: User Visits Another Page

```php
<?php
// dashboard.php
session_start(); // "Load the session based on the cookie!"

echo "Welcome back, " . $_SESSION['username']; // "Welcome back, John"
echo "Your ID is: " . $_SESSION['user_id']; // "Your ID is: 42"
?>
```

**What happens:**

1. Browser sends cookie: `Cookie: PHPSESSID=a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`
2. PHP reads the session ID from the cookie
3. PHP loads the file `/tmp/sess_a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`
4. PHP populates `$_SESSION` array with the saved data
5. You can access the data!

#### Step 3: User Closes Browser

Most session cookies are "session cookies" (confusing name!) - they expire when you close the browser. The session file stays on the server, but you lose the Session ID cookie, so you can't access that session anymore.

The server eventually deletes old session files (usually after 24 minutes of inactivity by default).

---

## Session ID: Your Digital Ticket

The **Session ID** is the most critical part of the session system. It's like your ticket number at the store.

### What Makes a Good Session ID?

**PHP generates Session IDs automatically** using cryptographically secure random data. A typical Session ID looks like:

```
4f8k2m9p1s7v3x6z8c4e1g5i2j7l9n3q
```

Properties of a secure Session ID:
- **Long**: 32+ characters (harder to guess)
- **Random**: Generated with cryptographically secure random functions
- **Unique**: No two users ever get the same ID
- **Unpredictable**: You can't guess the next ID

### Why Security Matters

If an attacker can guess or steal your Session ID, they can:
- Pretend to be you
- Access your account
- Steal your data

This is called **session hijacking** - we'll cover protection in Lesson 11.

---

## The $_SESSION Superglobal

In PHP, session data is stored in the `$_SESSION` array - a special "superglobal" available everywhere in your code.

### What Can You Store?

You can store any PHP data type:

```php
<?php
session_start();

// Strings
$_SESSION['username'] = 'John Doe';

// Integers
$_SESSION['user_id'] = 42;
$_SESSION['login_attempts'] = 3;

// Booleans
$_SESSION['is_admin'] = false;
$_SESSION['is_logged_in'] = true;

// Arrays
$_SESSION['permissions'] = ['read', 'write', 'delete'];
$_SESSION['user_data'] = [
    'name' => 'John',
    'email' => 'john@example.com',
    'age' => 30
];

// You can even store objects (but be careful!)
$_SESSION['cart'] = new ShoppingCart();
?>
```

### What You CANNOT Store

- **File handles**: `$_SESSION['file'] = fopen('data.txt', 'r');` ❌
- **Database connections**: `$_SESSION['db'] = new PDO(...)` ❌
- **Resources**: Anything that represents an external resource
- **Closures**: Anonymous functions ❌

These cannot be serialized (converted to text) for storage.

---

## The Session Lifecycle

Understanding the complete lifecycle helps you use sessions correctly.

### 1. Session Start

```php
<?php
session_start();
// Must be called BEFORE any output (HTML, echo, etc.)
// Must be called on EVERY page that uses sessions
?>
```

**Common mistake:**

```php
<?php
echo "Hello!"; // Output sent to browser
session_start(); // ERROR: Cannot modify header information
?>
```

Why? Because `session_start()` needs to send a cookie, and cookies are HTTP headers. Headers must be sent before any content.

### 2. Session Active

```php
<?php
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "Welcome back, user #" . $_SESSION['user_id'];
} else {
    echo "Please log in.";
}

// Modify session data
$_SESSION['last_page'] = 'dashboard.php';
$_SESSION['page_views'] = ($_SESSION['page_views'] ?? 0) + 1;
?>
```

### 3. Session Destroy

```php
<?php
session_start();

// Method 1: Unset specific variables
unset($_SESSION['user_id']);
unset($_SESSION['username']);

// Method 2: Clear all session data
$_SESSION = [];

// Method 3: Completely destroy the session
session_destroy();
?>
```

**Important:** `session_destroy()` doesn't unset `$_SESSION` immediately. For complete logout:

```php
<?php
session_start();

// 1. Clear session variables
$_SESSION = [];

// 2. Delete session cookie
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie('PHPSESSID', '', time() - 3600, '/');
}

// 3. Destroy session file on server
session_destroy();

header('Location: login.php');
exit;
?>
```

---

## Sessions vs Cookies

Both sessions and cookies store data, but they work differently:

### Cookies

```php
<?php
// Set a cookie that lasts 30 days
setcookie('username', 'John', time() + (30 * 24 * 60 * 60));

// Cookie is sent to the browser and stored there
// Every request sends this cookie back to the server
// Client can see and modify cookies!
?>
```

**Properties:**
- ✅ Persist after browser closes (if expiration set)
- ✅ Work without server-side storage
- ❌ Limited size (4KB per cookie)
- ❌ Client can see and modify the data
- ❌ Sent with EVERY request (affects performance)
- ❌ Not secure for sensitive data

### Sessions

```php
<?php
session_start();
$_SESSION['username'] = 'John';

// Data stored on SERVER, not sent to browser
// Browser only gets a Session ID cookie
// Client cannot see or modify the data
?>
```

**Properties:**
- ✅ More secure (data on server)
- ✅ Can store more data (limited by server disk space)
- ✅ Client cannot modify the data
- ✅ Only Session ID sent with requests
- ❌ Require server storage
- ❌ Usually expire when browser closes

### When to Use Each?

**Use Sessions for:**
- User authentication state (logged in/out)
- Sensitive data (user ID, permissions)
- Temporary data during browsing
- Shopping cart contents (if not logged in)

**Use Cookies for:**
- "Remember me" functionality (we'll cover in Lesson 8)
- User preferences (theme, language)
- Tracking for analytics
- Non-sensitive data that needs to persist

**In authentication, you'll use BOTH:**
- Session: Store user_id, login state
- Cookie: Store "remember me" token for persistent login

---

## Where Sessions Are Stored

By default, PHP stores session files on the server's filesystem:

```
Linux/Mac: /tmp/sess_[session_id]
Windows: C:\Windows\Temp\sess_[session_id]
```

### Session Storage Example

After running:
```php
<?php
session_start();
$_SESSION['user_id'] = 42;
$_SESSION['username'] = 'John';
?>
```

A file is created: `/tmp/sess_a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3`

Contents:
```
user_id|i:42;username|s:4:"John";
```

This is PHP's serialization format.

### Alternative Storage Methods

For larger applications, you can configure PHP to store sessions in:

1. **Database** (MySQL, PostgreSQL)
   - Good for multiple web servers
   - Persistent and reliable
   - Slower than files

2. **Redis or Memcached**
   - Very fast (in-memory)
   - Good for high-traffic sites
   - Requires additional software

3. **Custom handler**
   - You write your own storage logic
   - Maximum control

We'll stick with file-based storage for this module - it's simple and works great for most applications.

---

## Session Configuration

PHP has many configuration options for sessions. Here are the most important:

### php.ini Settings

```ini
; Session lifetime (in seconds)
session.gc_maxlifetime = 1440  ; 24 minutes

; Session cookie name
session.name = PHPSESSID

; Only send cookie over HTTPS
session.cookie_secure = 1

; JavaScript cannot access cookie (prevents XSS attacks)
session.cookie_httponly = 1

; Cookie is sent only to your domain
session.cookie_samesite = "Strict"
```

### Runtime Configuration

You can change settings in your code:

```php
<?php
// Must be called BEFORE session_start()
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 0); // Until browser closes
ini_set('session.cookie_httponly', 1); // JavaScript cannot access

session_start();
?>
```

We'll cover secure session configuration in detail in Lesson 11.

---

## Practical Example: Visit Counter

Let's build something real to see sessions in action:

```php
<?php
// counter.php
session_start();

// Initialize counter if it doesn't exist
if (!isset($_SESSION['visit_count'])) {
    $_SESSION['visit_count'] = 0;
}

// Increment counter
$_SESSION['visit_count']++;

// Store first visit time
if (!isset($_SESSION['first_visit'])) {
    $_SESSION['first_visit'] = date('Y-m-d H:i:s');
}

// Store last visit time
$_SESSION['last_visit'] = date('Y-m-d H:i:s');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visit Counter</title>
</head>
<body>
    <h1>Session Visit Counter</h1>

    <p>You have visited this page <strong><?= $_SESSION['visit_count'] ?></strong> times.</p>

    <p>First visit: <?= $_SESSION['first_visit'] ?></p>
    <p>Last visit: <?= $_SESSION['last_visit'] ?></p>

    <p><a href="counter.php">Refresh page</a> (counter will increase)</p>
    <p><a href="reset.php">Reset counter</a></p>

    <hr>
    <h2>Session Debug Info</h2>
    <p>Session ID: <?= session_id() ?></p>
    <p>Session Name: <?= session_name() ?></p>
    <pre><?php print_r($_SESSION); ?></pre>
</body>
</html>
```

```php
<?php
// reset.php
session_start();

// Clear all session data
session_unset();  // Removes all variables
session_destroy(); // Destroys the session

header('Location: counter.php');
exit;
?>
```

**Try this yourself!**
1. Visit `counter.php` - counter shows 1
2. Refresh page - counter increases
3. Close and reopen browser - counter resets (session expired)
4. Click "Reset counter" - counter goes back to 1

---

## Common Session Mistakes (Learn from Others' Errors!)

### Mistake 1: Forgetting session_start()

```php
<?php
// login.php
// session_start(); // FORGOT THIS!

$_SESSION['user_id'] = 42; // ERROR: Undefined array key
?>
```

**Fix:** Always call `session_start()` at the top of every page that uses sessions.

### Mistake 2: Output Before session_start()

```php
<?php
echo "Welcome!"; // Output sent!
session_start(); // ERROR: Cannot modify header information
?>
```

**Fix:** Call `session_start()` before ANY output (HTML, echo, whitespace).

### Mistake 3: Not Checking if Key Exists

```php
<?php
session_start();
echo "User ID: " . $_SESSION['user_id']; // WARNING: Undefined array key
?>
```

**Fix:** Always check if the key exists first:

```php
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'];
} else {
    echo "Not logged in";
}

// Or use null coalescing operator (PHP 7+)
$userId = $_SESSION['user_id'] ?? null;
?>
```

### Mistake 4: Incomplete Logout

```php
<?php
session_start();
session_destroy(); // Not enough!
// User data might still be accessible in $_SESSION!
?>
```

**Fix:** Full logout procedure (from earlier):

```php
<?php
session_start();
$_SESSION = [];
setcookie('PHPSESSID', '', time() - 3600, '/');
session_destroy();
?>
```

---

## Debugging Sessions

When sessions don't work, here's how to debug:

### Check if Session Started

```php
<?php
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "Session is active";
} else {
    echo "No session active";
}
?>
```

### Display All Session Data

```php
<?php
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>
```

### Check Session ID

```php
<?php
session_start();
echo "Session ID: " . session_id();
echo "<br>Session Name: " . session_name();
?>
```

### Check Session Files (on server)

```bash
# Linux/Mac
ls -la /tmp/sess_*

# View a session file
cat /tmp/sess_a8f5j2k9m3n7p1q4r6s8t2v5w7x9z1c3
```

---

## What's Next?

Now you understand **what** sessions are and **why** we need them. In the next lesson, we'll get hands-on:

**Lesson 02 - Session Basics**: You'll write real code to:
- Start sessions properly
- Store and retrieve data
- Build a multi-page form that remembers your input
- Create a simple login state

**Then in Lesson 03**, we'll learn about **password hashing** - the foundation of secure authentication.

---

## Quick Quiz

Test your understanding:

1. **What problem do sessions solve?**
   - HTTP is stateless, sessions preserve data across requests

2. **Where is session data stored?**
   - On the server (usually in files), not in the browser

3. **What gets sent to the browser?**
   - Only the Session ID (in a cookie)

4. **When must you call session_start()?**
   - At the beginning of every page that uses sessions, before any output

5. **Can users modify session data?**
   - No, it's stored on the server. They can only see their Session ID.

---

## Key Takeaways

- HTTP is stateless - servers forget you between requests
- Sessions preserve data by storing it on the server and sending a Session ID cookie
- Call `session_start()` before using `$_SESSION` on every page
- Session data is stored on the server, not in the browser
- Sessions are more secure than cookies for sensitive data
- Always validate and sanitize data, even from sessions
- Sessions expire after inactivity or when the browser closes

In the next lesson, we'll write real code and build something practical!

---

## Laravel Preview: What Changes Later?

**Now (Pure PHP):**
```php
<?php
session_start();
$_SESSION['user_id'] = 42;
$userId = $_SESSION['user_id'] ?? null;
?>
```

**Laravel (Module 16):**
```php
<?php
// Laravel handles session_start() automatically
session(['user_id' => 42]);
$userId = session('user_id');

// Or using the helper
session()->put('user_id', 42);
$userId = session()->get('user_id');
?>
```

Laravel also:
- Stores sessions in database by default (not files)
- Handles secure configuration automatically
- Provides CSRF protection out of the box
- Manages session regeneration for you

But now you know what's happening behind Laravel's magic!
