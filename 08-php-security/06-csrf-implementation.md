# Lesson 06 - Implementing CSRF Protection

**Duration**: 60 minutes

---

## Introduction: The Solution is Elegant

CSRF protection is based on a simple principle:

> Include a secret token in forms that only your site knows. When the form is submitted, verify the token matches. Since attackers can't read the token (Same-Origin Policy), they can't forge requests.

Let's build a complete CSRF protection system from scratch!

---

## Method 1: Synchronizer Token Pattern (Recommended)

This is the most common and reliable CSRF protection method.

### How It Works

1. **Generate** a random token when user logs in or starts session
2. **Store** token in the session
3. **Include** token in every form as hidden field
4. **Validate** token on form submission
5. **Reject** request if token is missing or doesn't match

### Step 1: Generate CSRF Token

```php
<?php
// csrf.php - CSRF Helper Functions

/**
 * Generate a CSRF token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Get existing CSRF token or generate new one
 */
function getCsrfToken(): string {
    return $_SESSION['csrf_token'] ?? generateCsrfToken();
}

/**
 * Validate CSRF token
 */
function validateCsrfToken(string $token): bool {
    // Check if session token exists
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Use hash_equals to prevent timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verify CSRF token or die
 */
function verifyCsrfToken(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die('CSRF validation failed');
    }
}

/**
 * Regenerate CSRF token (for extra security)
 */
function regenerateCsrfToken(): string {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
```

### Step 2: Include Token in Forms

```php
<?php
// example-form.php
session_start();
require_once 'csrf.php';

$csrfToken = getCsrfToken();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transfer Money</title>
</head>
<body>
    <h1>Bank Transfer</h1>

    <form method="POST" action="process-transfer.php">
        <!-- Hidden CSRF token field -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <label>To:</label>
        <input type="text" name="to" required>
        <br>

        <label>Amount:</label>
        <input type="number" name="amount" required>
        <br>

        <button type="submit">Transfer</button>
    </form>
</body>
</html>
```

### Step 3: Validate Token on Submission

```php
<?php
// process-transfer.php
session_start();
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method');
}

// Verify CSRF token
verifyCsrfToken();

// Token is valid, process the transfer
$to = $_POST['to'];
$amount = $_POST['amount'];

// ... validate and process transfer ...

echo "Transfer successful!";

// Optional: Regenerate token after critical actions
regenerateCsrfToken();
```

### Creating a Helper Function for Forms

```php
<?php
/**
 * Output CSRF token hidden input field
 */
function csrfField(): string {
    $token = getCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' .
           htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}
?>

<!-- Usage in forms -->
<form method="POST">
    <?= csrfField() ?>
    <input type="text" name="username">
    <button type="submit">Submit</button>
</form>
```

---

## Method 2: Double Submit Cookie Pattern

Alternative method when storing tokens in sessions is not ideal (stateless APIs).

### How It Works

1. **Generate** random token
2. **Store** in cookie AND send in form/header
3. **Validate** that cookie value matches form/header value

Since JavaScript from evil.com cannot read cookies from victim-site.com, attackers can't get the token.

### Implementation

```php
<?php
// double-submit-csrf.php

/**
 * Generate and set CSRF cookie
 */
function setDoubleCsrfToken(): string {
    $token = bin2hex(random_bytes(32));

    // Set cookie with secure flags
    setcookie('csrf_token', $token, [
        'expires' => time() + 3600,
        'path' => '/',
        'domain' => '',
        'secure' => true,   // HTTPS only
        'httponly' => false, // JavaScript needs to read it
        'samesite' => 'Strict'
    ]);

    return $token;
}

/**
 * Get CSRF token from cookie
 */
function getDoubleCsrfToken(): string {
    if (!isset($_COOKIE['csrf_token'])) {
        return setDoubleCsrfToken();
    }

    return $_COOKIE['csrf_token'];
}

/**
 * Validate double submit CSRF token
 */
function validateDoubleCsrfToken(string $submittedToken): bool {
    if (!isset($_COOKIE['csrf_token'])) {
        return false;
    }

    return hash_equals($_COOKIE['csrf_token'], $submittedToken);
}
```

### Usage with JavaScript

