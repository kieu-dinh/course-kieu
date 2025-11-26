# Lesson 04 - Building a Complete Registration System

**Duration**: 1.5 hours
**Objectives**: Build a production-ready user registration system with proper validation, error handling, and security

---

## What Makes a Good Registration System?

A professional registration system needs:

1. **User-friendly form** - Clear labels, helpful messages
2. **Strong validation** - Both client and server side
3. **Security** - Hashed passwords, SQL injection prevention
4. **Uniqueness checks** - No duplicate usernames/emails
5. **Error handling** - Clear, specific error messages
6. **Success feedback** - Confirm registration worked
7. **Input persistence** - Don't make users retype everything on error
8. **Email verification** (optional) - Confirm email ownership

We'll build all of this step by step.

---

## Database Setup

First, create the users table:

```sql
-- database/schema.sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_username (username),
    INDEX idx_email (email)
);
```

**Why these indexes?**
- We'll frequently query by username (login)
- We'll frequently query by email (uniqueness check)
- Indexes make these queries much faster

Run this to create the table:

```bash
mysql -u root -p your_database < database/schema.sql
```

---

## Project Structure

```
auth-system/
├── config/
│   └── database.php          # Database connection
├── includes/
│   ├── session.php           # Session helpers (from Lesson 02)
│   └── validation.php        # Validation helpers (new!)
├── public/
│   ├── register.php          # Registration page
│   ├── login.php            # Login page (next lesson)
│   └── dashboard.php        # Protected page
└── database/
    └── schema.sql           # Database schema
```

---

## Step 1: Database Connection

Create a reusable database connection:

```php
<?php
// config/database.php

$host = 'localhost';
$dbname = 'auth_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
```

**Key PDO options:**
- `ERRMODE_EXCEPTION`: Throw exceptions on errors (easier to catch)
- `FETCH_ASSOC`: Return associative arrays (cleaner)
- `EMULATE_PREPARES => false`: Use real prepared statements (more secure)

---

## Step 2: Validation Helpers

Create reusable validation functions:

```php
<?php
// includes/validation.php

/**
 * Validate username
 */
function validateUsername(string $username): array
{
    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (strlen($username) > 50) {
        $errors[] = 'Username must not exceed 50 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    return $errors;
}

/**
 * Validate email
 */
function validateEmail(string $email): array
{
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email address is invalid';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email must not exceed 255 characters';
    }

    return $errors;
}

/**
 * Validate password strength
 */
function validatePassword(string $password): array
{
    $errors = [];

    if (empty($password)) {
        $errors[] = 'Password is required';
    } else {
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character (!@#$%^&*)';
        }
    }

    return $errors;
}

/**
 * Check if username exists in database
 */
function usernameExists(PDO $pdo, string $username): bool
{
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch() !== false;
}

/**
 * Check if email exists in database
 */
function emailExists(PDO $pdo, string $email): bool
{
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch() !== false;
}

/**
 * Sanitize input (trim whitespace)
 */
function sanitize(string $input): string
{
    return trim($input);
}

/**
 * Get POST value safely
 */
function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}
?>
```

**Why separate validation functions?**
- Reusable across different forms
- Easier to test
- Easier to modify rules
- Cleaner main code

---

## Step 3: Registration Form (HTML)

Create the registration form with client-side validation:

