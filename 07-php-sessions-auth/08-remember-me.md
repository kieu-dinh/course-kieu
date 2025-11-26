# Lesson 08 - Remember Me: Persistent Login

**Duration**: 1.5 hours
**Objectives**: Implement secure "Remember Me" functionality for persistent login across browser sessions

---

## What is "Remember Me"?

**Problem:** Sessions expire when you close your browser. Users must log in again every time.

**Solution:** "Remember Me" creates a long-lived login that persists across browser sessions, so users stay logged in for days or weeks.

**Real-world example:** Gmail, Facebook, Amazon - they remember you even after closing the browser.

---

## How "Remember Me" Works

### The Flow

```
1. User logs in and checks "Remember Me"
   ↓
2. Server generates a unique, random token
   ↓
3. Server stores token in database with user_id and expiration
   ↓
4. Server sends token to browser as a long-lived cookie
   ↓
5. User closes browser (session ends)
   ↓
6. User returns to site
   ↓
7. Server checks for remember-me cookie
   ↓
8. If valid token found, auto-login user
   ↓
9. Regenerate token for security
```

### Security Considerations

**What NOT to do (DANGEROUS!):**

```php
// NEVER DO THIS!
setcookie('remember_user', $userId, time() + (30 * 86400));

// Attacker can just change the cookie:
// document.cookie = "remember_user=1" // Now they're admin!
```

**Problems:**
- User ID is predictable
- Anyone can modify cookies
- No way to revoke access
- Can't track which device

**The Right Way:**
- Generate cryptographically random tokens
- Store tokens in database
- Hash tokens before storing
- Allow users to revoke tokens
- Track which device has which token
- Regenerate tokens periodically

---

## Database Schema

```sql
-- database/schema.sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(64) NOT NULL,  -- SHA-256 hash of token
    selector VARCHAR(32) NOT NULL,     -- Identifies the token without revealing it
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Device info for user to see
    device_name VARCHAR(255) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    last_used TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_selector (selector),
    INDEX idx_expires (expires_at),

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Why selector + token?**

Instead of querying by token (which would require hashing every token in DB), we use a two-part system:
- **Selector**: Identifies which token (not secret, stored in plain text)
- **Token**: The secret part (hashed in database)

Cookie stores: `selector:token`

---

## Step 1: Token Generation

```php
<?php
// includes/remember-me.php

/**
 * Generate a secure remember-me token
 *
 * @return array ['selector' => string, 'token' => string, 'hash' => string]
 */
function generateRememberToken(): array
{
    // Selector: Used to find the token in database (16 bytes = 32 hex chars)
    $selector = bin2hex(random_bytes(16));

    // Token: The secret (32 bytes = 64 hex chars)
    $token = bin2hex(random_bytes(32));

    // Hash: What we store in database
    $hash = hash('sha256', $token);

    return [
        'selector' => $selector,
        'token' => $token,
        'hash' => $hash
    ];
}

/**
 * Create remember-me token for user
 *
 * @param PDO $pdo
 * @param int $userId
 * @param int $days How many days the token should last
 * @return string The cookie value (selector:token)
 */
