# Lesson 03 - Password Hashing: Secure Password Storage

**Duration**: 1 hour
**Objectives**: Understand why password hashing is critical and learn to implement it correctly

---

## The Worst Mistake in Web Development

**NEVER, EVER, EVER store passwords in plain text.**

If you do this, and your database gets hacked (and databases DO get hacked), every user's password is exposed. And since people reuse passwords, you've just compromised their email, bank, social media, and everything else.

### The Wrong Way (NEVER Do This!)

```php
<?php
// register.php - DANGEROUS! NEVER DO THIS!
$username = $_POST['username'];
$password = $_POST['password']; // Plain text password

$sql = "INSERT INTO users (username, password) VALUES (?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$username, $password]); // Storing plain text password!
?>
```

**Database:**
```
users table:
id | username | password
1  | john     | secret123
2  | mary     | password456
3  | admin    | admin2024
```

If someone hacks this database, they instantly have every user's password. Game over.

### Real-World Consequences

In 2012, LinkedIn was hacked. 6.5 million passwords leaked. Many were stored with weak or no hashing. Users lost accounts across multiple sites.

In 2013, Adobe was hacked. 150 million passwords leaked. They used weak encryption.

**Don't be that developer.** Learn to do this right from day one.

---

## What is Password Hashing?

**Hashing** is a one-way mathematical function that converts text into a fixed-length string of characters.

### Key Properties of Hashing

1. **One-way**: You can't reverse it
   - Hash "secret123" → `$2y$10$abcd...xyz`
   - You can't convert `$2y$10$abcd...xyz` back to "secret123"

2. **Deterministic**: Same input always produces same output
   - Hash "secret123" → `$2y$10$abcd...xyz`
   - Hash "secret123" again → Same hash (if using same salt)

3. **Avalanche effect**: Small change = completely different hash
   - Hash "secret123" → `$2y$10$abcd...xyz`
   - Hash "secret124" → `$2y$10$wxyz...abc` (totally different)

4. **Fixed length**: Output is always same length regardless of input
   - Hash "hi" → 60 characters
   - Hash "this is a very long password" → 60 characters

### Hashing vs Encryption

**Encryption** is two-way - you can decrypt it:
```php
$encrypted = encrypt("secret123", $key);
$decrypted = decrypt($encrypted, $key); // Back to "secret123"
```

**Hashing** is one-way - you can't reverse it:
```php
$hash = hash("secret123");
$original = unhash($hash); // IMPOSSIBLE! No such function exists.
```

**For passwords, we WANT one-way.** We never need to know the original password. We only need to verify if a login attempt matches.

---

## How Password Verification Works

Since we can't decrypt the hash, how do we verify logins?

### The Process

**Registration:**
1. User types password: `secret123`
2. Hash it: `$2y$10$abcd...xyz`
3. Store the hash in database

**Login:**
1. User types password: `secret123`
2. Get the stored hash from database: `$2y$10$abcd...xyz`
3. Hash the login attempt with the SAME algorithm and salt
4. Compare the two hashes
5. If they match → correct password
6. If they don't match → wrong password

**Example:**

```php
// Registration
$password = "secret123";
$hash = password_hash($password, PASSWORD_BCRYPT);
// Produces: $2y$10$N9qo8uLOickgx2ZMRZoMye/IjZmNLsXEKmCZkVu8W8xCQJME.L4.S
// Store this hash in database

// Login attempt
$loginPassword = "secret123"; // What user typed
$storedHash = "$2y$10$N9qo8uLOickgx2ZMRZoMye/IjZmNLsXEKmCZkVu8W8xCQJME.L4.S"; // From database

if (password_verify($loginPassword, $storedHash)) {
    echo "Correct password!";
} else {
    echo "Wrong password!";
}
```

---

## PHP's password_hash() Function

PHP provides built-in, secure password hashing functions. **Always use these.**

### Basic Usage

