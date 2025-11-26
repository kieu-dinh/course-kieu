# Lesson 02 - Sanitization: Cleaning User Input

**Duration**: 45-60 minutes

---

## Introduction: Defense in Depth

In the last lesson, we learned to **validate** input - to check if it's acceptable. But validation isn't always enough.

**Sanitization** is the process of cleaning data by:
- Removing unwanted characters
- Encoding special characters
- Converting data to a safe format

Think of it like this:
- **Validation** = "Is this person allowed in the club?" (Check ID)
- **Sanitization** = "Make sure they're not bringing weapons inside" (Security check)

Both are necessary for complete security!

---

## When to Use Validation vs Sanitization

### Validation First

**Always validate before sanitizing**:

```php
// GOOD - Validate first
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}
// Now we know it's a valid email format

// Then sanitize if storing/displaying
$cleanEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
```

### Common Use Cases

**Validation only**:
- Password (never modify, just check strength)
- Numeric values (age, price, quantity)
- Enums (country codes, status values)

**Validation + Sanitization**:
- Email (validate format, sanitize for display)
- Usernames (validate pattern, sanitize for output)
- URLs (validate format, sanitize for display)
- Text content (validate length, sanitize for XSS)

**Never sanitize passwords!**
```php
// WRONG - Never sanitize passwords
$password = strip_tags($_POST['password']); // NO!
$password = htmlspecialchars($_POST['password']); // NO!

// RIGHT - Hash as-is (after validation)
$password = $_POST['password'];
if (strlen($password) < 8) {
    die('Password too short');
}
$hash = password_hash($password, PASSWORD_DEFAULT);
```

---

## Types of Sanitization

### 1. Output Sanitization (Most Important!)

**The Golden Rule**: Sanitize data when displaying it, not when storing it.

```php
// Store the raw data
$comment = $_POST['comment'];
// ... validate length, etc ...
$db->query("INSERT INTO comments (content) VALUES (?)", [$comment]);

// Sanitize when displaying
echo "<p>" . htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') . "</p>";
```

**Why store raw?**
- You might need the original data
- Different contexts need different sanitization
- Easier to fix vulnerabilities later (just update display code)

#### htmlspecialchars() - Your Best Friend

```php
$userInput = "<script>alert('XSS')</script>";

// Without sanitization - XSS ATTACK!
echo $userInput;
// Output: <script>alert('XSS')</script>
// Browser executes the script!

// With sanitization - SAFE
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
// Output: &lt;script&gt;alert('XSS')&lt;/script&gt;
// Browser displays it as text, doesn't execute!
```

**What it does**:
- `<` becomes `&lt;`
- `>` becomes `&gt;`
- `"` becomes `&quot;`
- `'` becomes `&#039;`
- `&` becomes `&amp;`

**Always use these parameters**:
```php
htmlspecialchars($data, ENT_QUOTES, 'UTF-8')
```

- `ENT_QUOTES` - Encode both double and single quotes
- `'UTF-8'` - Handle international characters correctly

#### Common Output Contexts

**In HTML content**:
```php
<p><?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?></p>
```

**In HTML attributes**:
```php
<input type="text" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
```

**In URLs**:
```php
<a href="search.php?q=<?= urlencode($searchTerm) ?>">Search</a>
```

**In JavaScript strings**:
```php
<script>
var username = <?= json_encode($username, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
```

### 2. Input Sanitization

Sometimes you need to clean input before storing it.

#### Email Sanitization

```php
$email = $_POST['email'];

// Remove invalid characters
$email = filter_var($email, FILTER_SANITIZE_EMAIL);

// Then validate
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}

// Example:
$dirty = 'john@exa<>mple.com';
$clean = filter_var($dirty, FILTER_SANITIZE_EMAIL);
// Result: 'john@example.com'
```

#### URL Sanitization

```php
$url = $_POST['website'];

// Remove invalid characters
$url = filter_var($url, FILTER_SANITIZE_URL);

// Then validate
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    die('Invalid URL');
}

// Only allow http/https
$scheme = parse_url($url, PHP_URL_SCHEME);
if (!in_array($scheme, ['http', 'https'])) {
    die('URL must use http or https');
}
```

#### String Sanitization

```php
// Remove HTML tags
$bio = strip_tags($_POST['bio']);

// Or allow some tags
$bio = strip_tags($_POST['bio'], '<b><i><u><a>');

// Trim whitespace
$username = trim($_POST['username']);

// Remove multiple spaces
$title = preg_replace('/\s+/', ' ', $_POST['title']);
```

