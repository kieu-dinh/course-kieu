# Lesson 02 - Session Basics: Practical Implementation

**Duration**: 1 hour
**Objectives**: Write real code to start, use, and manage sessions in PHP

---

## Starting a Session Correctly

You learned the theory in Lesson 01. Now let's write code.

### The Golden Rule

**Every page that uses sessions must call `session_start()` first.**

Here's the basic template you'll use on every session-enabled page:

```php
<?php
// ALWAYS at the very top of the file
// Before ANY HTML, echo, or output
session_start();

// Now you can use $_SESSION
?>
<!DOCTYPE html>
<html>
<!-- Rest of your page -->
</html>
```

### Why "Before Any Output"?

When you call `session_start()`, PHP needs to send a cookie to the browser. Cookies are sent as HTTP headers, and headers must come before the page content.

**This breaks:**

```php
<!DOCTYPE html>
<?php session_start(); ?> <!-- ERROR! -->
```

Even a single space or newline before `<?php` counts as output!

**This also breaks:**

```php
<?php
echo "Hello";
session_start(); // ERROR: Cannot modify header information
?>
```

**This works:**

```php
<?php
session_start(); // First thing!
echo "Hello";
?>
```

---

## Creating a Session Helper File

Professional PHP projects use a helper file for session management. This ensures `session_start()` is called correctly everywhere.

Create this file and include it on every page:

```php
<?php
// includes/session.php

// Only start session if one isn't active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a session variable
 */
function session_set(string $key, mixed $value): void
{
    $_SESSION[$key] = $value;
}

/**
 * Get a session variable
 */
function session_get(string $key, mixed $default = null): mixed
{
    return $_SESSION[$key] ?? $default;
}

/**
 * Check if a session variable exists
 */
function session_has(string $key): bool
{
    return isset($_SESSION[$key]);
}

/**
 * Remove a session variable
 */
function session_remove(string $key): void
{
    unset($_SESSION[$key]);
}

/**
 * Get and remove a session variable (flash message)
 */
function session_flash(string $key, mixed $default = null): mixed
{
    $value = $_SESSION[$key] ?? $default;
    unset($_SESSION[$key]);
    return $value;
}

/**
 * Clear all session data
 */
function session_clear(): void
{
    $_SESSION = [];
}

/**
 * Completely destroy the session
 */
function session_end(): void
{
    $_SESSION = [];

    if (isset($_COOKIE['PHPSESSID'])) {
        setcookie('PHPSESSID', '', time() - 3600, '/');
    }

    session_destroy();
}
?>
```

**Why use helper functions?**

Instead of:
```php
$username = $_SESSION['username'] ?? null;
```

You write:
```php
$username = session_get('username');
```

Cleaner, more readable, and easier to change later.

### Using the Helper

```php
<?php
require_once __DIR__ . '/includes/session.php';

// Now session is already started, and you have helper functions!

session_set('username', 'John');
echo "Hello, " . session_get('username');

if (session_has('user_id')) {
    echo "You're logged in!";
}
?>
```

---

## Practical Example 1: Remembering Form Data

Ever filled out a long form, submitted it, got an error, and lost all your data? Frustrating! Sessions can prevent this.

### The Problem

```php
<!-- form.php -->
<form method="POST" action="process.php">
    <input type="text" name="name" placeholder="Your Name">
    <input type="email" name="email" placeholder="Email">
    <textarea name="message" placeholder="Message"></textarea>
    <button type="submit">Send</button>
</form>
```

```php
<?php
// process.php
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Redirect back to form
    header('Location: form.php');
    exit;
    // Problem: User loses all their data!
}
?>
```

### The Solution: Session Flashing

**Flash messages** are session variables that exist for one request, then disappear.

```php
<?php
// process.php
require_once 'includes/session.php';

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Save the form data to session
    session_set('old_name', $name);
    session_set('old_email', $email);
    session_set('old_message', $message);

    // Save error message
    session_set('error', 'Please enter a valid email address.');

    header('Location: form.php');
    exit;
}

// Process successful submission...
session_set('success', 'Message sent successfully!');
header('Location: form.php');
exit;
?>
```

