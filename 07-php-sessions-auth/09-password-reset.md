# Lesson 09 - Password Reset: Secure Account Recovery

**Duration**: 1.5 hours
**Objectives**: Implement secure password reset functionality with email tokens and proper validation

---

## What is Password Reset?

**Problem:** Users forget passwords (happens ALL the time).

**Solution:** Password reset flow:
1. User requests password reset
2. System generates unique token
3. System sends reset link via email
4. User clicks link and sets new password
5. Token expires after use or timeout

**Real-world example:** Every major website has "Forgot Password?" - you'll build exactly that.

---

## The Complete Flow

```
1. User clicks "Forgot Password?"
   ↓
2. Enters email address
   ↓
3. System generates random token
   ↓
4. System stores token in database with expiration
   ↓
5. System sends email with reset link: reset.php?token=xxx
   ↓
6. User clicks link in email
   ↓
7. System verifies token is valid and not expired
   ↓
8. User enters new password
   ↓
9. System updates password and deletes token
   ↓
10. User can now login with new password
```

---

## Security Considerations

### What NOT to Do

**NEVER do this:**

```php
// BAD: Token is user ID (predictable!)
$token = $userId;

// BAD: Token is based on email (predictable!)
$token = md5($email);

// BAD: No expiration
// Token works forever!

// BAD: Don't reveal if email exists
if ($user) {
    echo "Reset link sent!";
} else {
    echo "Email not found!";
    // Attacker now knows which emails are registered!
}
```

### The Right Way

- **Random tokens:** Use cryptographically secure random
- **Expiration:** Tokens expire after 1 hour
- **One-time use:** Token deleted after use
- **Rate limiting:** Prevent spam
- **No information leak:** Don't reveal if email exists
- **Email verification:** Only send to registered emails

---

## Database Schema

```sql
-- database/schema.sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,  -- SHA-256 hash of token
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,

    INDEX idx_email (email),
    INDEX idx_token_hash (token_hash),
    INDEX idx_expires (expires_at)
);
```

