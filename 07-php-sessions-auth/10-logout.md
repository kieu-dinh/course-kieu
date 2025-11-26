# Lesson 10 - Proper Logout Handling

**Duration**: 45 minutes
**Objectives**: Implement secure logout that properly cleans up sessions, cookies, and tokens

---

## Why Logout Matters

Logout seems simple - just destroy the session, right? But incomplete logout can lead to:

- **Session data persisting** - User appears logged out but data remains
- **Security vulnerabilities** - Session hijacking if not properly cleared
- **Remember-me still active** - User "logs out" but auto-logs back in
- **Cross-device issues** - Logout on one device but still logged in elsewhere

A proper logout must clean up EVERYTHING.

---

## What Needs to Be Cleaned

When a user logs out, we need to:

1. **Clear session variables** - Remove user data from `$_SESSION`
2. **Destroy session file** - Delete the server-side session file
3. **Delete session cookie** - Remove PHPSESSID cookie from browser
4. **Delete remember-me cookie** - Remove persistent login cookie
5. **Revoke remember-me tokens** - Delete tokens from database
6. **Log the action** - Track logout for security audit
7. **Clear any cached data** - Remove temporary data

Missing any of these can leave security holes.

---

## Step 1: Basic Logout

Let's start with a minimal logout:

```php
<?php
// public/logout.php - INCOMPLETE! Don't use this version.
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
```

**Problems with this:**
- Session variables still accessible in `$_SESSION`
- Session cookie still exists in browser
- Remember-me token still active
- No cleanup or logging

---

## Step 2: Proper Logout Implementation

Here's the complete, secure logout:

```php
<?php
// public/logout.php - COMPLETE VERSION
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../includes/activity.php';
require_once __DIR__ . '/../config/database.php';

// 1. Log the logout action BEFORE destroying session
if (session_has('user_id')) {
    logActivity($pdo, 'logout', 'User logged out');
}

// 2. Get user ID before clearing session
$userId = session_get('user_id');

// 3. Clear all session variables
$_SESSION = [];

// 4. Delete session cookie from browser
if (isset($_COOKIE['PHPSESSID'])) {
    setcookie(
        'PHPSESSID',
        '',
        time() - 3600,        // Expire in the past
        '/',                  // Path
        '',                   // Domain
        isset($_SERVER['HTTPS']), // Secure
        true                  // HttpOnly
    );
}

// 5. Destroy session file on server
session_destroy();

// 6. Delete remember-me cookie
if (isset($_COOKIE['remember_me'])) {
    clearRememberCookie();
}

// 7. Revoke all remember-me tokens for this user
if ($userId) {
    deleteAllUserTokens($pdo, $userId);
}

// 8. Set success message (creates new session automatically)
session_set('success', 'You have been logged out successfully.');

// 9. Redirect to login page
header('Location: login.php');
exit;
?>
```

**This logout:**
- ✅ Clears session variables
- ✅ Destroys session file
- ✅ Deletes session cookie
- ✅ Removes remember-me cookie
- ✅ Revokes database tokens
- ✅ Logs the action
- ✅ Shows confirmation message

---

## Step 3: Logout Options

Different logout behaviors for different needs:

### Option 1: Logout Current Device Only

```php
<?php
// logout.php - Logout current device only
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

$userId = session_get('user_id');

// Clear current session
session_end();

// Delete current remember-me token only
if (isset($_COOKIE['remember_me'])) {
    $cookieValue = $_COOKIE['remember_me'];
    $parts = explode(':', $cookieValue);

    if (count($parts) === 2) {
        $selector = $parts[0];

        // Delete only this token
        $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ? AND selector = ?");
        $stmt->execute([$userId, $selector]);
    }

    clearRememberCookie();
}

session_set('success', 'Logged out from this device.');
header('Location: login.php');
exit;
?>
```

### Option 2: Logout All Devices

```php
<?php
// logout-all.php - Logout from ALL devices
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

$userId = session_get('user_id');

// Clear current session
session_end();

// Delete ALL remember-me tokens for this user
deleteAllUserTokens($pdo, $userId);

// Clear current cookie
clearRememberCookie();

session_set('success', 'Logged out from all devices.');
header('Location: login.php');
exit;
?>
```

### Option 3: Logout with Confirmation