```php
<?php
// form.php
require_once 'includes/session.php';

// Get old values and remove from session (flash)
$oldName = session_flash('old_name', '');
$oldEmail = session_flash('old_email', '');
$oldMessage = session_flash('old_message', '');

// Get messages
$error = session_flash('error');
$success = session_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form</title>
    <style>
        .error { color: red; padding: 10px; background: #ffe6e6; }
        .success { color: green; padding: 10px; background: #e6ffe6; }
    </style>
</head>
<body>
    <h1>Contact Us</h1>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="process.php">
        <div>
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($oldName) ?>">
        </div>

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($oldEmail) ?>">
        </div>

        <div>
            <label>Message:</label>
            <textarea name="message"><?= htmlspecialchars($oldMessage) ?></textarea>
        </div>

        <button type="submit">Send</button>
    </form>
</body>
</html>
```

**What happens:**

1. User fills form and submits with invalid email
2. `process.php` saves form data to session
3. Redirects back to `form.php`
4. `form.php` retrieves and displays the data
5. `session_flash()` removes the data so it doesn't persist

Try submitting again - the old data is gone!

---

## Practical Example 2: Multi-Step Form

Sometimes forms are too long for one page. You split them into steps. Sessions remember answers from previous steps.

### Step 1: Personal Information

```php
<?php
// step1.php
require_once 'includes/session.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save to session
    session_set('step1_name', $_POST['name'] ?? '');
    session_set('step1_email', $_POST['email'] ?? '');
    session_set('step1_phone', $_POST['phone'] ?? '');

    // Go to next step
    header('Location: step2.php');
    exit;
}

// Get existing values (if user goes back)
$name = session_get('step1_name', '');
$email = session_get('step1_email', '');
$phone = session_get('step1_phone', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 1 - Personal Information</title>
</head>
<body>
    <h1>Step 1 of 3: Personal Information</h1>

    <form method="POST">
        <div>
            <label>Full Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div>
            <label>Phone:</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
        </div>

        <button type="submit">Next Step</button>
    </form>
</body>
</html>
```

### Step 2: Address

```php
<?php
// step2.php
require_once 'includes/session.php';

// Ensure step 1 is completed
if (!session_has('step1_name')) {
    header('Location: step1.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_set('step2_address', $_POST['address'] ?? '');
    session_set('step2_city', $_POST['city'] ?? '');
    session_set('step2_zipcode', $_POST['zipcode'] ?? '');

    header('Location: step3.php');
    exit;
}

$address = session_get('step2_address', '');
$city = session_get('step2_city', '');
$zipcode = session_get('step2_zipcode', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 2 - Address</title>
</head>
<body>
    <h1>Step 2 of 3: Address</h1>

    <form method="POST">
        <div>
            <label>Street Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" required>
        </div>

        <div>
            <label>City:</label>
            <input type="text" name="city" value="<?= htmlspecialchars($city) ?>" required>
        </div>

        <div>
            <label>Zip Code:</label>
            <input type="text" name="zipcode" value="<?= htmlspecialchars($zipcode) ?>" required>
        </div>

        <a href="step1.php">← Back</a>
        <button type="submit">Next Step</button>
    </form>
</body>
</html>
```

### Step 3: Confirmation

```php
<?php
// step3.php
require_once 'includes/session.php';

// Ensure previous steps completed
if (!session_has('step1_name') || !session_has('step2_address')) {
    header('Location: step1.php');
    exit;
}

// Handle final submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all data
    $data = [
        'name' => session_get('step1_name'),
        'email' => session_get('step1_email'),
        'phone' => session_get('step1_phone'),
        'address' => session_get('step2_address'),
        'city' => session_get('step2_city'),
        'zipcode' => session_get('step2_zipcode'),
    ];

    // Save to database, send email, etc.
    // ...

    // Clear form data from session
    session_remove('step1_name');
    session_remove('step1_email');
    session_remove('step1_phone');
    session_remove('step2_address');
    session_remove('step2_city');
    session_remove('step2_zipcode');

    session_set('success', 'Registration complete!');
    header('Location: success.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 3 - Confirmation</title>
</head>
<body>
    <h1>Step 3 of 3: Confirm Your Information</h1>

    <h2>Personal Information</h2>
    <p><strong>Name:</strong> <?= htmlspecialchars(session_get('step1_name')) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars(session_get('step1_email')) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars(session_get('step1_phone')) ?></p>

    <h2>Address</h2>
    <p><strong>Address:</strong> <?= htmlspecialchars(session_get('step2_address')) ?></p>
    <p><strong>City:</strong> <?= htmlspecialchars(session_get('step2_city')) ?></p>
    <p><strong>Zip:</strong> <?= htmlspecialchars(session_get('step2_zipcode')) ?></p>

    <form method="POST">
        <a href="step2.php">← Back</a>
        <button type="submit">Complete Registration</button>
    </form>
</body>
</html>
```

