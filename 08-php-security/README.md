# Module 08 - Security & Validation (Pure PHP)

**Duration**: 2-3 weeks
**Prerequisites**: Module 07 - Sessions & Auth

---

## Learning Objectives

By the end of this module, you will be able to:
- Validate and sanitize all user input
- Prevent Cross-Site Scripting (XSS) attacks
- Implement CSRF protection from scratch
- Prevent SQL injection (review + advanced)
- Handle file uploads securely
- Understand and prevent common web vulnerabilities
- Build secure forms
- Implement rate limiting basics
- Understand Laravel's security features later

---

## Why This Module Is Critical

**Security is not optional.** A single vulnerability can:
- Expose user data (emails, passwords, personal info)
- Allow attackers to take over accounts
- Deface your website
- Steal sensitive information
- Destroy your reputation

**Good news**: Most attacks are preventable with proper techniques!

---

## The OWASP Top 10 (What We'll Cover)

1. **Injection** (SQL, Command) - Already covered, reinforced here
2. **Broken Authentication** - Covered in Module 07
3. **Sensitive Data Exposure** - Encryption, HTTPS
4. **XML External Entities (XXE)** - Brief mention
5. **Broken Access Control** - Authorization checks
6. **Security Misconfiguration** - Proper settings
7. **Cross-Site Scripting (XSS)** ⭐ Major focus
8. **Insecure Deserialization** - Be aware of
9. **Using Components with Known Vulnerabilities** - Keep updated
10. **Insufficient Logging** - Track what happens

---

## What You'll Learn

### 1. Input Validation
- Never trust user input
- Whitelist vs blacklist
- Validation functions
- Type checking
- Length validation
- Format validation (email, URL, etc.)

### 2. XSS (Cross-Site Scripting)
- What is XSS?
- Reflected XSS
- Stored XSS
- DOM-based XSS
- Preventing XSS with `htmlspecialchars()`
- Content Security Policy (CSP) basics

### 3. CSRF (Cross-Site Request Forgery)
- What is CSRF?
- How CSRF attacks work
- Generating CSRF tokens
- Validating tokens on form submit
- Token storage in sessions
- SameSite cookies

### 4. SQL Injection (Advanced)
- Review of Module 06
- Prepared statements everywhere
- Never concatenate SQL
- Preventing second-order injection
- ORM benefits (preview of Eloquent)

### 5. File Upload Security
- File type validation
- File size limits
- Preventing PHP file uploads
- Secure file storage locations
- Filename sanitization
- Image validation
- Virus scanning (concept)

### 6. Access Control
- Authorization vs Authentication
- Role-based access control (RBAC)
- Permission checks
- Preventing horizontal privilege escalation
- Preventing vertical privilege escalation

### 7. Security Headers
- X-Frame-Options
- X-Content-Type-Options
- X-XSS-Protection
- Content-Security-Policy
- Strict-Transport-Security (HSTS)

---

## Lessons

1. **01-input-validation.md** - Validate everything from users
2. **02-sanitization.md** - Clean user input
3. **03-xss-explained.md** - Understanding XSS attacks
4. **04-xss-prevention.md** - Preventing XSS
5. **05-csrf-explained.md** - Understanding CSRF
6. **06-csrf-implementation.md** - Building CSRF protection
7. **07-sql-injection-review.md** - Advanced SQL injection prevention
8. **08-file-upload-security.md** - Secure file uploads
9. **09-access-control.md** - Authorization and permissions
10. **10-security-headers.md** - HTTP security headers
11. **11-rate-limiting.md** - Preventing brute force
12. **12-security-checklist.md** - Complete security audit

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 8.1 | Input Validation | Build comprehensive validation functions | 2 hours |
| 8.2 | XSS Prevention | Fix a vulnerable comment system | 2 hours |
| 8.3 | CSRF Protection | Implement CSRF tokens from scratch | 3 hours |
| 8.4 | Secure File Upload | Build secure image upload system | 3-4 hours |
| 8.5 | Security Audit | Fix a vulnerable application (complete) | 6-8 hours |

---

## Real-World Examples

### XSS Attack Example
```php
// VULNERABLE CODE:
echo "<h1>Welcome " . $_GET['name'] . "</h1>";

// Attacker visits: site.com?name=<script>alert('XSS')</script>
// Script executes in victim's browser!

// SECURE CODE:
echo "<h1>Welcome " . htmlspecialchars($_GET['name'], ENT_QUOTES, 'UTF-8') . "</h1>";
// Output: Welcome &lt;script&gt;alert('XSS')&lt;/script&gt;
// No execution!
```

### CSRF Attack Example
```php
// Bank transfer form without CSRF protection
// Attacker creates: <img src="bank.com/transfer?to=attacker&amount=1000">
// Victim visits attacker's site while logged into bank
// Transfer happens automatically!

// PREVENTION: CSRF Token
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
```

### Secure File Upload
```php
// VULNERABLE:
move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $_FILES['file']['name']);
// Attacker uploads shell.php, executes code!

// SECURE:
$allowed = ['jpg', 'png', 'gif'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    die('Invalid file type');
}
$newName = bin2hex(random_bytes(16)) . '.' . $ext;
move_uploaded_file($_FILES['file']['tmp_name'], 'uploads/' . $newName);
```

---

## Security Checklist for Every Project

### Input/Output
- [ ] All user input is validated
- [ ] All output is escaped with `htmlspecialchars()`
- [ ] SQL uses prepared statements (no concatenation)
- [ ] File uploads check type, size, and content

### Authentication & Sessions
- [ ] Passwords are hashed (never plain text)
- [ ] Sessions use secure settings
- [ ] Session ID regenerated after login
- [ ] CSRF tokens on all forms
- [ ] Rate limiting on login attempts

### Files & Uploads
- [ ] Uploaded files go outside web root
- [ ] File extensions validated (whitelist)
- [ ] Filename sanitized
- [ ] File size limits enforced

### Access Control
- [ ] Authorization checks on every protected page
- [ ] Users can only access their own data
- [ ] Admin functions require admin role

### Configuration
- [ ] `display_errors = Off` in production
- [ ] Error logging enabled
- [ ] HTTPS enforced
- [ ] Security headers set

---

## Project: Secure Comment System

Build a complete comment system with ALL security features:
- XSS-proof comment display
- CSRF-protected form
- Input validation
- Rate limiting (prevent spam)
- Admin moderation (access control)
- Secure against all common attacks

This project combines everything from Modules 06-08!

---

## Comparison: Now vs Laravel Later

**What you build now (Pure PHP):**
```php
// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF Validation
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die('CSRF validation failed');
}

// XSS Prevention
echo htmlspecialchars($userComment, ENT_QUOTES, 'UTF-8');
```

**What Laravel does (Module 16):**
```blade
{{-- CSRF automatically included --}}
@csrf

{{-- XSS prevention automatic --}}
{{ $userComment }}
```

**But now you understand what `@csrf` and `{{ }}` actually do behind the scenes!**

---

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OWASP Cheat Sheets](https://cheatsheetseries.owasp.org/)
- [PHP Security Guide](https://phptherightway.com/#security)
- [Web Security Academy](https://portswigger.net/web-security)

---

## Next Module

**Module 09 - APIs in Pure PHP**: Learn to build and consume RESTful APIs!