function createRememberToken(PDO $pdo, int $userId, int $days = 30): string
{
    $tokenData = generateRememberToken();

    // Calculate expiration
    $expiresAt = date('Y-m-d H:i:s', time() + ($days * 86400));

    // Store in database
    $stmt = $pdo->prepare("
        INSERT INTO remember_tokens (user_id, token_hash, selector, expires_at, device_name, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $deviceName = getDeviceName($_SERVER['HTTP_USER_AGENT'] ?? '');

    $stmt->execute([
        $userId,
        $tokenData['hash'],
        $tokenData['selector'],
        $expiresAt,
        $deviceName,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);

    // Return cookie value: selector:token
    return $tokenData['selector'] . ':' . $tokenData['token'];
}

/**
 * Parse device name from user agent
 */
function getDeviceName(string $userAgent): string
{
    if (stripos($userAgent, 'iPhone') !== false) {
        return 'iPhone';
    } elseif (stripos($userAgent, 'iPad') !== false) {
        return 'iPad';
    } elseif (stripos($userAgent, 'Android') !== false) {
        return 'Android Device';
    } elseif (stripos($userAgent, 'Macintosh') !== false) {
        return 'Mac';
    } elseif (stripos($userAgent, 'Windows') !== false) {
        return 'Windows PC';
    } elseif (stripos($userAgent, 'Linux') !== false) {
        return 'Linux';
    } else {
        return 'Unknown Device';
    }
}

/**
 * Set remember-me cookie in browser
 *
 * @param string $cookieValue The selector:token string
 * @param int $days How many days the cookie should last
 */
function setRememberCookie(string $cookieValue, int $days = 30): void
{
    $expires = time() + ($days * 86400);

    setcookie(
        'remember_me',           // Cookie name
        $cookieValue,            // Value: selector:token
        $expires,                // Expiration
        '/',                     // Path
        '',                      // Domain (empty = current domain)
        isset($_SERVER['HTTPS']),// Secure (HTTPS only)
        true                     // HttpOnly (no JavaScript access)
    );
}

/**
 * Delete remember-me cookie
 */
function clearRememberCookie(): void
{
    setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}
?>
```

---

## Step 2: Auto-Login with Token

```php
<?php
// includes/remember-me.php (continued)

/**
 * Attempt to log in user with remember-me token
 *
 * @param PDO $pdo
 * @return array|null User data if successful, null otherwise
 */
function attemptRememberLogin(PDO $pdo): ?array
{
    // Check if remember-me cookie exists
    if (!isset($_COOKIE['remember_me'])) {
        return null;
    }

    $cookieValue = $_COOKIE['remember_me'];

    // Parse selector:token
    $parts = explode(':', $cookieValue);

    if (count($parts) !== 2) {
        // Invalid format
        clearRememberCookie();
        return null;
    }

    [$selector, $token] = $parts;

    // Find token in database by selector
    $stmt = $pdo->prepare("
        SELECT rt.id, rt.user_id, rt.token_hash, rt.expires_at,
               u.id, u.username, u.email
        FROM remember_tokens rt
        JOIN users u ON rt.user_id = u.id
        WHERE rt.selector = ?
    ");

    $stmt->execute([$selector]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        // Token not found
        clearRememberCookie();
        return null;
    }

    // Check if token expired
    if (strtotime($record['expires_at']) < time()) {
        // Token expired - delete it
        deleteRememberToken($pdo, $record['id']);
        clearRememberCookie();
        return null;
    }

    // Verify token
    $tokenHash = hash('sha256', $token);

    if (!hash_equals($record['token_hash'], $tokenHash)) {
        // Token doesn't match - possible attack!
        // Delete ALL tokens for this user (safety measure)
        deleteAllUserTokens($pdo, $record['user_id']);
        clearRememberCookie();
        return null;
    }

    // Token is valid!

    // Update last used timestamp
    $stmt = $pdo->prepare("UPDATE remember_tokens SET last_used = NOW() WHERE id = ?");
    $stmt->execute([$record['id']]);

    // For security, regenerate the token
    // This prevents token reuse if cookie is stolen
    deleteRememberToken($pdo, $record['id']);
    $newCookieValue = createRememberToken($pdo, $record['user_id']);
    setRememberCookie($newCookieValue);

    // Return user data
    return [
        'id' => $record['user_id'],
        'username' => $record['username'],
        'email' => $record['email']
    ];
}

/**
 * Delete a specific remember-me token
 */
function deleteRememberToken(PDO $pdo, int $tokenId): void
{
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE id = ?");
    $stmt->execute([$tokenId]);
}

/**
 * Delete all remember-me tokens for a user
 */
function deleteAllUserTokens(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->execute([$userId]);
}

/**
 * Delete expired tokens (run periodically)
 */
function cleanupExpiredTokens(PDO $pdo): int
{
    $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE expires_at < NOW()");
    $stmt->execute();
    return $stmt->rowCount();
}
?>
```

---

## Step 3: Update Login Page

```php
<?php
// public/login.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

// Check for remember-me auto-login BEFORE showing login form
$rememberedUser = attemptRememberLogin($pdo);

if ($rememberedUser) {
    // Auto-login successful!
    loginUser($rememberedUser);
    header('Location: dashboard.php');
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize(post('username'));
    $password = post('password');
    $rememberMe = isset($_POST['remember_me']); // Checkbox state

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username or email is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        $result = attemptLogin($pdo, $username, $password);

        if ($result['success']) {
            // Login successful
            loginUser($result['user']);

            // Handle "Remember Me"
            if ($rememberMe) {
                $cookieValue = createRememberToken($pdo, $result['user']['id'], 30); // 30 days
                setRememberCookie($cookieValue, 30);
            }

            // Redirect
            $redirectTo = session_get('redirect_after_login', 'dashboard.php');
            session_remove('redirect_after_login');

            header("Location: $redirectTo");
            exit;
        } else {
            $errors[] = $result['error'];
        }
    }

    if (!empty($errors)) {
        session_set('errors', $errors);
        session_set('old_username', $username);
    }
}

$errors = session_flash('errors', []);
$oldUsername = session_flash('old_username', '');
$success = session_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* ... existing styles ... */
        .checkbox-group {
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 8px;
        }
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
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

            <!-- Remember Me checkbox -->
            <div class="checkbox-group">
                <input type="checkbox" id="remember_me" name="remember_me" value="1">
                <label for="remember_me">Remember me for 30 days</label>
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

## Step 4: Update Logout

When logging out, delete remember-me tokens:

```php
<?php
// public/logout.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/remember-me.php';
require_once __DIR__ . '/../config/database.php';

// Get user ID before destroying session
$userId = session_get('user_id');

// Delete all remember-me tokens for this user
if ($userId) {
    deleteAllUserTokens($pdo, $userId);
}

// Clear remember-me cookie
clearRememberCookie();

// Logout user (destroy session)
logoutUser();

// Redirect
session_set('success', 'You have been logged out successfully.');
header('Location: login.php');
exit;
?>
```

---

## Step 5: Manage Remember-Me Tokens

Let users see and revoke their active tokens:

```php
<?php
// public/settings/sessions.php
require_once __DIR__ . '/../../includes/middleware.php';
require_once __DIR__ . '/../../includes/remember-me.php';
require_once __DIR__ . '/../../config/database.php';

protectPage();

$userId = session_get('user_id');

// Handle token deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_token'])) {
    $tokenId = (int)$_POST['token_id'];

    // Verify token belongs to current user
    $stmt = $pdo->prepare("SELECT user_id FROM remember_tokens WHERE id = ?");
    $stmt->execute([$tokenId]);
    $tokenUserId = $stmt->fetchColumn();

    if ($tokenUserId == $userId) {
        deleteRememberToken($pdo, $tokenId);
        session_set('success', 'Device removed successfully.');
    }

    header('Location: sessions.php');
    exit;
}

// Handle "logout all devices"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout_all'])) {
    deleteAllUserTokens($pdo, $userId);
    session_set('success', 'Logged out from all devices.');
    header('Location: sessions.php');
    exit;
}

// Get all active tokens
$stmt = $pdo->prepare("
    SELECT id, device_name, ip_address, created_at, last_used, expires_at
    FROM remember_tokens
    WHERE user_id = ?
    AND expires_at > NOW()
    ORDER BY last_used DESC
");
$stmt->execute([$userId]);
$tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = session_flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Active Sessions</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .success {
            background: #e6ffe6;
            color: #006600;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .session-list {
            list-style: none;
        }

        .session-item {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .session-info h3 {
            margin-bottom: 8px;
            color: #333;
        }

        .session-info p {
            color: #666;
            font-size: 14px;
            margin: 3px 0;
        }

        .session-actions button {
            padding: 8px 16px;
            background: #ff4444;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .session-actions button:hover {
            background: #cc0000;
        }

        .logout-all {
            margin-top: 30px;
            padding: 12px 24px;
            background: #ff6b6b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .logout-all:hover {
            background: #ff5252;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Active Sessions</h1>
        <p class="subtitle">Manage devices where you're logged in with "Remember Me"</p>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (empty($tokens)): ?>
            <div class="empty-state">
                <p>No active "Remember Me" sessions.</p>
            </div>
        <?php else: ?>
            <ul class="session-list">
                <?php foreach ($tokens as $token): ?>
                    <li class="session-item">
                        <div class="session-info">
                            <h3><?= htmlspecialchars($token['device_name']) ?></h3>
                            <p><strong>IP:</strong> <?= htmlspecialchars($token['ip_address']) ?></p>
                            <p><strong>Created:</strong> <?= date('M j, Y g:i A', strtotime($token['created_at'])) ?></p>
                            <p><strong>Last used:</strong> <?= date('M j, Y g:i A', strtotime($token['last_used'])) ?></p>
                            <p><strong>Expires:</strong> <?= date('M j, Y', strtotime($token['expires_at'])) ?></p>
                        </div>
                        <div class="session-actions">
                            <form method="POST">
                                <input type="hidden" name="token_id" value="<?= $token['id'] ?>">
                                <button type="submit" name="delete_token">Remove</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <form method="POST">
                <button type="submit" name="logout_all" class="logout-all"
                        onclick="return confirm('Logout from all devices?')">
                    Logout All Devices
                </button>
            </form>
        <?php endif; ?>

        <p style="margin-top: 30px;">
            <a href="../dashboard.php">← Back to Dashboard</a>
        </p>
    </div>
</body>
</html>
```

---

## Security Best Practices

### 1. Use Random Tokens

```php
// GOOD
$token = bin2hex(random_bytes(32));

// BAD
$token = uniqid(); // Predictable!
$token = md5($userId . time()); // Predictable!
```

### 2. Hash Tokens in Database

```php
// Store hash, not plain token
$hash = hash('sha256', $token);
```

This prevents rainbow table attacks if database is compromised.

### 3. Use Two-Part Token (Selector + Token)

```php
// More efficient database queries
$selector = bin2hex(random_bytes(16));
$token = bin2hex(random_bytes(32));

// Query by selector, verify token
```

### 4. Regenerate Token After Use

```php
// After successful auto-login
deleteRememberToken($pdo, $tokenId);
$newToken = createRememberToken($pdo, $userId);
setRememberCookie($newToken);
```

This limits damage if token is stolen.

### 5. Set Secure Cookie Flags

```php
setcookie(
    'remember_me',
    $value,
    $expires,
    '/',
    '',
    isset($_SERVER['HTTPS']), // Secure: HTTPS only
    true                       // HttpOnly: No JavaScript access
);
```

### 6. Allow Token Revocation

Users must be able to:
- See active sessions
- Revoke specific sessions
- Logout from all devices

### 7. Clean Up Old Tokens

```php
// Run periodically (cron job)
cleanupExpiredTokens($pdo);
```

---

## Common Issues

### Issue 1: Token Not Persisting

**Symptom:** User checks "Remember Me" but still has to login

**Causes:**
1. Cookie not being set
2. Cookie domain/path mismatch
3. Cookie being deleted by browser

**Debug:**
```php
// Check if cookie was set
var_dump($_COOKIE);

// Check cookie details
print_r(headers_list());
```

### Issue 2: Auto-Login Not Working

**Symptom:** Cookie exists but user not logged in

**Debug:**
```php
// Check token exists
var_dump($_COOKIE['remember_me']);

// Check token in database
$stmt = $pdo->prepare("SELECT * FROM remember_tokens WHERE selector = ?");
$stmt->execute([$selector]);
var_dump($stmt->fetch());
```

### Issue 3: Token Works Once Then Fails

**Symptom:** Auto-login works first time, fails after

**Cause:** Token regeneration but cookie not updated

**Fix:** Ensure new cookie is set after regeneration.

---

## What's Next?

You now understand "Remember Me":
- How persistent login works
- Token generation and storage
- Auto-login implementation
- Token management UI
- Security best practices

**In Lesson 09**, we'll implement **password reset** functionality - allowing users to recover their accounts via email.

**In Lesson 10**, we'll cover proper **logout** handling including remember-me cleanup.

---

## Laravel Preview

**Now (Pure PHP):**
```php
$token = createRememberToken($pdo, $userId, 30);
setRememberCookie($token, 30);

$user = attemptRememberLogin($pdo);
```

**Laravel:**
```php
// Login with remember
Auth::attempt($credentials, $remember = true);

// Laravel handles token generation, storage, auto-login automatically!
```

Laravel provides:
- Built-in remember-me functionality
- Automatic token management
- Secure defaults

But now you understand how Laravel's `$remember` parameter actually works!