```php
<?php
// logout.php - With confirmation
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/middleware.php';

requireLogin();

// If not confirmed, show confirmation page
if (!isset($_POST['confirm'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Confirm Logout</title>
        <style>
            body {
                font-family: sans-serif;
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                background: #f5f5f5;
                margin: 0;
            }
            .box {
                background: white;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                text-align: center;
                max-width: 400px;
            }
            h1 { margin-bottom: 20px; color: #333; }
            p { color: #666; margin-bottom: 30px; }
            button {
                padding: 12px 24px;
                margin: 0 10px;
                border: none;
                border-radius: 6px;
                font-weight: 600;
                cursor: pointer;
            }
            .btn-logout {
                background: #ff4444;
                color: white;
            }
            .btn-cancel {
                background: #e0e0e0;
                color: #333;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>Confirm Logout</h1>
            <p>Are you sure you want to log out?</p>
            <form method="POST">
                <button type="submit" name="confirm" value="1" class="btn-logout">Yes, Logout</button>
                <button type="button" onclick="history.back()" class="btn-cancel">Cancel</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// User confirmed, proceed with logout
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

$userId = session_get('user_id');

session_end();
clearRememberCookie();

if ($userId) {
    deleteAllUserTokens($pdo, $userId);
}

session_set('success', 'You have been logged out.');
header('Location: login.php');
exit;
?>
```

---

## Step 4: Auto-Logout Features

### Auto-Logout on Timeout

```php
<?php
// includes/middleware.php

/**
 * Check for session timeout and auto-logout
 */
function checkAutoLogout(int $timeout = 1800): void
{
    if (!session_has('user_id')) {
        return;
    }

    if (session_has_timed_out($timeout)) {
        // Session expired
        $userId = session_get('user_id');

        // Log the auto-logout
        global $pdo;
        logActivity($pdo, 'auto_logout', 'Session timed out');

        // Clear everything
        session_end();
        clearRememberCookie();

        if ($userId) {
            deleteAllUserTokens($pdo, $userId);
        }

        // Redirect with timeout message
        session_set('error', 'Your session expired due to inactivity. Please log in again.');
        header('Location: login.php?timeout=1');
        exit;
    }
}
?>
```

### Auto-Logout on Suspicious Activity

```php
<?php
// includes/security.php

/**
 * Check for suspicious activity and force logout
 */
function checkSuspiciousActivity(): void
{
    if (!session_has('user_id')) {
        return;
    }

    // Check if user agent changed (possible session hijacking)
    $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $storedUserAgent = session_get('user_agent', '');

    if ($storedUserAgent !== '' && $currentUserAgent !== $storedUserAgent) {
        // User agent changed - force logout
        $userId = session_get('user_id');

        global $pdo;
        logActivity($pdo, 'forced_logout', 'User agent mismatch - possible session hijacking');

        // Clear everything
        session_end();
        clearRememberCookie();

        if ($userId) {
            deleteAllUserTokens($pdo, $userId);
        }

        session_set('error', 'Security alert: Your session was terminated. Please log in again.');
        header('Location: login.php?security=1');
        exit;
    }
}
?>
```

---

## Step 5: Logout with Redirect Options

Allow logout and redirect to specific pages:

```php
<?php
// logout.php - With redirect option
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

$userId = session_get('user_id');

// Get redirect parameter
$redirect = $_GET['redirect'] ?? 'login.php';

// Whitelist allowed redirects (security!)
$allowedRedirects = [
    'login.php',
    'index.php',
    'about.php',
    'contact.php'
];

if (!in_array($redirect, $allowedRedirects)) {
    $redirect = 'login.php';
}

// Perform logout
if ($userId) {
    logActivity($pdo, 'logout', 'User logged out');
}

session_end();
clearRememberCookie();

if ($userId) {
    deleteAllUserTokens($pdo, $userId);
}

session_set('success', 'You have been logged out.');
header("Location: $redirect");
exit;
?>
```

Usage:
```html
<a href="logout.php">Logout</a>
<a href="logout.php?redirect=index.php">Logout and go home</a>
```

---

## Step 6: AJAX Logout

For single-page applications:

```php
<?php
// api/logout.php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

// Check if logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$userId = session_get('user_id');

// Log the action
logActivity($pdo, 'logout', 'User logged out via API');

// Clear session
session_end();
clearRememberCookie();

// Delete tokens
if ($userId) {
    deleteAllUserTokens($pdo, $userId);
}

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>
```