```php
<?php
$password = "secret123";

// Hash the password
$hash = password_hash($password, PASSWORD_BCRYPT);

echo $hash;
// Output: $2y$10$N9qo8uLOickgx2ZMRZoMye/IjZmNLsXEKmCZkVu8W8xCQJME.L4.S
?>
```

### Understanding the Hash Format

```
$2y$10$N9qo8uLOickgx2ZMRZoMye/IjZmNLsXEKmCZkVu8W8xCQJME.L4.S
│││ │  │                      │                              │
│││ │  │                      │                              └─ Actual hash
│││ │  │                      └─ Salt (22 characters)
│││ │  └─ Separator
│││ └─ Cost factor (10 = 2^10 = 1024 iterations)
││└─ Separator
│└─ Algorithm variant (2y = bcrypt)
└─ Algorithm identifier ($2 = bcrypt)
```

**The hash contains everything needed to verify it** - the algorithm, cost, salt, and hash. You just store this one string!

### Algorithm Options

```php
<?php
// Bcrypt (recommended, default)
$hash = password_hash($password, PASSWORD_BCRYPT);

// Argon2i (more secure, requires PHP 7.2+)
$hash = password_hash($password, PASSWORD_ARGON2I);

// Argon2id (most secure, requires PHP 7.3+)
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Default (currently bcrypt, may change in future PHP versions)
$hash = password_hash($password, PASSWORD_DEFAULT);
?>
```

**Recommendation:** Use `PASSWORD_DEFAULT` for new projects. It automatically uses the best algorithm available in your PHP version.

### Cost Factor (Work Factor)

The **cost** determines how many iterations to run. Higher cost = more secure but slower.

```php
<?php
// Default cost (10)
$hash = password_hash($password, PASSWORD_BCRYPT);

// Custom cost (higher = slower but more secure)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
?>
```

**How to choose cost:**

```php
<?php
// Test different costs
function findBestCost() {
    $targetTime = 0.05; // 50 milliseconds

    for ($cost = 8; $cost < 15; $cost++) {
        $start = microtime(true);
        password_hash("test", PASSWORD_BCRYPT, ['cost' => $cost]);
        $end = microtime(true);

        $time = $end - $start;
        echo "Cost $cost: " . round($time * 1000, 2) . " ms\n";

        if ($time > $targetTime) {
            return $cost - 1;
        }
    }

    return 12; // Default fallback
}

$bestCost = findBestCost();
echo "Best cost for your server: $bestCost";
?>
```

**Rule of thumb:**
- Cost 10: Fast, good for most sites (default)
- Cost 12: Slower, better for sensitive data
- Cost 14+: Very slow, only for highly sensitive data

### Verifying Passwords

```php
<?php
$password = "secret123";
$storedHash = "$2y$10$N9qo8uLOickgx2ZMRZoMye/IjZmNLsXEKmCZkVu8W8xCQJME.L4.S";

// Verify password
if (password_verify($password, $storedHash)) {
    echo "Password correct!";
} else {
    echo "Password incorrect!";
}
?>
```

**Important:** `password_verify()` is timing-attack resistant. Never compare hashes manually!

---

## The Salt: Why Hashing Isn't Enough

Imagine two users have the same password:

```
Without salt:
User A: secret123 → hash1
User B: secret123 → hash1
// Same password = same hash = security problem!
```

An attacker can see that User A and User B have the same password. They can also use **rainbow tables** - pre-computed tables of common passwords and their hashes.

### What is a Salt?

A **salt** is random data added to the password before hashing:

```
With salt:
User A: secret123 + salt_abc123 → hash1
User B: secret123 + salt_xyz789 → hash2
// Same password, different salts = different hashes!
```

Now even if both users have "secret123", their stored hashes are completely different.

### PHP Handles Salts Automatically

**You don't need to generate salts manually!** PHP's `password_hash()` generates a cryptographically secure random salt automatically and includes it in the hash string.

