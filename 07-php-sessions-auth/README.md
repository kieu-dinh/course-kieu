# Module 07 - Sessions & Authentication (Pure PHP)

**Duration**: 2-3 weeks
**Prerequisites**: Module 06 - PHP & MySQL

---

## Learning Objectives

By the end of this module, you will be able to:
- Understand how sessions work in PHP
- Build a complete login/register system from scratch
- Hash and verify passwords securely
- Implement "Remember Me" functionality
- Handle password reset flows
- Protect against common auth vulnerabilities
- Manage user sessions properly
- Build middleware-like protection
- Understand what Laravel does for you later!

---

## Why Learn This in Pure PHP First?

**Later, Laravel will do this automatically.** But by building it yourself, you'll:
- Understand what happens behind the scenes
- Debug auth issues easily
- Appreciate framework magic
- Be able to work on any PHP project, even without frameworks
- Have deeper knowledge than developers who only know Laravel

---

## What You'll Learn

### 1. How Sessions Work
- What is a session?
- Session IDs and cookies
- `session_start()`, `$_SESSION`
- Session security
- Session hijacking prevention

### 2. User Registration
- Registration forms
- Input validation
- Password hashing with `password_hash()`
- Storing users in database
- Email validation
- Username uniqueness

### 3. User Login
- Login forms
- Retrieving user from database
- Password verification with `password_verify()`
- Starting user session
- Redirecting after login

### 4. Session Management
- Checking if user is logged in
- Getting current user
- Protecting pages (middleware concept)
- Logout functionality
- Session timeout

### 5. Remember Me
- Persistent login tokens
- Remember me cookies
- Token storage in database
- Security considerations
- Auto-login on return

### 6. Password Reset
- "Forgot Password" flow
- Generating reset tokens
- Sending reset emails (simulation)
- Token expiration
- Setting new password

### 7. Security Best Practices
- Password requirements
- Rate limiting (basic)
- CSRF tokens (next module!)
- Session fixation prevention
- Secure session configuration

---

## Lessons

1. **01-sessions-intro.md** - What are sessions and how do they work?
2. **02-session-basics.md** - Starting sessions, storing data
3. **03-password-hashing.md** - Secure password storage
4. **04-registration.md** - Building registration system
5. **05-login.md** - Building login system
6. **06-session-management.md** - Managing logged-in users
7. **07-middleware-concept.md** - Protecting pages (auth middleware)
8. **08-remember-me.md** - Persistent login
9. **09-password-reset.md** - Password recovery flow
10. **10-logout.md** - Proper logout handling
11. **11-auth-security.md** - Common vulnerabilities and fixes

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 7.1 | Session Basics | Store and retrieve session data | 1 hour |
| 7.2 | User Registration | Complete registration with validation | 3 hours |
| 7.3 | User Login | Login system with password verification | 2 hours |
| 7.4 | Protected Pages | Check authentication before showing content | 2 hours |
| 7.5 | Remember Me | Implement persistent login | 3-4 hours |
| 7.6 | Password Reset | Complete password recovery flow | 4 hours |
| 7.7 | Complete Auth System | Put it all together | 6-8 hours |

---

## Project Structure

You'll build a complete authentication system:

```
auth-system/
├── config/
│   └── database.php       (DB connection)
├── includes/
│   ├── session.php        (Session management)
│   ├── auth.php          (Auth functions)
│   └── middleware.php    (Protection functions)
├── public/
│   ├── index.php         (Home - protected)
│   ├── login.php         (Login page)
│   ├── register.php      (Registration page)
│   ├── logout.php        (Logout script)
│   ├── forgot-password.php
│   ├── reset-password.php
│   └── dashboard.php     (User dashboard - protected)
└── database/
    └── schema.sql        (Users, tokens tables)
```

---

## Key Concepts

### Password Hashing
```php
// NEVER store plain passwords!
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Verify login
if (password_verify($inputPassword, $hashedPasswordFromDB)) {
    // Login successful
}
```

### Session Check
```php
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}
```

### Remember Me Token
```php
// Generate unique token
$token = bin2hex(random_bytes(32));
// Store in database with user_id
// Set cookie for 30 days
setcookie('remember_token', $token, time() + (86400 * 30));
```

---

## Security Checklist

- [ ] Passwords hashed with bcrypt (never plain text)
- [ ] Password minimum length enforced
- [ ] Session ID regenerated after login (prevent fixation)
- [ ] Remember me tokens are random and unique
- [ ] Tokens expire after period
- [ ] Sessions expire after inactivity
- [ ] Logout clears session and cookies
- [ ] SQL injection prevented (prepared statements)

---

## Comparison: Now vs Laravel Later

**What you build now (Pure PHP):**
```php
// Registration
$hash = password_hash($password, PASSWORD_BCRYPT);
$sql = "INSERT INTO users (email, password) VALUES (:email, :password)";
// ... 20-30 lines of code

// Login
session_start();
$_SESSION['user_id'] = $user['id'];
// ... validation, error handling, etc.
```

**What Laravel does (Module 16):**
```php
// Registration
User::create([
    'email' => $email,
    'password' => Hash::make($password)
]);

// Login
Auth::attempt(['email' => $email, 'password' => $password]);
```

**But now you'll understand what `Hash::make()` and `Auth::attempt()` actually do!**

---

## Resources

- [PHP Sessions Documentation](https://www.php.net/manual/en/book.session.php)
- [Password Hashing](https://www.php.net/manual/en/function.password-hash.php)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

---

## Next Module

**Module 08 - Security & Validation**: Learn to protect your applications from XSS, CSRF, and other attacks!