```html
<form id="myForm">
    <input type="text" name="username">
    <button type="submit">Submit</button>
</form>

<script>
document.getElementById('myForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Read CSRF token from cookie
    const csrfToken = document.cookie
        .split('; ')
        .find(row => row.startsWith('csrf_token='))
        ?.split('=')[1];

    // Send with fetch
    fetch('/api/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            username: this.username.value
        })
    });
});
</script>
```

---

## Method 3: SameSite Cookie Attribute

Modern browsers support the `SameSite` cookie attribute which prevents cookies from being sent with cross-site requests.

### Setting SameSite Cookies

```php
<?php
// Set session cookie with SameSite
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'  // or 'Lax'
]);

session_start();
```

### SameSite Values

**Strict**: Cookie never sent in cross-site requests
```php
'samesite' => 'Strict'

// Cookie sent: example.com → example.com ✓
// Cookie NOT sent: evil.com → example.com ✗
```

**Lax**: Cookie sent with top-level navigations (GET only)
```php
'samesite' => 'Lax'

// Cookie sent: User clicks link from evil.com to example.com ✓
// Cookie NOT sent: evil.com makes POST to example.com ✗
// Cookie NOT sent: <img src="example.com"> from evil.com ✗
```

**None**: Cookie sent with all requests (requires Secure flag)
```php
'samesite' => 'None'
'secure' => true // Required with None
```

### Recommended Configuration

```php
<?php
// config.php - Secure session configuration

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

// Or using session_set_cookie_params
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
```

### SameSite Limitations

**Pros**:
- Easy to implement
- No token management
- Works automatically

**Cons**:
- Not supported in older browsers
- Lax mode allows GET-based attacks
- Strict mode breaks some legitimate flows (email links, external logins)

**Best practice**: Use SameSite as an additional layer, but still implement CSRF tokens for complete protection.

---

## Method 4: Custom Request Headers

For AJAX requests, you can require custom headers that cross-site requests can't set.

### Implementation

```php
<?php
// api/endpoint.php

// Check for custom header
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
    $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    die('Invalid request');
}

// Process request
```

### Client-Side

```javascript
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
});
```

### Why This Works

Cross-site requests (from evil.com to victim-site.com) can't set custom headers due to CORS restrictions.

**However**: This method alone isn't sufficient because:
- Can be bypassed if CORS is misconfigured
- Doesn't work for traditional form submissions
- Requires JavaScript (no progressive enhancement)

**Best practice**: Use custom headers as an additional check for AJAX endpoints, but still validate CSRF tokens.

---

## Complete CSRF Protection Class

Let's build a comprehensive CSRF protection system:

```php
<?php
// CsrfProtection.php

class CsrfProtection {

    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Initialize CSRF protection
     */
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_start();
        }

        // Generate token if not exists
        self::generateToken();
    }

    /**
     * Generate CSRF token
     */
    public static function generateToken(): string {
        if (empty($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Get current CSRF token
     */
    public static function getToken(): string {
        return $_SESSION[self::TOKEN_NAME] ?? self::generateToken();
    }

    /**
     * Output hidden input field with CSRF token
     */
    public static function field(): string {
        $token = self::getToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Get token from request
     */
    private static function getSubmittedToken(): ?string {
        // Check POST data
        if (isset($_POST[self::TOKEN_NAME])) {
            return $_POST[self::TOKEN_NAME];
        }

        // Check custom header (for AJAX)
        $headerName = 'HTTP_X_' . strtoupper(str_replace('-', '_', self::TOKEN_NAME));
        if (isset($_SERVER[$headerName])) {
            return $_SERVER[$headerName];
        }

        return null;
    }

    /**
     * Validate CSRF token
     */
    public static function validate(): bool {
        $submittedToken = self::getSubmittedToken();

        if ($submittedToken === null) {
            return false;
        }

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION[self::TOKEN_NAME], $submittedToken);
    }

    /**
     * Verify CSRF token or throw exception
     */
    public static function verify(): void {
        if (!self::validate()) {
            http_response_code(403);
            throw new Exception('CSRF validation failed');
        }
    }

    /**
     * Regenerate CSRF token
     * Call after sensitive operations (login, password change)
     */
    public static function regenerate(): string {
        $_SESSION[self::TOKEN_NAME] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Middleware-style validation
     * Returns true if validation passes, false if fails
     */
    public static function middleware(): bool {
        // Skip CSRF check for GET, HEAD, OPTIONS
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return true;
        }

        // Validate for state-changing methods
        return self::validate();
    }
}
```