### 3. Database Sanitization

**Use prepared statements - ALWAYS!**

```php
// WRONG - Vulnerable to SQL injection
$username = mysqli_real_escape_string($conn, $_POST['username']);
$query = "SELECT * FROM users WHERE username = '$username'";

// RIGHT - Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_POST['username']]);

// Prepared statements handle ALL sanitization for SQL
// You don't need to escape anything!
```

We covered this in Module 06, but it's worth repeating: **Never build SQL with string concatenation. Use prepared statements.**

### 4. Filename Sanitization

**File uploads are extremely dangerous** without sanitization!

```php
$originalName = $_FILES['document']['name'];

// Remove path characters
$originalName = basename($originalName);

// Remove special characters, keep only safe ones
$safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);

// Ensure unique name
$uniqueName = uniqid() . '_' . $safeName;

// Validate extension
$extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
$allowed = ['jpg', 'png', 'pdf', 'docx'];
if (!in_array($extension, $allowed)) {
    die('File type not allowed');
}

// Example:
// Original: "my document (1).pdf"
// Safe: "my_document__1_.pdf"
// Final: "6548a2f3b8e99_my_document__1_.pdf"
```

---

## PHP's Sanitization Functions

### filter_var() Sanitization Filters

```php
// Email
$email = filter_var($input, FILTER_SANITIZE_EMAIL);

// URL
$url = filter_var($input, FILTER_SANITIZE_URL);

// String (strips tags and special chars)
$string = filter_var($input, FILTER_SANITIZE_STRING); // Deprecated in PHP 8.1

// Number (remove all except digits, +, -)
$number = filter_var($input, FILTER_SANITIZE_NUMBER_INT);

// Float (remove all except digits, +, -, .)
$price = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// Special chars (HTML encode)
$text = filter_var($input, FILTER_SANITIZE_SPECIAL_CHARS);
```

**Note**: `FILTER_SANITIZE_STRING` is deprecated in PHP 8.1+. Use `htmlspecialchars()` instead.

### String Manipulation Functions

```php
// Remove whitespace from both ends
$clean = trim($input);

// Remove HTML and PHP tags
$clean = strip_tags($input);

// Allow specific tags
$clean = strip_tags($input, '<p><br><b><i>');

// Convert to lowercase
$clean = strtolower($input);

// Convert to uppercase
$clean = strtoupper($input);

// Remove multiple spaces
$clean = preg_replace('/\s+/', ' ', $input);

// Remove non-alphanumeric
$clean = preg_replace('/[^a-zA-Z0-9]/', '', $input);
```

---

## Creating a Sanitization Helper Class

```php
<?php
class Sanitizer {

    /**
     * Sanitize for HTML output (prevents XSS)
     */
    public static function html(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     */
    public static function email(string $email): string {
        $email = trim($email);
        $email = strtolower($email);
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     */
    public static function url(string $url): string {
        $url = trim($url);
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize string (remove tags, trim)
     */
    public static function string(string $value): string {
        $value = trim($value);
        $value = strip_tags($value);
        return $value;
    }

    /**
     * Sanitize integer
     */
    public static function int(mixed $value): int {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     */
    public static function float(mixed $value): float {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize filename
     */
    public static function filename(string $filename): string {
        // Get basename (removes path)
        $filename = basename($filename);

        // Replace spaces with underscores
        $filename = str_replace(' ', '_', $filename);

        // Remove anything that's not alphanumeric, underscore, dash, or dot
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Limit length
        if (strlen($filename) > 200) {
            $filename = substr($filename, 0, 200);
        }

        return $filename;
    }

    /**
     * Sanitize for use in URLs (slug)
     */
    public static function slug(string $value): string {
        // Convert to lowercase
        $value = strtolower($value);

        // Replace spaces with dashes
        $value = str_replace(' ', '-', $value);

        // Remove special characters
        $value = preg_replace('/[^a-z0-9-]/', '', $value);

        // Remove multiple dashes
        $value = preg_replace('/-+/', '-', $value);

        // Trim dashes from ends
        $value = trim($value, '-');

        return $value;
    }

    /**
     * Sanitize phone number (keep only digits)
     */
    public static function phone(string $phone): string {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Sanitize boolean
     */
    public static function bool(mixed $value): bool {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Sanitize array of strings
     */
    public static function arrayOfStrings(array $array): array {
        return array_map([self::class, 'string'], $array);
    }
}
```

### Using the Sanitizer

