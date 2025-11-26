# Lesson 05 - Building a Complete Login System

**Duration**: 1.5 hours
**Objectives**: Build a secure login system with proper authentication, session management, and security measures

---

## What Makes a Good Login System?

A professional login system needs:

1. **Secure authentication** - Verify credentials properly
2. **Session management** - Track logged-in users
3. **Failed attempt tracking** - Prevent brute force
4. **Account lockout** - Temporary ban after too many failures
5. **Session regeneration** - Prevent session fixation
6. **Remember me** (optional) - Persistent login
7. **Clear error messages** - Without revealing too much
8. **Redirect after login** - Send to appropriate page

We'll build all of this step by step.

---

## The Login Flow

Understanding the complete flow helps you implement it correctly:

```
1. User visits login.php
   ↓
2. User enters username/email and password
   ↓
3. Form submits to server (POST)
   ↓
4. Server looks up user in database
   ↓
5. Server verifies password with password_verify()
   ↓
6a. If correct:                    6b. If wrong:
    - Regenerate session ID            - Increment failed attempts
    - Store user data in session       - Lock account if too many attempts
    - Redirect to dashboard            - Show error message
                                       - Stay on login page
```

---

## Step 1: Basic Login Form

Let's start with a clean, user-friendly login form:

```php
<?php
// public/login.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (session_has('user_id')) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize(post('username'));
    $password = post('password');

    $errors = [];

    // Basic validation
    if (empty($username)) {
        $errors[] = 'Username or email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        // Look up user by username OR email
        $stmt = $pdo->prepare("
            SELECT id, username, email, password
            FROM users
            WHERE username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify password
        if ($user && password_verify($password, $user['password'])) {
            // Login successful!

            // Regenerate session ID (prevent session fixation)
            session_regenerate_id(true);

            // Store user data in session
            session_set('user_id', $user['id']);
            session_set('username', $user['username']);
            session_set('email', $user['email']);
            session_set('logged_in', true);

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // Login failed
            $errors[] = 'Invalid username or password';
        }
    }

    // Save errors
    if (!empty($errors)) {
        session_set('errors', $errors);
        session_set('old_username', $username);
    }
}

// Get flash data
$errors = session_flash('errors', []);
$oldUsername = session_flash('old_username', '');
$success = session_flash('success'); // From registration
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            margin-bottom: 10px;
            color: #333;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .success {
            background: #e6ffe6;
            border-left: 4px solid #44ff44;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #006600;
        }

        .errors {
            background: #ffe6e6;
            border-left: 4px solid #ff4444;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .errors ul {
            list-style: none;
        }

        .errors li {
            color: #cc0000;
            padding: 5px 0;
        }

        .errors li:before {
            content: "⚠ ";
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        .forgot-password {
            text-align: right;
            margin-top: 5px;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome Back</h1>
        <p class="subtitle">Log in to your account</p>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($oldUsername) ?>"
                    required
                    autocomplete="username"
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
            </div>

            <button type="submit" class="btn">Log In</button>
        </form>

        <div class="footer">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
    </div>
</body>
</html>
```

---

## Step 2: Security Enhancement - Failed Login Tracking

We need to track failed login attempts to prevent brute force attacks.

### Add columns to users table:

```sql
-- Add to database/schema.sql
ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0;
ALTER TABLE users ADD COLUMN account_locked_until TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL;
```

### Create authentication helper functions:

```php
<?php
// includes/auth.php

/**
 * Attempt to log in a user
 */
function attemptLogin(PDO $pdo, string $username, string $password): array
{
    // Get user with lock status
    $stmt = $pdo->prepare("
        SELECT id, username, email, password, failed_login_attempts, account_locked_until
        FROM users
        WHERE username = ? OR email = ?
    ");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    // Check if account is locked
    if ($user['account_locked_until']) {
        $lockExpires = strtotime($user['account_locked_until']);

        if (time() < $lockExpires) {
            $minutesLeft = ceil(($lockExpires - time()) / 60);
            return [
                'success' => false,
                'error' => "Account locked. Try again in $minutesLeft minutes.",
                'locked' => true
            ];
        } else {
            // Lock expired, reset
            unlockAccount($pdo, $user['id']);
        }
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Wrong password - increment failed attempts
        incrementFailedAttempts($pdo, $user['id']);

        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    // Login successful!

    // Check if password needs rehashing
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $user['id']]);
    }

    // Reset failed attempts
    resetFailedAttempts($pdo, $user['id']);

    // Update last login
    updateLastLogin($pdo, $user['id']);

    return [
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ];
}

/**
 * Increment failed login attempts
 */
function incrementFailedAttempts(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET failed_login_attempts = failed_login_attempts + 1
        WHERE id = ?
    ");
    $stmt->execute([$userId]);

    // Lock account if too many attempts
    $stmt = $pdo->prepare("SELECT failed_login_attempts FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 5) {
        lockAccount($pdo, $userId, 30); // Lock for 30 minutes
    }
}

/**
 * Lock account for specified minutes
 */
function lockAccount(PDO $pdo, int $userId, int $minutes): void
{
    $lockUntil = date('Y-m-d H:i:s', time() + ($minutes * 60));

    $stmt = $pdo->prepare("
        UPDATE users
        SET account_locked_until = ?
        WHERE id = ?
    ");
    $stmt->execute([$lockUntil, $userId]);
}

/**
 * Unlock account
 */
function unlockAccount(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET account_locked_until = NULL,
            failed_login_attempts = 0
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Reset failed login attempts
 */
function resetFailedAttempts(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET failed_login_attempts = 0,
            account_locked_until = NULL
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Update last login timestamp
 */
function updateLastLogin(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET last_login = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
}

/**
 * Log user in (create session)
 */
function loginUser(array $user): void
{
    // Regenerate session ID (prevent session fixation)
    session_regenerate_id(true);

    // Store user data in session
    session_set('user_id', $user['id']);
    session_set('username', $user['username']);
    session_set('email', $user['email']);
    session_set('logged_in', true);
    session_set('login_time', time());
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return session_get('logged_in', false) === true;
}

/**
 * Get current logged-in user ID
 */
function currentUserId(): ?int
{
    return session_get('user_id');
}

/**
 * Get current logged-in username
 */
function currentUsername(): ?string
{
    return session_get('username');
}

/**
 * Require user to be logged in
 */
function requireLogin(string $redirectTo = 'login.php'): void
{
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit;
    }
}
?>
```