```php
<?php
// public/register.php
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
    // Get and sanitize inputs
    $username = sanitize(post('username'));
    $email = sanitize(post('email'));
    $password = post('password');
    $confirmPassword = post('confirm_password');

    $errors = [];

    // Validate inputs
    $errors = array_merge($errors, validateUsername($username));
    $errors = array_merge($errors, validateEmail($email));
    $errors = array_merge($errors, validatePassword($password));

    // Check password confirmation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // Check username uniqueness
    if (empty(validateUsername($username)) && usernameExists($pdo, $username)) {
        $errors[] = 'Username is already taken';
    }

    // Check email uniqueness
    if (empty(validateEmail($email)) && emailExists($pdo, $email)) {
        $errors[] = 'Email address is already registered';
    }

    // If no errors, create user
    if (empty($errors)) {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $sql = "INSERT INTO users (username, email, password, created_at)
                    VALUES (:username, :email, :password, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            // Success! Redirect to login
            session_set('success', 'Registration successful! Please log in.');
            header('Location: login.php');
            exit;

        } catch (PDOException $e) {
            // Log error in production, display generic message
            error_log("Registration error: " . $e->getMessage());
            $errors[] = 'Registration failed. Please try again later.';
        }
    }

    // If errors, save for display
    if (!empty($errors)) {
        session_set('errors', $errors);
        session_set('old_username', $username);
        session_set('old_email', $email);
    }
}

// Get flash data
$errors = session_flash('errors', []);
$oldUsername = session_flash('old_username', '');
$oldEmail = session_flash('old_email', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            max-width: 450px;
        }

        h1 {
            margin-bottom: 10px;
            color: #333;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
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

        .hint {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
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

        .password-strength {
            margin-top: 10px;
        }

        .strength-meter {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }

        .strength-meter-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
        }

        .strength-weak { width: 33%; background: #ff4444; }
        .strength-medium { width: 66%; background: #ffaa00; }
        .strength-strong { width: 100%; background: #44ff44; }

        .strength-text {
            font-size: 13px;
            margin-top: 5px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create Account</h1>
        <p class="subtitle">Join us today! It's free.</p>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" id="registerForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?= htmlspecialchars($oldUsername) ?>"
                    required
                    pattern="[a-zA-Z0-9_]+"
                    minlength="3"
                    maxlength="50"
                    autocomplete="username"
                >
                <div class="hint">3-50 characters. Letters, numbers, and underscores only.</div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($oldEmail) ?>"
                    required
                    autocomplete="email"
                >
                <div class="hint">We'll never share your email.</div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <div class="password-strength">
                    <div class="strength-meter">
                        <div class="strength-meter-fill" id="strengthMeter"></div>
                    </div>
                    <div class="strength-text" id="strengthText"></div>
                </div>
                <div class="hint">Min 8 characters with uppercase, lowercase, number, and special character.</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="footer">
            Already have an account? <a href="login.php">Log in</a>
        </div>
    </div>

    <script>
        // Client-side password strength meter
        const passwordInput = document.getElementById('password');
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthText = document.getElementById('strengthText');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            strengthMeter.className = 'strength-meter-fill';

            if (strength <= 2) {
                strengthMeter.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.style.color = '#ff4444';
            } else if (strength <= 4) {
                strengthMeter.classList.add('strength-medium');
                strengthText.textContent = 'Medium password';
                strengthText.style.color = '#ffaa00';
            } else {
                strengthMeter.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.style.color = '#44aa44';
            }
        });

        // Client-side password match validation
        const confirmPassword = document.getElementById('confirm_password');
        const form = document.getElementById('registerForm');

        form.addEventListener('submit', function(e) {
            if (passwordInput.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });
    </script>
</body>
</html>
```

---

## Step 4: Understanding the Flow

Let's trace what happens when a user registers:

### 1. User Visits register.php

```
GET /register.php
→ Shows empty form
```

### 2. User Fills Form and Submits

```
POST /register.php
username: john_doe
email: john@example.com
password: MySecret123!
confirm_password: MySecret123!
```

### 3. Server-Side Processing

```php
// 1. Sanitize inputs
$username = "john_doe" (trimmed)
$email = "john@example.com" (trimmed)

// 2. Validate
validateUsername() → [] (no errors)
validateEmail() → [] (no errors)
validatePassword() → [] (no errors)
Password match? → Yes

// 3. Check uniqueness
usernameExists() → false (available)
emailExists() → false (available)

// 4. No errors, proceed
$hashedPassword = password_hash("MySecret123!", PASSWORD_DEFAULT)
// Result: $2y$10$abc123...xyz789

// 5. Insert into database
INSERT INTO users (username, email, password, created_at)
VALUES ('john_doe', 'john@example.com', '$2y$10$abc123...xyz789', NOW())

// 6. Success!
Redirect to login.php with success message
```

### 4. If Errors Occur

```php
// Example: Username taken
$errors[] = "Username is already taken";

// Save errors and old input to session
session_set('errors', $errors);
session_set('old_username', $username);
session_set('old_email', $email);

// No redirect - form re-displays with errors
// User sees their input preserved
```

---

## Step 5: Security Considerations

### SQL Injection Prevention

**Wrong (Vulnerable):**
```php
$sql = "INSERT INTO users (username, password)
        VALUES ('$username', '$password')";
$pdo->query($sql); // DANGER!
```

If `$username = "admin'); DROP TABLE users; --"`, you're in trouble!

**Right (Protected):**
```php
$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $password]);
```

Prepared statements automatically escape special characters.

### XSS Prevention

**Always escape output:**
```php
// Wrong
<input value="<?= $oldUsername ?>"> <!-- XSS vulnerability! -->

// Right
<input value="<?= htmlspecialchars($oldUsername) ?>">
```

If `$oldUsername = '"><script>alert("XSS")</script>'`, the first version would execute JavaScript!

### Password Storage

**Never:**
```php
// Store plain text
INSERT INTO users (password) VALUES ('secret123')

// Use MD5 or SHA1
INSERT INTO users (password) VALUES (MD5('secret123'))

// Use your own hashing
INSERT INTO users (password) VALUES (SHA256($password . $salt))
```

**Always:**
```php
$hash = password_hash($password, PASSWORD_DEFAULT);
INSERT INTO users (password) VALUES ('$hash')
```

### Rate Limiting (Basic)

Prevent account creation spam:

```php
// Check last registration from this IP
$stmt = $pdo->prepare("
    SELECT created_at FROM users
    WHERE registration_ip = ?
    ORDER BY created_at DESC
    LIMIT 1
");
$stmt->execute([$_SERVER['REMOTE_ADDR']]);
$lastReg = $stmt->fetch();

if ($lastReg) {
    $timeSince = time() - strtotime($lastReg['created_at']);
    if ($timeSince < 60) { // 1 minute cooldown
        $errors[] = "Please wait before creating another account";
    }
}
```

