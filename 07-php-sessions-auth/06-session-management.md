# Lesson 06 - Session Management: Managing Logged-In Users

**Duration**: 1 hour
**Objectives**: Learn to properly manage user sessions, implement timeouts, track activity, and handle edge cases

---

## What is Session Management?

Session management is everything that happens AFTER login:

- **Maintaining state** - Keeping users logged in across pages
- **Tracking activity** - Recording what users do
- **Timeout handling** - Logging out inactive users
- **Session security** - Preventing hijacking and fixation
- **User data access** - Getting current user information
- **Session validation** - Ensuring session is still valid

Think of it like a library card. Once you have it, the library needs to:
- Verify your card is valid
- Track what books you borrow
- Expire your card if unused for too long
- Cancel your card if something suspicious happens

---

## Session Lifecycle Management

### The Complete Session Lifecycle

```
1. User logs in
   ↓
2. Session created, ID generated
   ↓
3. User browses site (session active)
   ↓
4. Track last activity on each request
   ↓
5. Check timeout on each request
   ↓
6a. If timeout → Logout         6b. If active → Continue
   ↓                                ↓
7. Session destroyed            7. Update last activity
```

Let's implement each part.

---

## Step 1: Enhanced Session Helper

Expand our session helper with management functions:

```php
<?php
// includes/session.php (enhanced version)

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript access
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // HTTPS only in production
    ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
    ini_set('session.use_strict_mode', 1); // Reject uninitialized session IDs

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

    // Delete session cookie
    if (isset($_COOKIE['PHPSESSID'])) {
        setcookie('PHPSESSID', '', time() - 3600, '/');
    }

    session_destroy();
}

/**
 * Regenerate session ID (prevent fixation)
 */
function session_regenerate(): void
{
    session_regenerate_id(true);
}

/**
 * Initialize session activity tracking
 */
function session_init_activity(): void
{
    if (!session_has('session_started_at')) {
        session_set('session_started_at', time());
    }

    session_set('last_activity', time());
}

/**
 * Update last activity timestamp
 */
function session_update_activity(): void
{
    session_set('last_activity', time());
}

/**
 * Check if session has timed out
 *
 * @param int $timeout Timeout in seconds (default: 30 minutes)
 * @return bool True if session has timed out
 */
function session_has_timed_out(int $timeout = 1800): bool
{
    $lastActivity = session_get('last_activity');

    if ($lastActivity === null) {
        return true; // No activity recorded = invalid session
    }

    $elapsed = time() - $lastActivity;

    return $elapsed > $timeout;
}

/**
 * Get seconds since last activity
 */
function session_idle_time(): int
{
    $lastActivity = session_get('last_activity', time());
    return time() - $lastActivity;
}

/**
 * Get session age (time since creation)
 */
function session_age(): int
{
    $startedAt = session_get('session_started_at', time());
    return time() - $startedAt;
}

/**
 * Check if session is valid
 */
function session_is_valid(): bool
{
    // Check if session has required data
    if (!session_has('user_id')) {
        return false;
    }

    // Check timeout
    if (session_has_timed_out()) {
        return false;
    }

    // Check user agent (basic fingerprinting)
    $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $storedUserAgent = session_get('user_agent', '');

    if ($storedUserAgent !== '' && $currentUserAgent !== $storedUserAgent) {
        // User agent changed - possible session hijacking
        return false;
    }

    return true;
}

/**
 * Initialize session fingerprint
 */
function session_init_fingerprint(): void
{
    session_set('user_agent', $_SERVER['HTTP_USER_AGENT'] ?? '');
    session_set('ip_address', $_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * Verify session fingerprint
 */
function session_verify_fingerprint(): bool
{
    $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $storedUserAgent = session_get('user_agent', '');

    // Check user agent
    if ($storedUserAgent !== '' && $currentUserAgent !== $storedUserAgent) {
        return false;
    }

    // IP check is optional (can break with mobile networks)
    // Uncomment if you want strict IP checking:
    // $currentIp = $_SERVER['REMOTE_ADDR'] ?? '';
    // $storedIp = session_get('ip_address', '');
    // if ($storedIp !== '' && $currentIp !== $storedIp) {
    //     return false;
    // }

    return true;
}
?>
```

---

## Step 2: Enhanced Auth Functions

Update auth.php with session management:

```php
<?php
// includes/auth.php (add these functions)

/**
 * Log user in (create session)
 */
function loginUser(array $user): void
{
    // Regenerate session ID (prevent session fixation)
    session_regenerate();

    // Store user data in session
    session_set('user_id', $user['id']);
    session_set('username', $user['username']);
    session_set('email', $user['email']);
    session_set('logged_in', true);

    // Initialize activity tracking
    session_init_activity();

    // Store session fingerprint
    session_init_fingerprint();
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    // Must have logged_in flag
    if (!session_get('logged_in', false)) {
        return false;
    }

    // Session must be valid
    if (!session_is_valid()) {
        return false;
    }

    // Update activity
    session_update_activity();

    return true;
}

/**
 * Require user to be logged in
 */
function requireLogin(string $redirectTo = 'login.php'): void
{
    if (!isLoggedIn()) {
        // Save intended URL
        session_set('redirect_after_login', $_SERVER['REQUEST_URI']);

        // Redirect to login
        header("Location: $redirectTo");
        exit;
    }
}

/**
 * Log user out
 */
function logoutUser(): void
{
    session_end();
}

/**
 * Get current user from database
 */
function getCurrentUser(PDO $pdo): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $userId = session_get('user_id');

    $stmt = $pdo->prepare("
        SELECT id, username, email, created_at, last_login
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$userId]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If user not found (deleted?), logout
    if (!$user) {
        logoutUser();
        return null;
    }

    return $user;
}

/**
 * Refresh user session data from database
 */
function refreshUserSession(PDO $pdo): bool
{
    $user = getCurrentUser($pdo);

    if (!$user) {
        return false;
    }

    // Update session with fresh data
    session_set('username', $user['username']);
    session_set('email', $user['email']);

    return true;
}
?>
```

---

## Step 3: Session Timeout Implementation

Create a middleware-style function to check timeouts:

```php
<?php
// includes/middleware.php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';

/**
 * Check session timeout and logout if needed
 */
function checkSessionTimeout(int $timeout = 1800): void
{
    if (!session_has('user_id')) {
        return; // Not logged in
    }

    if (session_has_timed_out($timeout)) {
        // Session timed out
        session_end();

        // Redirect to login with message
        session_set('error', 'Your session has expired due to inactivity. Please log in again.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Protect page - require login and check timeout
 */
function protectPage(int $timeout = 1800, string $redirectTo = 'login.php'): void
{
    requireLogin($redirectTo);
    checkSessionTimeout($timeout);
}

/**
 * Check if user has permission
 */
function requirePermission(string $permission): void
{
    requireLogin();

    // Example: Check if user has permission
    // In real app, you'd check against database
    $userPermissions = session_get('permissions', []);

    if (!in_array($permission, $userPermissions)) {
        http_response_code(403);
        die('Access denied');
    }
}
?>
```

---

## Step 4: Using Session Management

Now use these functions in your pages:

### Protected Page Example

```php
<?php
// dashboard.php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../config/database.php';

// Protect page with 30-minute timeout
protectPage(1800);

// Get current user
$user = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>

    <p>Session info:</p>
    <ul>
        <li>Session age: <?= gmdate('H:i:s', session_age()) ?></li>
        <li>Idle time: <?= gmdate('H:i:s', session_idle_time()) ?></li>
        <li>Session ID: <?= session_id() ?></li>
    </ul>

    <a href="logout.php">Logout</a>
</body>
</html>
```

### Logout Page

```php
<?php
// logout.php
require_once __DIR__ . '/../includes/auth.php';

// Logout user
logoutUser();

// Redirect to login
session_set('success', 'You have been logged out successfully.');
header('Location: login.php');
exit;
?>
```

---

## Step 5: Activity Tracking

Track what users do for analytics and security:

```sql
-- database/schema.sql
CREATE TABLE user_activity (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

```php
<?php
// includes/activity.php

/**
 * Log user activity
 */
function logActivity(PDO $pdo, string $action, ?string $details = null): void
{
    $userId = session_get('user_id');

    if (!$userId) {
        return; // Not logged in
    }

    $stmt = $pdo->prepare("
        INSERT INTO user_activity (user_id, action, details, ip_address, user_agent, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $userId,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Get user activity history
 */
function getUserActivity(PDO $pdo, int $userId, int $limit = 50): array
{
    $stmt = $pdo->prepare("
        SELECT action, details, ip_address, created_at
        FROM user_activity
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");

    $stmt->execute([$userId, $limit]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
```

### Using Activity Tracking

```php
<?php
// Example: Track profile update
require_once 'includes/activity.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile...

    logActivity($pdo, 'profile_updated', 'Changed email address');
}

// Example: Track page view
logActivity($pdo, 'page_view', 'dashboard');
?>
```

---

## Step 6: Session Timeout Warning

Warn users before session expires:

```php
<?php
// In your protected pages
$timeoutSeconds = 1800; // 30 minutes
$idleTime = session_idle_time();
$remainingTime = $timeoutSeconds - $idleTime;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        #timeout-warning {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff9800;
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        #timeout-warning button {
            margin-top: 10px;
            padding: 8px 16px;
            background: white;
            color: #ff9800;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Timeout warning -->
    <div id="timeout-warning">
        <strong>Warning!</strong>
        <p>Your session will expire soon due to inactivity.</p>
        <button onclick="extendSession()">Stay Logged In</button>
    </div>

    <!-- Page content -->
    <h1>Dashboard</h1>

    <script>
        // Session timeout in seconds
        const timeoutSeconds = <?= $timeoutSeconds ?>;
        const warningSeconds = 300; // Show warning 5 minutes before timeout

        // Current idle time
        let idleSeconds = <?= $idleTime ?>;

        // Update idle time every second
        setInterval(() => {
            idleSeconds++;

            // Show warning
            if (idleSeconds >= (timeoutSeconds - warningSeconds) && idleSeconds < timeoutSeconds) {
                document.getElementById('timeout-warning').style.display = 'block';
            }

            // Auto-logout on timeout
            if (idleSeconds >= timeoutSeconds) {
                window.location.href = 'logout.php?reason=timeout';
            }
        }, 1000);

        // Reset idle time on any user activity
        let activityEvents = ['mousedown', 'keydown', 'scroll', 'touchstart'];
        activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                if (idleSeconds > 60) { // Only update if idle for more than 1 minute
                    fetch('refresh-session.php')
                        .then(() => {
                            idleSeconds = 0;
                            document.getElementById('timeout-warning').style.display = 'none';
                        });
                }
            });
        });

        // Extend session button
        function extendSession() {
            fetch('refresh-session.php')
                .then(() => {
                    idleSeconds = 0;
                    document.getElementById('timeout-warning').style.display = 'none';
                });
        }
    </script>
</body>
</html>
```

### Session Refresh Endpoint

```php
<?php
// refresh-session.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    die('Unauthorized');
}