---

## Step 3: Updated Login with Security Features

Now update `login.php` to use the auth helpers:

```php
<?php
// public/login.php (updated with security features)
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize(post('username'));
    $password = post('password');

    $errors = [];

    // Basic validation
    if (empty($username)) {
        $errors[] = 'Username or email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        // Attempt login
        $result = attemptLogin($pdo, $username, $password);

        if ($result['success']) {
            // Login successful
            loginUser($result['user']);

            // Redirect to intended page or dashboard
            $redirectTo = session_get('redirect_after_login', 'dashboard.php');
            session_remove('redirect_after_login');

            header("Location: $redirectTo");
            exit;
        } else {
            // Login failed
            $errors[] = $result['error'];
        }
    }

    // Save errors
    if (!empty($errors)) {
        session_set('errors', $errors);
        session_set('old_username', $username);
    }
}

// Get flash data
$errors = session_flash('errors', []);
$oldUsername = session_flash('old_username', '');
$success = session_flash('success');
?>
<!-- HTML stays the same as before -->
```

---

## Step 4: Protected Pages (Dashboard)

Now create a dashboard that only logged-in users can access:

```php
<?php
// public/dashboard.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Require login
requireLogin();

// Get current user data
$userId = currentUserId();

// Fetch additional user data from database
$stmt = $pdo->prepare("
    SELECT username, email, created_at, last_login
    FROM users
    WHERE id = ?
");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: white;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar h1 {
            color: #333;
        }

        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar .username {
            color: #666;
        }

        .navbar a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .user-details {
            display: grid;
            gap: 15px;
        }

        .user-detail {
            display: flex;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }

        .user-detail .label {
            font-weight: 600;
            width: 150px;
            color: #666;
        }

        .user-detail .value {
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>Dashboard</h1>
        <div class="user-info">
            <span class="username">Welcome, <?= htmlspecialchars($user['username']) ?>!</span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Your Account Information</h2>
            <div class="user-details">
                <div class="user-detail">
                    <div class="label">Username:</div>
                    <div class="value"><?= htmlspecialchars($user['username']) ?></div>
                </div>

                <div class="user-detail">
                    <div class="label">Email:</div>
                    <div class="value"><?= htmlspecialchars($user['email']) ?></div>
                </div>

                <div class="user-detail">
                    <div class="label">Member Since:</div>
                    <div class="value"><?= date('F j, Y', strtotime($user['created_at'])) ?></div>
                </div>

                <?php if ($user['last_login']): ?>
                    <div class="user-detail">
                        <div class="label">Last Login:</div>
                        <div class="value"><?= date('F j, Y g:i A', strtotime($user['last_login'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>Session Information</h2>
            <div class="user-details">
                <div class="user-detail">
                    <div class="label">Session ID:</div>
                    <div class="value"><?= session_id() ?></div>
                </div>

                <div class="user-detail">
                    <div class="label">Logged in for:</div>
                    <div class="value">
                        <?php
                        $loginTime = session_get('login_time', time());
                        $elapsed = time() - $loginTime;
                        $minutes = floor($elapsed / 60);
                        $seconds = $elapsed % 60;
                        echo "$minutes minutes, $seconds seconds";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
```

---

## Step 5: Redirect to Intended Page

If a user tries to access a protected page, log them in, then send them back:

```php
<?php
// protected-page.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    // Save current URL
    session_set('redirect_after_login', $_SERVER['REQUEST_URI']);

    header('Location: login.php');
    exit;
}

// User is logged in, show page
echo "This is a protected page!";
?>
```