We'll cover rate limiting in more depth in Module 08 (Security).

---

## Step 6: Email Verification (Optional Enhancement)

For production systems, verify email ownership:

### Add email verification columns to users table:

```sql
ALTER TABLE users ADD COLUMN email_verified BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL;
ALTER TABLE users ADD COLUMN verification_token_expires TIMESTAMP NULL;
```

### Generate verification token on registration:

```php
// After inserting user
$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+24 hours'));

$stmt = $pdo->prepare("
    UPDATE users
    SET verification_token = ?, verification_token_expires = ?
    WHERE id = ?
");
$stmt->execute([$token, $expires, $userId]);

// Send email with verification link
$verificationUrl = "https://yoursite.com/verify.php?token=$token";
// mail($email, "Verify your email", "Click here: $verificationUrl");
```

### Verification handler:

```php
// verify.php
$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("
    SELECT id FROM users
    WHERE verification_token = ?
    AND verification_token_expires > NOW()
    AND email_verified = FALSE
");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user) {
    // Mark as verified
    $stmt = $pdo->prepare("
        UPDATE users
        SET email_verified = TRUE,
            verification_token = NULL,
            verification_token_expires = NULL
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);

    echo "Email verified! You can now log in.";
} else {
    echo "Invalid or expired verification link.";
}
```

We'll implement full email verification in Exercise 7.7!

---

## Common Problems and Solutions

### Problem 1: "Headers already sent"

**Error:**
```
Warning: Cannot modify header information - headers already sent by (output started at register.php:1)
```

**Cause:** Output before `header()` redirect

**Solution:**
- No whitespace before `<?php`
- No `echo` before redirect
- Use output buffering: `ob_start()` at top of file

### Problem 2: Form data lost on error

**Symptom:** User fixes error, submits again, all data gone

**Solution:** Use session flash to preserve input:
```php
session_set('old_username', $username);
session_set('old_email', $email);
```

### Problem 3: Duplicate email not caught

**Cause:** Email saved with different casing: `John@example.com` vs `john@example.com`

**Solution:** Convert to lowercase before checking:
```php
$email = strtolower($email);
```

Or use case-insensitive collation in MySQL:
```sql
email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
```

### Problem 4: Username with spaces accepted

**Cause:** Client-side validation bypassed or insufficient

**Solution:** Always validate on server:
```php
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Invalid username format';
}
```

---

## Testing Your Registration System

Create a test script:

```php
<?php
// test-registration.php

require_once 'includes/validation.php';

// Test 1: Valid username
$errors = validateUsername('john_doe');
assert(empty($errors), "Valid username should pass");

// Test 2: Invalid username (too short)
$errors = validateUsername('ab');
assert(!empty($errors), "Short username should fail");

// Test 3: Invalid username (special chars)
$errors = validateUsername('john@doe');
assert(!empty($errors), "Username with @ should fail");

// Test 4: Valid email
$errors = validateEmail('test@example.com');
assert(empty($errors), "Valid email should pass");

// Test 5: Invalid email
$errors = validateEmail('not-an-email');
assert(!empty($errors), "Invalid email should fail");

// Test 6: Weak password
$errors = validatePassword('short');
assert(!empty($errors), "Weak password should fail");

// Test 7: Strong password
$errors = validatePassword('MySecret123!');
assert(empty($errors), "Strong password should pass");

echo "All tests passed!\n";
?>
```

---

## Best Practices Checklist

- [ ] **Validate on both client and server** - Never trust client alone
- [ ] **Use prepared statements** - Prevent SQL injection
- [ ] **Hash passwords** - Never store plain text
- [ ] **Check uniqueness** - Username and email
- [ ] **Preserve input on error** - Better UX
- [ ] **Use HTTPS** - Protect passwords in transit
- [ ] **Sanitize inputs** - Trim whitespace
- [ ] **Escape outputs** - Prevent XSS
- [ ] **Log errors** - Don't expose internals to users
- [ ] **Rate limit** - Prevent abuse
- [ ] **Verify emails** (optional) - Confirm ownership
- [ ] **Test thoroughly** - Try to break your own system

---

## What's Next?

You now have a complete, secure registration system!

**In Lesson 05**, we'll build the **login system** that:
- Authenticates users
- Verifies passwords
- Creates user sessions
- Handles login failures
- Tracks login attempts

**In Lesson 06**, we'll add **session management** to:
- Check if users are logged in
- Get current user data
- Handle session timeouts
- Implement "remember me"

After these lessons, you'll have a fully functional authentication system!

---

## Laravel Preview

**Now (Pure PHP):**
```php
// 50+ lines of validation, hashing, inserting...
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt->execute([$username, $email, $hashedPassword]);
```

**Laravel:**
```php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

User::create([
    'username' => $request->username,
    'email' => $request->email,
    'password' => Hash::make($request->password)
]);
```

Laravel also provides:
- Form requests for validation
- Automatic email verification
- Built-in rate limiting
- CSRF protection

But now you understand what's happening behind Laravel's magic!