```php
<?php
$password = "secret123";

// First hash
$hash1 = password_hash($password, PASSWORD_BCRYPT);
echo $hash1; // $2y$10$abcd...

// Second hash (different salt!)
$hash2 = password_hash($password, PASSWORD_BCRYPT);
echo $hash2; // $2y$10$wxyz... (different!)

// But both verify correctly!
echo password_verify($password, $hash1); // true
echo password_verify($password, $hash2); // true
?>
```

Each hash contains its own unique salt. `password_verify()` extracts the salt from the hash and uses it for verification.

---

## Practical Implementation: Registration

Let's build a proper registration system with password hashing:

```php
<?php
// register.php
require_once 'includes/session.php';
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    // Validation
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores';
    }

    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email is invalid';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    // Check if username already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->fetch()) {
            $errors[] = 'Username already taken';
        }
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = 'Email already registered';
        }
    }

    // If no errors, create user
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $email, $hashedPassword])) {
            session_set('success', 'Registration successful! Please log in.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }

    // Save errors and old input
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
    <title>Register</title>
    <style>
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { padding: 8px; width: 300px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Register</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($oldUsername) ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($oldEmail) ?>" required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
            <small>Min 8 characters, must include uppercase, lowercase, and number</small>
        </div>

        <div class="form-group">
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>
```

**Key points:**
1. Validate password strength
2. Check for existing username/email
3. Use `password_hash()` with `PASSWORD_DEFAULT`
4. Never store plain text password
5. Confirm password match before hashing

---

## Practical Implementation: Login

```php
<?php
// login.php
require_once 'includes/session.php';
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    if (empty($username)) {
        $errors[] = 'Username is required';
    }

    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        // Get user from database
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Password correct!

            // Regenerate session ID (security)
            session_regenerate_id(true);

            // Store user data in session
            session_set('user_id', $user['id']);
            session_set('username', $user['username']);
            session_set('logged_in', true);

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            // Wrong username or password
            $errors[] = 'Invalid username or password';
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
    <title>Login</title>
    <style>
        .error { color: red; background: #ffe6e6; padding: 10px; margin: 10px 0; }
        .success { color: green; background: #e6ffe6; padding: 10px; margin: 10px 0; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { padding: 8px; width: 300px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Login</h1>

    <?php if ($success): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($oldUsername) ?>" required>
        </div>

        <div class="form-group">
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Login</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>
</html>
```

**Key points:**
1. Fetch user by username
2. Use `password_verify()` to check password
3. Regenerate session ID after successful login
4. Store user info in session
5. Never reveal if username or password was wrong (security)

---

## Password Rehashing

Over time, better algorithms become available, or you may want to increase the cost factor. PHP provides a way to check if a password needs rehashing:

```php
<?php
// During login, after successful password verification
if (password_verify($password, $user['password'])) {
    // Login successful

    // Check if password needs rehashing
    if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
        // Rehash with new algorithm/cost
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        // Update in database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $user['id']]);
    }

    // Continue with login...
}
?>
```

**When would this trigger?**
- PHP upgrades and `PASSWORD_DEFAULT` changes
- You change the cost factor
- You switch from `PASSWORD_BCRYPT` to `PASSWORD_ARGON2ID`

The rehash happens transparently during login - user doesn't notice anything!

---

## Common Mistakes and How to Avoid Them

### Mistake 1: Storing Plain Text Passwords

```php
// WRONG!
$sql = "INSERT INTO users (password) VALUES ('{$_POST['password']}')";
```

**Fix:** Always hash passwords.

### Mistake 2: Using MD5 or SHA1

```php
// WRONG! MD5 and SHA1 are broken!
$hash = md5($password);
$hash = sha1($password);
```

These algorithms are fast (bad for passwords!) and have known vulnerabilities.

**Fix:** Use `password_hash()`.

### Mistake 3: Manual Salting

```php
// WRONG! Don't do this manually!
$salt = uniqid();
$hash = hash('sha256', $password . $salt);
```

You'll likely do it wrong. Let PHP handle it.