```php
<?php
require_once 'Sanitizer.php';

// Display user input (XSS protection)
$comment = $_POST['comment'];
echo "<p>" . Sanitizer::html($comment) . "</p>";

// Process email
$email = Sanitizer::email($_POST['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}

// Create URL slug
$title = "My First Blog Post!";
$slug = Sanitizer::slug($title);
// Result: "my-first-blog-post"

// Safe filename
$originalName = $_FILES['file']['name'];
$safeName = Sanitizer::filename($originalName);

// Phone number
$phone = Sanitizer::phone("(555) 123-4567");
// Result: "5551234567"
```

---

## Context-Specific Sanitization

Different contexts require different sanitization:

### 1. HTML Context

```php
// In HTML content
<h1><?= Sanitizer::html($title) ?></h1>
<p><?= Sanitizer::html($description) ?></p>

// In HTML attributes
<input type="text" value="<?= Sanitizer::html($username) ?>">
<img src="avatar.jpg" alt="<?= Sanitizer::html($altText) ?>">
```

### 2. URL Context

```php
// In href attribute
<a href="search.php?q=<?= urlencode($query) ?>">Search</a>

// In URL path
<a href="user/<?= urlencode($username) ?>">Profile</a>

// Full URL
$cleanUrl = filter_var($url, FILTER_SANITIZE_URL);
if (filter_var($cleanUrl, FILTER_VALIDATE_URL)) {
    echo '<a href="' . Sanitizer::html($cleanUrl) . '">Link</a>';
}
```

### 3. JavaScript Context

```php
// NEVER do this:
<script>
var username = "<?= $username ?>"; // VULNERABLE!
</script>

// DO this:
<script>
var username = <?= json_encode($username, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>

// json_encode handles all escaping for JavaScript context
// Flags prevent </script> injection
```

### 4. CSS Context

```php
// Avoid user input in CSS if possible
// If you must:
<style>
.user-color {
    /* Only allow hex colors after validation */
    color: <?= preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : '#000000' ?>;
}
</style>
```

### 5. SQL Context

```php
// Use prepared statements - they handle all sanitization
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// NEVER use mysqli_real_escape_string or similar
// Prepared statements are the only correct way
```

---

## Common Sanitization Mistakes

### Mistake 1: Double Encoding

```php
// WRONG - Double encoding
$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
$db->query("INSERT INTO comments (content) VALUES (?)", [$comment]);

// Later...
echo htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

// Result: User sees "&lt;script&gt;" instead of "<script>"
// The entities are encoded twice!
```

**Fix**: Store raw, sanitize on output:

```php
// RIGHT - Store raw
$comment = $_POST['comment'];
$db->query("INSERT INTO comments (content) VALUES (?)", [$comment]);

// Sanitize when displaying
echo htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
```

### Mistake 2: Wrong Context

```php
// WRONG - Using HTML sanitization for URL
<a href="<?= htmlspecialchars($url) ?>">Link</a>

// WRONG - Using URL encoding for HTML
<p><?= urlencode($comment) ?></p>
```

**Fix**: Use correct sanitization for context:

```php
// RIGHT
<a href="<?= urlencode($url) ?>">Link</a>
<p><?= htmlspecialchars($comment, ENT_QUOTES, 'UTF-8') ?></p>
```

### Mistake 3: Sanitizing Passwords

```php
// WRONG - Never sanitize passwords
$password = strip_tags($_POST['password']);
$password = htmlspecialchars($password);

// This might remove valid characters from the password!
```

**Fix**: Hash passwords as-is:

```php
// RIGHT - No sanitization, just hash
$password = $_POST['password'];
$hash = password_hash($password, PASSWORD_DEFAULT);
```

### Mistake 4: Trusting Sanitization Alone

```php
// WRONG - Sanitization without validation
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
// What if sanitization produced an empty string?
// What if the result is still invalid?
```

**Fix**: Always validate after sanitizing:

```php
// RIGHT - Sanitize then validate
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}
```

---

## Sanitization vs Validation: Complete Example