**Note:** We don't use a foreign key to `users.id` because:
- Email might not exist (but we don't want to reveal that)
- User might be deleted
- Simpler to query by email directly

---

## Step 1: Request Password Reset

```php
<?php
// public/forgot-password.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
guest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize(post('email'));

    $errors = [];

    // Validate email format
    $emailErrors = validateEmail($email);
    if (!empty($emailErrors)) {
        $errors = array_merge($errors, $emailErrors);
    }

    if (empty($errors)) {
        // Check if email exists (but don't reveal this to user!)
        $stmt = $pdo->prepare("SELECT id, email, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // User exists - create reset token

            // Generate cryptographically secure token
            $token = bin2hex(random_bytes(32)); // 64 hex characters
            $tokenHash = hash('sha256', $token);

            // Token expires in 1 hour
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);

            // Delete any existing reset tokens for this email
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Store new token
            $stmt = $pdo->prepare("
                INSERT INTO password_resets (email, token_hash, expires_at)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$email, $tokenHash, $expiresAt]);

            // Send email with reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;

            // In production, send real email
            // For now, we'll simulate it
            sendPasswordResetEmail($user['email'], $user['username'], $resetLink);

            // Log this action
            error_log("Password reset requested for: $email");
        }

        // ALWAYS show success message (even if email doesn't exist)
        // This prevents email enumeration attacks
        session_set('success', 'If that email exists, a password reset link has been sent.');
        header('Location: forgot-password.php');
        exit;
    }

    if (!empty($errors)) {
        session_set('errors', $errors);
        session_set('old_email', $email);
    }
}

$errors = session_flash('errors', []);
$success = session_flash('success');
$oldEmail = session_flash('old_email', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
            line-height: 1.6;
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
        <h1>Forgot Password?</h1>
        <p class="subtitle">
            Enter your email address and we'll send you a link to reset your password.
        </p>

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
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($oldEmail) ?>"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn">Send Reset Link</button>
        </form>

        <div class="footer">
            Remember your password? <a href="login.php">Log in</a>
        </div>
    </div>
</body>
</html>
```

---

## Step 2: Email Simulation

For development, we'll simulate email sending. In production, use a real email service.

```php
<?php
// includes/email.php

/**
 * Send password reset email (simulated for development)
 *
 * In production, use:
 * - PHP mail() function
 * - PHPMailer library
 * - Email service (SendGrid, Mailgun, Amazon SES)
 */
function sendPasswordResetEmail(string $email, string $username, string $resetLink): void
{
    $subject = "Password Reset Request";

    $message = "
    Hello $username,

    You requested a password reset for your account.

    Click the link below to reset your password:
    $resetLink

    This link will expire in 1 hour.

    If you didn't request this, please ignore this email.

    Thanks,
    The Team
    ";

    // For development: Save to file instead of sending
    $logFile = __DIR__ . '/../storage/emails.log';
    $logEntry = "
========================================
TO: $email
SUBJECT: $subject
TIME: " . date('Y-m-d H:i:s') . "
RESET LINK: $resetLink
========================================
$message
========================================

";

    file_put_contents($logFile, $logEntry, FILE_APPEND);

    // In production, uncomment this:
    // mail($email, $subject, $message, "From: noreply@yoursite.com");

    // Or use PHPMailer, SendGrid, etc.
}

/**
 * Send password changed confirmation email
 */
function sendPasswordChangedEmail(string $email, string $username): void
{
    $subject = "Password Changed Successfully";

    $message = "
    Hello $username,

    Your password has been changed successfully.

    If you didn't make this change, please contact us immediately.

    Thanks,
    The Team
    ";

    // For development: Log to file
    $logFile = __DIR__ . '/../storage/emails.log';
    $logEntry = "
========================================
TO: $email
SUBJECT: $subject
TIME: " . date('Y-m-d H:i:s') . "
========================================
$message
========================================

";

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}
?>
```

---

## Step 3: Reset Password Form

```php
<?php
// public/reset-password.php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/validation.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../config/database.php';

// Redirect if already logged in
guest();

// Get token from URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    session_set('error', 'Invalid reset link.');
    header('Location: forgot-password.php');
    exit;
}

// Verify token exists and is not expired
$tokenHash = hash('sha256', $token);

$stmt = $pdo->prepare("
    SELECT email, expires_at
    FROM password_resets
    WHERE token_hash = ?
");
$stmt->execute([$tokenHash]);
$resetRecord = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resetRecord) {
    session_set('error', 'Invalid or expired reset link.');
    header('Location: forgot-password.php');
    exit;
}

// Check if token expired
if (strtotime($resetRecord['expires_at']) < time()) {
    // Delete expired token
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token_hash = ?");
    $stmt->execute([$tokenHash]);

    session_set('error', 'Reset link has expired. Please request a new one.');
    header('Location: forgot-password.php');
    exit;
}

// Token is valid!
$email = $resetRecord['email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = post('password');
    $confirmPassword = post('confirm_password');

    $errors = [];

    // Validate password
    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        $errors = array_merge($errors, $passwordErrors);
    }

    // Check confirmation
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        // Get user
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);

            // Delete reset token (one-time use)
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->execute([$email]);

            // Delete all remember-me tokens (force re-login on all devices)
            $stmt = $pdo->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
            $stmt->execute([$user['id']]);

            // Send confirmation email
            sendPasswordChangedEmail($email, $user['username']);

            // Log this action
            error_log("Password reset completed for: $email");

            // Redirect to login
            session_set('success', 'Password changed successfully! Please log in.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'User account not found.';
        }
    }

    if (!empty($errors)) {
        session_set('errors', $errors);
    }
}

$errors = session_flash('errors', []);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .email-info {
            background: #f0f0f0;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #666;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Reset Your Password</h1>
        <p class="subtitle">Enter your new password below.</p>

        <div class="email-info">
            Resetting password for: <strong><?= htmlspecialchars($email) ?></strong>
        </div>

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
                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="8"
                    autocomplete="new-password"
                >
                <div class="hint">Min 8 characters with uppercase, lowercase, number, and special character.</div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    autocomplete="new-password"
                >
            </div>

            <button type="submit" class="btn">Reset Password</button>
        </form>
    </div>
</body>
</html>
```

---

## Step 4: Cleanup Old Tokens

Create a cleanup script to run periodically:

```php
<?php
// cron/cleanup-password-resets.php
require_once __DIR__ . '/../config/database.php';

// Delete expired tokens
$stmt = $pdo->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
$stmt->execute();

$deleted = $stmt->rowCount();

echo "Deleted $deleted expired password reset tokens.\n";
?>
```

Run this via cron:

```bash
# Every hour
0 * * * * php /path/to/cron/cleanup-password-resets.php
```

---

## Step 5: Rate Limiting

Prevent abuse by limiting reset requests:

```php
<?php
// includes/rate-limiter.php

/**
 * Check if IP has exceeded rate limit for password resets
 */
function checkPasswordResetRateLimit(PDO $pdo, string $ip, int $maxAttempts = 3, int $windowMinutes = 15): bool
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM password_resets
        WHERE created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmt->execute([$windowMinutes]);

    $count = $stmt->fetchColumn();

    return $count < $maxAttempts;
}
?>
```

Use in forgot-password.php:

```php
<?php
// In forgot-password.php, before processing

if (!checkPasswordResetRateLimit($pdo, $_SERVER['REMOTE_ADDR'])) {
    $errors[] = 'Too many reset requests. Please try again later.';
}
?>
```

---

## Security Checklist

- [ ] **Random tokens** - Use cryptographically secure random
- [ ] **Hash tokens** - Store SHA-256 hash in database
- [ ] **Expiration** - Tokens expire after 1 hour
- [ ] **One-time use** - Delete token after use
- [ ] **No information leak** - Don't reveal if email exists
- [ ] **Rate limiting** - Max 3 requests per 15 minutes
- [ ] **Force re-login** - Delete remember-me tokens
- [ ] **Send confirmation** - Email user after password change
- [ ] **Log actions** - Track reset requests and completions
- [ ] **HTTPS only** - Never send tokens over HTTP

---

## Common Issues

### Issue 1: "Invalid reset link"

**Causes:**
- Token manually modified
- Token already used
- Token expired
- Database issue

**Debug:**
```php
echo "Token from URL: " . $_GET['token'] . "<br>";
echo "Token hash: " . hash('sha256', $_GET['token']) . "<br>";

// Check database
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token_hash = ?");
$stmt->execute([hash('sha256', $_GET['token'])]);
var_dump($stmt->fetch());
```

### Issue 2: Email not sending

**Causes:**
- PHP mail() not configured
- Email service credentials wrong
- Firewall blocking

**Fix:**
- Check `storage/emails.log` for simulated emails
- Configure SMTP properly
- Use email service (SendGrid, etc.)

---

## What's Next?

You now understand password reset:
- Token generation and validation
- Email sending (simulated)
- Security best practices
- Rate limiting

**In Lesson 10**, we'll cover proper **logout** handling including session and token cleanup.

**In Lesson 11**, we'll review **authentication security** - covering all common vulnerabilities and how to prevent them.

---

## Laravel Preview

**Now (Pure PHP):**
```php
$token = bin2hex(random_bytes(32));
$stmt->execute([$email, hash('sha256', $token), $expiresAt]);
```

**Laravel:**
```php
// Built-in password reset
use Illuminate\Support\Facades\Password;

Password::sendResetLink(['email' => $email]);

// Laravel handles tokens, emails, expiration automatically!
```

Laravel provides:
- Password reset routes
- Email templates
- Token management
- Throttling

But now you understand what `Password::sendResetLink()` does behind the scenes!