### Using the CSRF Protection Class

```php
<?php
// bootstrap.php
require_once 'CsrfProtection.php';

// Initialize CSRF protection
CsrfProtection::init();
```

```php
<?php
// form.php
require_once 'bootstrap.php';
?>

<!DOCTYPE html>
<html>
<body>
    <form method="POST" action="process.php">
        <?= CsrfProtection::field() ?>

        <input type="text" name="username">
        <button type="submit">Submit</button>
    </form>
</body>
</html>
```

```php
<?php
// process.php
require_once 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verify CSRF token
        CsrfProtection::verify();

        // Token valid, process form
        $username = $_POST['username'];

        // ... process data ...

        echo "Success!";

    } catch (Exception $e) {
        // CSRF validation failed
        echo "Error: " . $e->getMessage();
    }
}
```

### Using with AJAX

```php
<?php
// api/update.php
require_once '../bootstrap.php';

header('Content-Type: application/json');

if (!CsrfProtection::validate()) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF validation failed']);
    exit;
}

// Process API request
$data = json_decode(file_get_contents('php://input'), true);

// ... process data ...

echo json_encode(['success' => true]);
```

```html
<!-- client.html -->
<script>
// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

fetch('/api/update', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Csrf-Token': csrfToken
    },
    credentials: 'same-origin',
    body: JSON.stringify({
        username: 'john'
    })
})
.then(response => response.json())
.then(data => console.log(data));
</script>

<!-- In your HTML head -->
<meta name="csrf-token" content="<?= CsrfProtection::getToken() ?>">
```

---

## CSRF Protection Best Practices

### 1. Generate Strong Tokens

```php
// GOOD - Cryptographically secure
$token = bin2hex(random_bytes(32));

// BAD - Predictable
$token = md5(time());
$token = uniqid();
```

### 2. Use hash_equals() for Comparison

```php
// GOOD - Prevents timing attacks
if (hash_equals($_SESSION['csrf_token'], $submittedToken)) {
    // Valid
}

// BAD - Vulnerable to timing attacks
if ($_SESSION['csrf_token'] === $submittedToken) {
    // Timing attack possible
}
```

**Why timing attacks matter**: Attackers can measure how long comparisons take to guess tokens character by character.

### 3. Protect All State-Changing Operations

```php
// Protect these:
- POST, PUT, DELETE, PATCH requests
- Login, logout
- Profile updates
- Password changes
- Email changes
- Purchases, transfers
- Admin actions
- Settings changes

// Don't need protection:
- GET requests (if read-only)
- Public pages
- API endpoints using Bearer tokens (not cookies)
```

### 4. Regenerate After Login

```php
<?php
// login.php

if (verifyLogin($username, $password)) {
    // Regenerate session ID
    session_regenerate_id(true);

    // Regenerate CSRF token
    CsrfProtection::regenerate();

    $_SESSION['user_id'] = $userId;
    $_SESSION['logged_in'] = true;
}
```

### 5. Set Token in Meta Tag for AJAX

```html
<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="<?= CsrfProtection::getToken() ?>">
</head>
<body>
    <!-- Your content -->

    <script>
    // Access token in JavaScript
    const token = document.querySelector('meta[name="csrf-token"]').content;

    // Use in all AJAX requests
    </script>
</body>
</html>
```

### 6. Combine Multiple Protections

**Defense in depth**:

```php
<?php
// 1. SameSite cookies
session_set_cookie_params([
    'samesite' => 'Lax',
    'secure' => true,
    'httponly' => true
]);

// 2. CSRF tokens
CsrfProtection::init();

// 3. Verify referer (additional check)
function verifyReferer(): bool {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';

    return strpos($referer, 'https://' . $host) === 0;
}

// 4. Require re-authentication for critical actions
function requirePasswordForAction(): void {
    if (!isset($_POST['current_password'])) {
        die('Password required for this action');
    }

    // Verify current password
    // This ensures it's actually the user, not a CSRF attack
}
```

---

## Testing Your CSRF Protection

### Manual Test

1. Create test HTML file:

```html
<!-- csrf-test.html -->
<!DOCTYPE html>
<html>
<body>
    <h1>CSRF Test</h1>

    <form id="test" action="https://your-site.com/process.php" method="POST">
        <input type="hidden" name="username" value="hacked">
        <input type="hidden" name="email" value="attacker@evil.com">
        <!-- No CSRF token -->
    </form>

    <script>
    // Auto-submit
    document.getElementById('test').submit();
    </script>
</body>
</html>
```