**Fix:** Use `password_hash()` - it handles salting automatically.

### Mistake 4: Comparing Hashes Directly

```php
// WRONG! Vulnerable to timing attacks!
if ($hash1 === $hash2) {
    // Login
}
```

**Fix:** Use `password_verify()` which is timing-attack resistant.

### Mistake 5: Weak Password Requirements

```php
// WRONG! Too weak!
if (strlen($password) >= 6) {
    // OK
}
```

"123456" would pass this check!

**Fix:** Enforce strong passwords:

```php
function validatePasswordStrength($password) {
    $errors = [];

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
        $errors[] = 'Password must contain at least one special character';
    }

    return $errors;
}
```

---

## Database Schema for Users

Your `users` table should store the hashed password:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,  -- Store the hash here
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Why VARCHAR(255)?**
- Bcrypt produces 60-character strings
- Argon2 produces longer strings
- Future algorithms might need more space
- 255 is safe for any algorithm

---

## Security Best Practices Summary

1. **Never store plain text passwords** - Always use `password_hash()`
2. **Use PASSWORD_DEFAULT** - Gets latest algorithm automatically
3. **Enforce strong passwords** - Min 8 chars, mixed case, numbers, symbols
4. **Use password_verify()** - Timing-attack resistant
5. **Regenerate session ID on login** - Prevents session fixation
6. **Don't reveal if username or password was wrong** - Generic "invalid credentials" message
7. **Implement account lockout** - Prevent brute force (we'll cover later)
8. **Use HTTPS** - Passwords should never be transmitted over HTTP
9. **Consider password_needs_rehash()** - Upgrade old hashes transparently
10. **Validate on server** - Never trust client-side validation alone

---

## Testing Your Password Hashing

Here's a simple test script:

```php
<?php
// test-password.php

$password = "TestPassword123!";

echo "Original password: $password\n\n";

// Generate hash
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated hash:\n$hash\n\n";

// Test correct password
if (password_verify($password, $hash)) {
    echo "✓ Correct password verified\n";
} else {
    echo "✗ Correct password NOT verified (BUG!)\n";
}

// Test wrong password
if (password_verify("WrongPassword", $hash)) {
    echo "✗ Wrong password verified (BUG!)\n";
} else {
    echo "✓ Wrong password correctly rejected\n";
}

// Test hash uniqueness
$hash2 = password_hash($password, PASSWORD_DEFAULT);
echo "\nSecond hash of same password:\n$hash2\n";
echo ($hash === $hash2 ? "✗ Hashes are identical (missing salt!)\n" : "✓ Hashes are different (salting working)\n");

// Test rehashing
if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
    echo "⚠ Hash needs rehashing\n";
} else {
    echo "✓ Hash is up to date\n";
}
?>
```

Run this to verify your password hashing is working correctly!

---

## What's Next?

You now understand:
- Why password hashing is critical
- How hashing differs from encryption
- How salts prevent rainbow table attacks
- How to use `password_hash()` and `password_verify()`
- How to validate password strength
- How to implement secure registration and login

**In Lesson 04**, we'll build a complete **registration system** with:
- Form validation
- Username/email uniqueness checks
- Error handling
- Success messages

**In Lesson 05**, we'll build a complete **login system** with:
- Authentication
- Session management
- Login attempt tracking
- "Remember Me" preparation

After that, you'll have a fully functional authentication system!

---

## Laravel Preview

**Now (Pure PHP):**
```php
$hash = password_hash($password, PASSWORD_DEFAULT);
if (password_verify($password, $hash)) {
    // Login
}
```

**Laravel:**
```php
use Illuminate\Support\Facades\Hash;

$hash = Hash::make($password);
if (Hash::check($password, $hash)) {
    // Login
}
```

Laravel's `Hash` facade uses the same `password_hash()` and `password_verify()` functions under the hood! The concepts are identical - Laravel just wraps them in a nicer API.

You now understand what Laravel's authentication does behind the scenes!