**Key concepts:**
- Each step saves its data to session
- Next step checks if previous steps are completed
- User can go back and data persists
- Final step clears session data after saving

---

## Practical Example 3: Simple Login State

Before we build real authentication (next lessons), let's simulate login state:

```php
<?php
// login.php
require_once 'includes/session.php';

// Fake user (in real app, check database)
$validUsername = 'admin';
$validPassword = 'secret123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $validUsername && $password === $validPassword) {
        // Login successful
        session_set('logged_in', true);
        session_set('username', $username);

        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <div>
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>

        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <p><em>Try: admin / secret123</em></p>
</body>
</html>
```

```php
<?php
// dashboard.php
require_once 'includes/session.php';

// Check if logged in
if (!session_get('logged_in')) {
    header('Location: login.php');
    exit;
}

$username = session_get('username');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Dashboard</h1>
    <p>Welcome, <?= htmlspecialchars($username) ?>!</p>

    <p>This page is protected. Only logged in users can see it.</p>

    <a href="logout.php">Logout</a>
</body>
</html>
```

```php
<?php
// logout.php
require_once 'includes/session.php';

session_end();

header('Location: login.php');
exit;
?>
```

**Try it:**
1. Visit `dashboard.php` - redirects to login
2. Login with admin/secret123
3. Now you can access dashboard
4. Logout - back to login page
5. Try to access dashboard again - protected!

---

## Understanding Session Expiration

Sessions don't last forever. They expire for security and server resources.

### Default Expiration

By default, PHP session cookies expire when you close your browser. The session file on the server expires after 24 minutes of inactivity.

### Setting Custom Expiration

```php
<?php
// session-config.php

// Set session lifetime to 1 hour (3600 seconds)
ini_set('session.gc_maxlifetime', 3600);

// Set cookie to expire in 1 hour
ini_set('session.cookie_lifetime', 3600);

session_start();
?>
```

**Important:** Both settings work together:
- `gc_maxlifetime`: How long the session file stays on server
- `cookie_lifetime`: How long the browser keeps the Session ID cookie

### Manual Timeout Check

You can implement custom timeout:

```php
<?php
// includes/session.php

// Add this to your session helper
function check_session_timeout(int $timeout = 1800): void
{
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];

        if ($elapsed > $timeout) {
            // Session expired
            session_end();
            header('Location: login.php?timeout=1');
            exit;
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}
?>
```

```php
<?php
// dashboard.php
require_once 'includes/session.php';

// Check for timeout (30 minutes)
check_session_timeout(1800);

if (!session_get('logged_in')) {
    header('Location: login.php');
    exit;
}
?>
```

---

## Session Security Basics

We'll cover security in depth in Lesson 11, but here are essential basics:

### 1. Always Use HTTPS in Production

Sessions over HTTP can be intercepted (session hijacking).

```php
<?php
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (php_sapi_name() !== 'cli') { // Allow CLI for testing
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Set secure cookie flag
ini_set('session.cookie_secure', 1);
session_start();
?>
```

### 2. Prevent JavaScript Access

XSS attacks can steal session IDs via JavaScript. Prevent this:

```php
<?php
ini_set('session.cookie_httponly', 1);
session_start();
?>
```

Now JavaScript cannot access the session cookie.

### 3. Regenerate Session ID on Login

Prevents session fixation attacks (more in Lesson 11):

```php
<?php
// After successful login
session_regenerate_id(true); // true = delete old session file
$_SESSION['user_id'] = $userId;
?>
```

### 4. Never Trust Session Data Blindly

Even session data should be validated:

```php
<?php
$userId = session_get('user_id');

// Verify user still exists in database
$user = getUserFromDatabase($userId);

if (!$user) {
    // User was deleted - logout
    session_end();
    header('Location: login.php');
    exit;
}
?>
```

---

## Common Session Patterns

### Pattern 1: Flash Messages

Messages that show once, then disappear:

```php
<?php
// Helper function
function set_flash(string $type, string $message): void
{
    session_set("flash_{$type}", $message);
}

function get_flash(string $type): ?string
{
    return session_flash("flash_{$type}");
}
?>
```

```php
<?php
// After saving something
set_flash('success', 'User created successfully!');
header('Location: users.php');
exit;
?>
```