After login, they'll be sent back to `protected-page.php` automatically!

---

## Step 6: Login Security Best Practices

### 1. Don't Reveal Which Part Was Wrong

**Bad:**
```php
if (!$user) {
    echo "Username doesn't exist";
} elseif (!password_verify($password, $user['password'])) {
    echo "Password is incorrect";
}
```

This tells attackers which usernames exist!

**Good:**
```php
if (!$user || !password_verify($password, $user['password'])) {
    echo "Invalid credentials";
}
```

Generic message for both cases.

### 2. Use Timing-Safe Comparison

Our `attemptLogin()` function is already timing-safe because `password_verify()` is constant-time.

### 3. Rate Limit Login Attempts

We already lock accounts after 5 failed attempts. For additional protection, add IP-based rate limiting:

```php
function checkIpRateLimit(PDO $pdo, string $ip): bool
{
    // Create login_attempts table first
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts
        WHERE ip_address = ?
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$ip]);
    $attempts = $stmt->fetchColumn();

    return $attempts < 10; // Max 10 attempts per IP in 15 minutes
}

function logLoginAttempt(PDO $pdo, string $ip, string $username, bool $success): void
{
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (ip_address, username, success, attempted_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$ip, $username, $success ? 1 : 0]);
}
```

Create the table:
```sql
CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50) NOT NULL,
    success BOOLEAN DEFAULT FALSE,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_ip_time (ip_address, attempted_at)
);
```

### 4. Use HTTPS in Production

Never transmit passwords over HTTP!

```php
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (php_sapi_name() !== 'cli') {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirect");
        exit;
    }
}
```

### 5. Implement CSRF Protection

We'll cover this in Module 08, but here's a preview:

```php
// Generate token
function csrf_token(): string
{
    if (!session_has('csrf_token')) {
        session_set('csrf_token', bin2hex(random_bytes(32)));
    }
    return session_get('csrf_token');
}

// Verify token
function verify_csrf_token(string $token): bool
{
    return hash_equals(session_get('csrf_token', ''), $token);
}
```

---

## Common Login Problems

### Problem 1: Session not persisting

**Symptom:** User logs in, redirects to dashboard, but appears logged out

**Causes:**
1. `session_start()` not called
2. Cookies disabled in browser
3. Session ID changing unexpectedly

**Debug:**
```php
echo "Session ID: " . session_id() . "<br>";
echo "User ID: " . session_get('user_id') . "<br>";
print_r($_SESSION);
```

### Problem 2: Account stays locked forever

**Cause:** `account_locked_until` comparison issue

**Fix:** Check in `attemptLogin()`:
```php
if ($user['account_locked_until'] && time() < strtotime($user['account_locked_until'])) {
    // Still locked
}
```

### Problem 3: Password always wrong

**Causes:**
1. Password not hashed during registration
2. Comparing plain text passwords
3. Extra whitespace in password

**Debug:**
```php
echo "Input: $password<br>";
echo "Hash: " . $user['password'] . "<br>";
echo "Verify: " . (password_verify($password, $user['password']) ? 'true' : 'false');
```

---

## Testing Your Login System

Create a test suite:

```php
<?php
// test-login.php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Test 1: Login with correct credentials
$result = attemptLogin($pdo, 'testuser', 'TestPassword123!');
assert($result['success'] === true, "Valid login should succeed");

// Test 2: Login with wrong password
$result = attemptLogin($pdo, 'testuser', 'wrongpassword');
assert($result['success'] === false, "Wrong password should fail");

// Test 3: Login with non-existent user
$result = attemptLogin($pdo, 'nonexistent', 'password');
assert($result['success'] === false, "Non-existent user should fail");

// Test 4: Check account lockout (simulate 5 failed attempts)
for ($i = 0; $i < 5; $i++) {
    attemptLogin($pdo, 'testuser', 'wrongpassword');
}
$result = attemptLogin($pdo, 'testuser', 'TestPassword123!');
assert(isset($result['locked']) && $result['locked'] === true, "Account should be locked");

echo "All login tests passed!\n";
?>
```

---

## What's Next?

You now have a complete, secure login system with:
- Password verification
- Session management
- Failed attempt tracking
- Account lockout
- Security best practices

**In Lesson 06**, we'll learn about **session management**:
- Checking authentication status
- Getting current user data
- Session timeout
- Activity tracking

**In Lesson 07**, we'll implement **middleware** to protect pages easily.

**In Lesson 08**, we'll add **"Remember Me"** functionality for persistent login.

---

## Laravel Preview

**Now (Pure PHP):**
```php
$result = attemptLogin($pdo, $username, $password);
if ($result['success']) {
    loginUser($result['user']);
}
```

**Laravel:**
```php
use Illuminate\Support\Facades\Auth;

if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Login successful
}
```

Laravel also provides:
- Built-in throttling (rate limiting)
- Automatic session management
- Remember me functionality
- Password reset flows

But now you understand what `Auth::attempt()` actually does behind the scenes!