JavaScript usage:
```javascript
async function logout() {
    const response = await fetch('/api/logout.php');
    const data = await response.json();

    if (data.success) {
        window.location.href = '/login.php';
    } else {
        alert('Logout failed: ' + data.error);
    }
}
```

---

## Step 7: Logout Everywhere (Admin Feature)

Allow admins to force logout a user:

```php
<?php
// admin/force-logout.php
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

admin(); // Require admin permission

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetUserId = (int)$_POST['user_id'];

    // Delete all sessions for this user (if you store sessions in DB)
    $stmt = $pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->execute([$targetUserId]);

    // Delete all remember-me tokens
    deleteAllUserTokens($pdo, $targetUserId);

    // Log admin action
    logActivity($pdo, 'admin_force_logout', "Forced logout user ID: $targetUserId");

    session_set('success', 'User has been logged out from all devices.');
    header('Location: users.php');
    exit;
}
?>
```

---

## Security Best Practices

### 1. Always Clear Everything

```php
// GOOD - Complete cleanup
session_end();
clearRememberCookie();
deleteAllUserTokens($pdo, $userId);

// BAD - Incomplete
session_destroy();
// Forgot cookies and tokens!
```

### 2. Log Logout Actions

```php
// Before destroying session
logActivity($pdo, 'logout', 'User logged out');
```

This helps detect suspicious patterns (e.g., multiple rapid logouts).

### 3. Whitelist Redirects

```php
// GOOD - Whitelist
$allowed = ['login.php', 'index.php'];
if (!in_array($redirect, $allowed)) {
    $redirect = 'login.php';
}

// BAD - Open redirect vulnerability
header("Location: " . $_GET['redirect']); // Attacker can redirect anywhere!
```

### 4. Use POST for Logout

```php
// GOOD - POST request (CSRF protected)
<form method="POST" action="logout.php">
    <button type="submit">Logout</button>
</form>

// ACCEPTABLE - GET with CSRF token
<a href="logout.php?token=<?= csrf_token() ?>">Logout</a>

// BAD - Simple GET without protection
<a href="logout.php">Logout</a> // CSRF vulnerability!
```

### 5. Show Confirmation

For sensitive actions, always confirm:

```php
if (!isset($_POST['confirm'])) {
    // Show confirmation page
} else {
    // Proceed with logout
}
```

---

## Common Issues

### Issue 1: "Already logged in" after logout

**Symptom:** User logs out but still appears logged in

**Causes:**
- Remember-me cookie not deleted
- Session cookie not deleted
- Browser cached the page

**Fix:**
```php
// Make sure to clear both cookies
clearRememberCookie();
setcookie('PHPSESSID', '', time() - 3600, '/');

// Add no-cache headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
```

### Issue 2: Logout loops

**Symptom:** Clicking logout keeps redirecting back

**Cause:** Session creation in redirect

**Fix:**
```php
// Clear session BEFORE redirect
session_end();

// Don't start new session before redirect
// BAD:
session_set('success', 'Logged out'); // This starts a new session!
header('Location: login.php');
```

### Issue 3: Logout doesn't work on some pages

**Cause:** Logout script not properly included

**Fix:** Use consistent logout URL:
```php
// In all pages
<a href="/logout.php">Logout</a>
// Not: <a href="logout.php">Logout</a>
// Not: <a href="../logout.php">Logout</a>
```

---

## Testing Checklist

Test your logout implementation:

- [ ] Session variables cleared
- [ ] Session file deleted from server
- [ ] Session cookie removed from browser
- [ ] Remember-me cookie removed
- [ ] Remember-me tokens deleted from database
- [ ] Logout action logged
- [ ] Cannot access protected pages after logout
- [ ] Auto-login doesn't work after logout
- [ ] Success message shown
- [ ] Works on all browsers

---

## What's Next?

You now understand proper logout:
- Complete cleanup process
- Different logout options
- Auto-logout features
- Security considerations

**In Lesson 11 (Final Lesson)**, we'll review **complete authentication security** - covering all common vulnerabilities and how to prevent them. This brings everything together!

---

## Laravel Preview

**Now (Pure PHP):**
```php
session_end();
clearRememberCookie();
deleteAllUserTokens($pdo, $userId);
```

**Laravel:**
```php
use Illuminate\Support\Facades\Auth;

Auth::logout();

// Laravel handles everything automatically:
// - Session cleanup
// - Cookie removal
// - Remember token deletion
```

But now you understand what `Auth::logout()` does behind the scenes!