```php
<?php
// users.php
$success = get_flash('success');
$error = get_flash('error');
?>
```

### Pattern 2: Redirect with Data

```php
<?php
function redirect_with(string $url, array $data): void
{
    foreach ($data as $key => $value) {
        session_set("redirect_{$key}", $value);
    }

    header("Location: {$url}");
    exit;
}

function redirect_data(string $key, mixed $default = null): mixed
{
    return session_flash("redirect_{$key}", $default);
}
?>
```

```php
<?php
// After failed validation
redirect_with('form.php', [
    'errors' => ['Email is required', 'Name is too short'],
    'old' => $_POST
]);
?>
```

```php
<?php
// form.php
$errors = redirect_data('errors', []);
$old = redirect_data('old', []);
?>
```

### Pattern 3: Shopping Cart

```php
<?php
function add_to_cart(int $productId, int $quantity = 1): void
{
    $cart = session_get('cart', []);

    if (isset($cart[$productId])) {
        $cart[$productId] += $quantity;
    } else {
        $cart[$productId] = $quantity;
    }

    session_set('cart', $cart);
}

function get_cart(): array
{
    return session_get('cart', []);
}

function cart_count(): int
{
    $cart = get_cart();
    return array_sum($cart);
}

function clear_cart(): void
{
    session_remove('cart');
}
?>
```

---

## Debugging Session Issues

### Issue 1: "Headers already sent"

**Error:**
```
Warning: Cannot modify header information - headers already sent by (output started at file.php:1)
```

**Causes:**
1. Output before `session_start()`
2. Whitespace or BOM before `<?php`
3. Echo statements before `session_start()`

**Debug:**
```php
<?php
// Check if headers sent
if (headers_sent($file, $line)) {
    echo "Headers sent in $file on line $line";
}
?>
```

**Fix:**
- Ensure `session_start()` is first line after `<?php`
- Remove any whitespace before `<?php`
- Don't echo before starting session

### Issue 2: Session Data Not Persisting

**Check:**
1. Is `session_start()` called on both pages?
2. Are you testing in incognito/private mode? (Each window has separate session)
3. Did you call `session_destroy()` somewhere?

**Debug:**
```php
<?php
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";
echo "Session Data: ";
print_r($_SESSION);
?>
```

### Issue 3: Session Works Locally But Not on Server

**Check:**
1. Server permissions - can PHP write to session directory?
2. Server `session.save_path` setting
3. Are you on shared hosting with custom session paths?

**Test write permissions:**
```php
<?php
$sessionPath = session_save_path();
echo "Session path: $sessionPath<br>";
echo "Writable: " . (is_writable($sessionPath) ? 'Yes' : 'No');
?>
```

---

## Best Practices Summary

1. **Always start sessions first** - Before any output
2. **Use helper functions** - Cleaner, more maintainable code
3. **Check if keys exist** - Use `isset()` or `??` operator
4. **Validate session data** - Never trust blindly
5. **Use flash for one-time messages** - Cleaner UX
6. **Regenerate ID on privilege change** - Security
7. **Set secure cookie flags** - HttpOnly, Secure, SameSite
8. **Implement timeout** - Don't let sessions live forever
9. **Clear sensitive data** - Remove from session when done
10. **Test session expiration** - Ensure logout works properly

---

## What's Next?

You now know how to:
- Start and manage sessions
- Store and retrieve data
- Build multi-step forms
- Create flash messages
- Implement basic login state

**In Lesson 03**, we'll learn about **password hashing** - the critical foundation for secure authentication. Never store passwords in plain text!

After that, we'll build a real registration and login system that combines everything you've learned.

---

## Practice Challenge

Before moving to the next lesson, try building:

**A simple note-taking app** where:
- Users can add notes (stored in session)
- Notes persist across page refreshes
- Users can delete notes
- Clearing all notes requires confirmation

This will reinforce session concepts before we add authentication!

---

## Laravel Preview

**Now (Pure PHP):**
```php
<?php
session_start();
$_SESSION['key'] = 'value';
$value = $_SESSION['key'] ?? null;
?>
```

**Laravel:**
```php
<?php
session(['key' => 'value']);
$value = session('key');

// Flash message
session()->flash('success', 'It worked!');
$success = session('success');
?>
```

Laravel also provides:
- `old()` helper for form repopulation
- `redirect()->with()` for flash data
- Automatic CSRF protection
- Encrypted session storage

But the concepts are the same - you now understand what Laravel automates!