```php
<?php
require_once 'Validator.php';
require_once 'Sanitizer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw input
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $website = $_POST['website'] ?? '';
    $bio = $_POST['bio'] ?? '';

    // Sanitize input
    $username = Sanitizer::string($username);
    $email = Sanitizer::email($email);
    $website = Sanitizer::url($website);
    $bio = Sanitizer::string($bio);

    // Validate sanitized input
    $validator = new Validator();

    $validator->required('username', $username);
    $validator->length('username', $username, 3, 20);
    $validator->pattern('username', $username, '/^[a-zA-Z0-9_-]+$/');

    $validator->required('email', $email);
    $validator->email('email', $email);

    if (!empty($website)) {
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $validator->errors['website'] = 'Invalid URL';
        }
    }

    $validator->length('bio', $bio, 0, 500);

    // Check if valid
    if ($validator->passes()) {
        // Store in database (prepared statements handle SQL sanitization)
        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, website, bio)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$username, $email, $website, $bio]);

        echo "Registration successful!";
    } else {
        // Show errors
        foreach ($validator->getErrors() as $error) {
            echo "<p style='color:red;'>" . Sanitizer::html($error) . "</p>";
        }
    }
}
?>

<form method="POST">
    <!-- Sanitize when displaying previous input -->
    <input type="text" name="username" value="<?= Sanitizer::html($_POST['username'] ?? '') ?>">
    <input type="email" name="email" value="<?= Sanitizer::html($_POST['email'] ?? '') ?>">
    <input type="url" name="website" value="<?= Sanitizer::html($_POST['website'] ?? '') ?>">
    <textarea name="bio"><?= Sanitizer::html($_POST['bio'] ?? '') ?></textarea>
    <button type="submit">Register</button>
</form>
```

---

## The Complete Security Flow

```
User Input
    ↓
1. Sanitize (clean)
    ↓
2. Validate (check)
    ↓
3. Store (prepared statements)
    ↓
4. Retrieve from database
    ↓
5. Sanitize for output context (HTML, URL, JS, etc.)
    ↓
Display to User
```

---

## Quick Reference: Sanitization Checklist

**For every piece of user data**:

- [ ] **Input Phase**: Sanitize basic issues (trim, remove tags, etc.)
- [ ] **Validation Phase**: Check if data is acceptable
- [ ] **Storage Phase**: Use prepared statements (SQL sanitization)
- [ ] **Output Phase**: Sanitize for context (HTML, URL, JS)

**Context-specific sanitization**:

- [ ] **HTML**: `htmlspecialchars($data, ENT_QUOTES, 'UTF-8')`
- [ ] **URL**: `urlencode($data)`
- [ ] **JavaScript**: `json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP)`
- [ ] **SQL**: Use prepared statements (never manual escaping)
- [ ] **Filename**: `preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename))`

---

## Practice Exercise

Create a file `sanitization-test.php`:

```php
<?php
// Test different sanitization scenarios

// Test 1: HTML output
$comment = '<script>alert("XSS")</script>Hello World!';
echo "Comment: " . /* TODO: Sanitize for HTML */ . "<br>";

// Test 2: URL parameter
$searchQuery = 'php & mysql tutorial';
echo '<a href="search.php?q=' . /* TODO: Sanitize for URL */ . '">Search</a><br>';

// Test 3: JavaScript variable
$username = 'John "The Hacker" Doe';
echo '<script>var name = ' . /* TODO: Sanitize for JS */ . ';</script>';

// Test 4: Filename
$uploadedFile = '../../../etc/passwd';
$safeName = /* TODO: Sanitize filename */;
echo "Safe filename: $safeName<br>";

// Test 5: Email
$email = 'john@EXAMPLE..COM<script>';
$cleanEmail = /* TODO: Sanitize email */;
echo "Clean email: $cleanEmail<br>";

// Test 6: Slug
$title = 'How to Learn PHP in 2024!';
$slug = /* TODO: Create URL slug */;
echo "Slug: $slug<br>";
```

**Expected output**:
- Comment displays as text (no script execution)
- Search link works with special characters
- JavaScript variable is safe
- Filename has no path traversal
- Email is clean and lowercase
- Slug is URL-friendly

---

## Key Takeaways

1. **Sanitization cleans data** - Removes or encodes dangerous characters
2. **Different contexts need different sanitization** - HTML, URL, JS, SQL, files
3. **htmlspecialchars() for HTML** - Your primary weapon against XSS
4. **Store raw, sanitize on output** - Prevents double encoding
5. **Sanitize + Validate** - Both are necessary, neither is sufficient alone
6. **Never sanitize passwords** - Hash them as-is
7. **Prepared statements for SQL** - They handle all SQL sanitization
8. **Context matters** - Use the right sanitization for the right place

---

## What's Next?

Now that you understand validation and sanitization, we'll dive deep into **XSS (Cross-Site Scripting)** attacks - one of the most common and dangerous web vulnerabilities.

You'll learn:
- How XSS attacks work
- Real-world examples of XSS breaches
- How attackers exploit XSS
- Advanced XSS prevention techniques

Get ready - this is where security gets really interesting!