// Update activity
session_update_activity();

// Return success
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
```

---

## Step 7: Multiple Device Management

Track and manage user sessions across devices:

```sql
-- database/schema.sql
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(128) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_session_id (session_id),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

```php
<?php
// includes/session-tracking.php

/**
 * Save session to database
 */
function saveSessionToDb(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE last_activity = NOW()
    ");

    $stmt->execute([
        $userId,
        session_id(),
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
}

/**
 * Get all active sessions for user
 */
function getUserSessions(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare("
        SELECT session_id, ip_address, user_agent, last_activity, created_at
        FROM user_sessions
        WHERE user_id = ?
        AND last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ORDER BY last_activity DESC
    ");

    $stmt->execute([$userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Logout all other sessions
 */
function logoutOtherSessions(PDO $pdo, int $userId): void
{
    $currentSessionId = session_id();

    $stmt = $pdo->prepare("
        DELETE FROM user_sessions
        WHERE user_id = ?
        AND session_id != ?
    ");

    $stmt->execute([$userId, $currentSessionId]);
}

/**
 * Logout specific session
 */
function logoutSession(PDO $pdo, int $userId, string $sessionId): void
{
    $stmt = $pdo->prepare("
        DELETE FROM user_sessions
        WHERE user_id = ?
        AND session_id = ?
    ");

    $stmt->execute([$userId, $sessionId]);
}
?>
```

---

## Security Best Practices

### 1. Always Regenerate Session ID on Login

```php
// After successful login
session_regenerate_id(true); // Delete old session
```

This prevents **session fixation** attacks.

### 2. Set Secure Cookie Flags

```php
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
```

### 3. Implement Session Fingerprinting

We already do this with user agent checking. This helps detect **session hijacking**.

### 4. Use Absolute Timeouts

Even with activity, logout after maximum time:

```php
$maxSessionAge = 86400; // 24 hours

if (session_age() > $maxSessionAge) {
    logoutUser();
    header('Location: login.php?reason=expired');
    exit;
}
```

### 5. Clean Up Old Sessions

```php
// Cron job or scheduled task
$stmt = $pdo->prepare("
    DELETE FROM user_sessions
    WHERE last_activity < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
");
$stmt->execute();
```

---

## Common Session Management Issues

### Issue 1: Session Lost on Redirect

**Symptom:** User logs in, redirects, but appears logged out

**Cause:** Session not saved before redirect

**Fix:**
```php
session_set('user_id', $userId);
session_write_close(); // Force save
header('Location: dashboard.php');
exit;
```

### Issue 2: Session Works Locally But Not on Server

**Causes:**
- Server permissions on session directory
- Shared hosting session conflicts
- Different PHP configuration

**Fix:**
```php
// Set custom session save path
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0700, true);
}
session_save_path($sessionPath);
```

### Issue 3: Users Logged Out Too Quickly

**Cause:** Timeout too short or not updating activity

**Fix:**
```php
// Update activity on every request
session_update_activity();
```

---

## What's Next?

You now understand complete session management:
- Activity tracking
- Timeout handling
- Session validation
- Multi-device management
- Security best practices

**In Lesson 07**, we'll learn about **middleware** - elegant ways to protect pages and check permissions.

**In Lesson 08**, we'll implement **"Remember Me"** for persistent login across browser sessions.

---

## Laravel Preview

**Now (Pure PHP):**
```php
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
session_update_activity();
```

**Laravel:**
```php
// In routes/web.php
Route::get('/dashboard', function() {
    // ...
})->middleware('auth');

// Laravel handles timeout, activity, regeneration automatically!
```

Laravel provides:
- `auth` middleware for protection
- Automatic session management
- Built-in activity tracking
- Configurable timeouts

But now you understand what Laravel's session middleware actually does!