2. Host this file (can use `php -S localhost:8000`)
3. Log into your actual site in same browser
4. Visit the test file
5. **Expected**: Request should be rejected (403 Forbidden)
6. **If successful**: Your CSRF protection is working!

### Automated Test

```php
<?php
// tests/CsrfTest.php

// Test 1: Request without token should fail
$ch = curl_init('https://your-site.com/process.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['username' => 'test']);
curl_setopt($ch, CURLOPT_COOKIE, 'session=YOUR_SESSION_COOKIE');
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

assert($code === 403, 'Request without CSRF token should be rejected');

// Test 2: Request with wrong token should fail
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'username' => 'test',
    'csrf_token' => 'wrong_token'
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

assert($code === 403, 'Request with wrong token should be rejected');

// Test 3: Request with valid token should succeed
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'username' => 'test',
    'csrf_token' => 'VALID_TOKEN_FROM_SESSION'
]);
$response = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

assert($code === 200, 'Request with valid token should succeed');

echo "All CSRF tests passed!\n";
```

---

## Common CSRF Protection Mistakes

### Mistake 1: Only Checking if Token Exists

```php
// WRONG - Just checking if token is present
if (isset($_POST['csrf_token'])) {
    // Process request
}

// Attacker can send any token!
```

### Mistake 2: Not Using hash_equals()

```php
// WRONG - Timing attack possible
if ($_SESSION['csrf_token'] == $_POST['csrf_token']) {
    // ...
}

// RIGHT - Constant time comparison
if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // ...
}
```

### Mistake 3: Token in URL

```php
// WRONG - Token exposed in URL
<a href="/delete?id=123&csrf_token=<?= $token ?>">Delete</a>

// Problems:
// - Token in browser history
// - Token in server logs
// - Token in referer headers
// - Token can be bookmarked
```

### Mistake 4: Using GET for State Changes

```php
// WRONG - State change via GET
<a href="/delete-account.php?confirm=yes">Delete Account</a>

// Can be exploited with:
<img src="https://victim-site.com/delete-account.php?confirm=yes">
```

### Mistake 5: Not Protecting All Forms

```php
// Form 1: Protected ✓
<form method="POST">
    <?= CsrfProtection::field() ?>
    ...
</form>

// Form 2: NOT protected ✗
<form method="POST" action="delete.php">
    <!-- Missing CSRF token! -->
    <input type="hidden" name="id" value="123">
    <button>Delete</button>
</form>
```

---

## CSRF Protection Checklist

- [ ] **Token generation**: Using cryptographically secure random_bytes()
- [ ] **Token storage**: In session (server-side)
- [ ] **Token inclusion**: In all forms as hidden field
- [ ] **Token validation**: Using hash_equals() comparison
- [ ] **All POST/PUT/DELETE**: Protected with CSRF validation
- [ ] **No GET state changes**: GET only for read operations
- [ ] **SameSite cookies**: Set to Lax or Strict
- [ ] **Secure session settings**: httponly, secure flags enabled
- [ ] **AJAX support**: Token in meta tag or headers
- [ ] **Regenerate on login**: New token after authentication
- [ ] **Error handling**: Proper 403 responses on validation failure
- [ ] **Testing**: Manual and automated tests pass

---

## Key Takeaways

1. **CSRF tokens** are the primary defense against CSRF attacks
2. **Generate secure tokens** using random_bytes(32)
3. **Store in session**, include in forms, validate on submission
4. **hash_equals()** prevents timing attacks
5. **SameSite cookies** provide additional protection
6. **Protect all state-changing operations** (POST, PUT, DELETE)
7. **Never use GET** for operations that modify state
8. **Regenerate tokens** after login and critical operations
9. **Test thoroughly** to ensure protection works
10. **Defense in depth** - combine multiple protection methods

---

## What's Next?

You now understand and can implement CSRF protection! In the next lesson, we'll review **SQL injection** (covered in Module 06) with advanced techniques and edge cases to ensure your database queries are completely secure.

After that, we'll cover:
- File upload security
- Access control and authorization
- Security headers
- Rate limiting

You're building a comprehensive security toolkit!
