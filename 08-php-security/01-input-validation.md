# Lesson 01 - Input Validation: Never Trust User Input

**Duration**: 45-60 minutes

---

## Introduction: The First Line of Defense

Imagine you're a nightclub bouncer. Your job isn't just to let people in—it's to **check IDs, verify ages, and keep troublemakers out**. Input validation is the same thing for your application.

**The Golden Rule of Security**:
> Never trust data from users. Not from forms, not from URLs, not from cookies, not from anywhere.

Every piece of data entering your application is **potentially malicious** until proven otherwise.

---

## Why Input Validation Matters

### Real-World Breach: LinkedIn (2012)

In 2012, 6.5 million LinkedIn passwords were stolen and leaked online. One major contributing factor? **Weak input validation** allowed attackers to probe the system and find vulnerabilities.

**Cost**:
- 6.5 million compromised accounts
- $1.25 million legal settlement
- Massive reputation damage
- Years of lost user trust

### What Happens Without Validation?

```php
// VULNERABLE CODE - No validation
$age = $_POST['age'];
$db->query("UPDATE users SET age = $age WHERE id = 1");

// User sends: age=-999999
// Result: Invalid data in database

// User sends: age=5; DROP TABLE users;--
// Result: SQL injection attack!
```

Without validation, attackers can:
- **Inject malicious code** (SQL, JavaScript)
- **Break your application** (invalid data types)
- **Bypass security** (negative numbers, wrong formats)
- **Cause unexpected behavior** (null values, edge cases)

---

## Types of Input to Validate

### 1. Form Data (`$_POST`, `$_GET`)

The most common source of user input:

```php
$username = $_POST['username'];    // Text input
$email = $_POST['email'];          // Email input
$age = $_POST['age'];              // Number input
$country = $_POST['country'];      // Select dropdown
$newsletter = $_POST['newsletter']; // Checkbox
```

### 2. URL Parameters (`$_GET`)

```php
// URL: profile.php?user_id=123&action=edit
$userId = $_GET['user_id'];
$action = $_GET['action'];
```

### 3. Cookies (`$_COOKIE`)

```php
$theme = $_COOKIE['user_theme'];
$language = $_COOKIE['language'];
```

### 4. File Uploads (`$_FILES`)

```php
$fileName = $_FILES['avatar']['name'];
$fileSize = $_FILES['avatar']['size'];
$fileType = $_FILES['avatar']['type'];
```

### 5. Request Headers (`$_SERVER`)

```php
$userAgent = $_SERVER['HTTP_USER_AGENT'];
$referer = $_SERVER['HTTP_REFERER'];
```

**All of these must be validated before use!**

---

## Validation Strategy: Whitelist vs Blacklist

### Blacklist Approach (BAD)

**Blacklist**: Try to block known bad values.

```php
// BAD - Trying to block bad characters
function validateUsername($username) {
    $blocked = ['<', '>', '"', "'", '&', ';'];
    foreach ($blocked as $char) {
        if (strpos($username, $char) !== false) {
            return false;
        }
    }
    return true;
}

// Problem: What about `, /, \, %, $, etc.?
// Attackers will find characters you forgot!
```

**Why it fails**:
- You can't think of every bad value
- New attack vectors are discovered constantly
- One missed character = vulnerability

### Whitelist Approach (GOOD)

**Whitelist**: Only allow known good values.

```php
// GOOD - Only allow valid characters
function validateUsername($username) {
    // Only allow letters, numbers, underscore, dash
    // Length: 3-20 characters
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username) === 1;
}

// This is explicit and secure
```

**Why it's better**:
- Explicitly define what's acceptable
- Everything else is automatically rejected
- Much harder for attackers to bypass

**Rule**: Always prefer whitelist validation!

---

## Validation Principles

### 1. Validate Type

Check that data is the expected type:

```php
// String
if (!is_string($username)) {
    throw new Exception('Username must be a string');
}

// Integer
if (!is_numeric($age) || $age != (int)$age) {
    throw new Exception('Age must be an integer');
}

// Or use type casting with validation
$age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
if ($age === false) {
    throw new Exception('Invalid age');
}

// Boolean
$newsletter = filter_var($_POST['newsletter'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($newsletter === null) {
    throw new Exception('Invalid checkbox value');
}
```

### 2. Validate Length

Prevent too-short or too-long input:

```php
function validatePassword($password) {
    $length = strlen($password);

    if ($length < 8) {
        return ['valid' => false, 'error' => 'Password must be at least 8 characters'];
    }

    if ($length > 128) {
        return ['valid' => false, 'error' => 'Password too long (max 128 characters)'];
    }

    return ['valid' => true];
}

// Why max length?
// - Prevents DOS attacks (someone sends 1GB password)
// - Prevents buffer overflow issues
// - Database field limits
```

### 3. Validate Format

Check that data matches expected patterns:

```php
// Email
function validateEmail($email) {
    // Check format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    // Check length (email max is 254 chars per RFC)
    if (strlen($email) > 254) {
        return false;
    }

    return true;
}

// URL
function validateUrl($url) {
    // Must be valid URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    // Only allow http/https (no javascript:, data:, etc.)
    $parsed = parse_url($url);
    if (!in_array($parsed['scheme'], ['http', 'https'])) {
        return false;
    }

    return true;
}

// Date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Example:
validateDate('2024-02-30'); // false - invalid date
validateDate('2024-02-28'); // true
```

### 4. Validate Range

Check numeric ranges:

```php
function validateAge($age) {
    // Must be integer
    if (!is_numeric($age) || $age != (int)$age) {
        return ['valid' => false, 'error' => 'Age must be a number'];
    }

    $age = (int)$age;

    // Must be positive
    if ($age < 0) {
        return ['valid' => false, 'error' => 'Age cannot be negative'];
    }

    // Reasonable range
    if ($age < 13) {
        return ['valid' => false, 'error' => 'Must be at least 13 years old'];
    }

    if ($age > 120) {
        return ['valid' => false, 'error' => 'Invalid age'];
    }

    return ['valid' => true, 'value' => $age];
}
```

### 5. Validate Against Allowed Values

For select dropdowns, radio buttons, etc.:

```php
function validateCountry($country) {
    $allowedCountries = [
        'US', 'CA', 'UK', 'FR', 'DE', 'JP', 'VN'
    ];

    if (!in_array($country, $allowedCountries, true)) {
        return false;
    }

    return true;
}

// Why strict comparison (true parameter)?
// in_array('0', [false]) returns true without it!
// Always use strict: in_array($value, $array, true)
```

---

## PHP's Built-in Validation Functions

PHP provides powerful built-in validation via `filter_var()`:

### Common Filters

```php
// Integer
$age = filter_var($_POST['age'], FILTER_VALIDATE_INT);
if ($age === false) {
    die('Invalid age');
}

// Integer with range
$age = filter_var($_POST['age'], FILTER_VALIDATE_INT, [
    'options' => [
        'min_range' => 13,
        'max_range' => 120
    ]
]);

// Float
$price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);

// Boolean
$newsletter = filter_var($_POST['newsletter'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

// Email
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

// URL
$website = filter_var($_POST['website'], FILTER_VALIDATE_URL);

// IP Address
$ip = filter_var($_POST['ip'], FILTER_VALIDATE_IP);

// MAC Address
$mac = filter_var($_POST['mac'], FILTER_VALIDATE_MAC);

// Domain
$domain = filter_var($_POST['domain'], FILTER_VALIDATE_DOMAIN);
```

### Validation vs Sanitization

**Important distinction**:
- **Validation**: Check if data is valid (returns true/false or original/false)
- **Sanitization**: Clean data by removing invalid characters (returns cleaned string)

```php
// VALIDATION - Returns false if invalid
$email = filter_var('john@example..com', FILTER_VALIDATE_EMAIL);
// Result: false (invalid email)

// SANITIZATION - Removes invalid chars
$email = filter_var('john@exa<>mple.com', FILTER_SANITIZE_EMAIL);
// Result: 'john@example.com' (cleaned)
```

**Best practice**: Validate first, sanitize if needed, then validate again!

---

## Building a Validation Helper Class

Let's create a reusable validator:

```php
<?php
class Validator {

    private array $errors = [];

    /**
     * Validate required field
     */
    public function required(string $field, mixed $value): bool {
        if (empty($value) && $value !== '0' && $value !== 0) {
            $this->errors[$field] = "$field is required";
            return false;
        }
        return true;
    }

    /**
     * Validate string length
     */
    public function length(string $field, string $value, int $min, int $max): bool {
        $length = strlen($value);

        if ($length < $min) {
            $this->errors[$field] = "$field must be at least $min characters";
            return false;
        }

        if ($length > $max) {
            $this->errors[$field] = "$field must be at most $max characters";
            return false;
        }

        return true;
    }

    /**
     * Validate email
     */
    public function email(string $field, string $value): bool {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$field must be a valid email";
            return false;
        }

        if (strlen($value) > 254) {
            $this->errors[$field] = "$field is too long";
            return false;
        }

        return true;
    }

    /**
     * Validate integer
     */
    public function integer(string $field, mixed $value, ?int $min = null, ?int $max = null): bool {
        $options = [];

        if ($min !== null || $max !== null) {
            $options['options'] = [];
            if ($min !== null) $options['options']['min_range'] = $min;
            if ($max !== null) $options['options']['max_range'] = $max;
        }

        $validated = empty($options)
            ? filter_var($value, FILTER_VALIDATE_INT)
            : filter_var($value, FILTER_VALIDATE_INT, $options);

        if ($validated === false) {
            $this->errors[$field] = "$field must be a valid integer";
            if ($min !== null && $max !== null) {
                $this->errors[$field] .= " between $min and $max";
            }
            return false;
        }

        return true;
    }

    /**
     * Validate against allowed values (enum)
     */
    public function enum(string $field, mixed $value, array $allowed): bool {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = "$field must be one of: " . implode(', ', $allowed);
            return false;
        }
        return true;
    }

    /**
     * Validate regex pattern
     */
    public function pattern(string $field, string $value, string $pattern, string $message = null): bool {
        if (!preg_match($pattern, $value)) {
            $this->errors[$field] = $message ?? "$field format is invalid";
            return false;
        }
        return true;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool {
        return !$this->passes();
    }
}
```

### Using the Validator

```php
<?php
require_once 'Validator.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator();

    // Validate username
    $username = $_POST['username'] ?? '';
    $validator->required('username', $username);
    $validator->length('username', $username, 3, 20);
    $validator->pattern('username', $username, '/^[a-zA-Z0-9_-]+$/', 'Username can only contain letters, numbers, underscore and dash');

    // Validate email
    $email = $_POST['email'] ?? '';
    $validator->required('email', $email);
    $validator->email('email', $email);

    // Validate age
    $age = $_POST['age'] ?? '';
    $validator->required('age', $age);
    $validator->integer('age', $age, 13, 120);

    // Validate country
    $country = $_POST['country'] ?? '';
    $validator->required('country', $country);
    $validator->enum('country', $country, ['US', 'CA', 'UK', 'FR', 'DE', 'JP', 'VN']);

    // Check if validation passed
    if ($validator->passes()) {
        // All data is valid - safe to use
        echo "Registration successful!";

        // Now you can safely use the data
        // (Still need to sanitize for output - next lesson!)

    } else {
        // Show errors
        $errors = $validator->getErrors();
        foreach ($errors as $field => $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username">
    <input type="email" name="email" placeholder="Email">
    <input type="number" name="age" placeholder="Age">
    <select name="country">
        <option value="">Select Country</option>
        <option value="US">United States</option>
        <option value="VN">Vietnam</option>
    </select>
    <button type="submit">Register</button>
</form>
```

---

## Common Validation Mistakes

### Mistake 1: Validating Only on Client-Side

```javascript
// JavaScript validation - NEVER trust this alone!
function validateForm() {
    if (document.getElementById('email').value === '') {
        alert('Email required');
        return false;
    }
    return true;
}
```

**Problem**: Attackers can bypass JavaScript by:
- Disabling JavaScript
- Using browser dev tools
- Sending POST requests directly (curl, Postman)

**Solution**: Always validate on the server (PHP)!

```php
// ALWAYS validate on server side
if (empty($_POST['email'])) {
    die('Email required');
}
```

### Mistake 2: Not Validating All Inputs

```php
// BAD - Only validating some inputs
$username = $_POST['username']; // Validated
$email = validateEmail($_POST['email']); // Validated
$bio = $_POST['bio']; // NOT VALIDATED - vulnerability!
```

**Solution**: Validate EVERY input, even optional ones:

```php
$bio = $_POST['bio'] ?? '';
if (!empty($bio)) {
    // Validate bio length, content, etc.
    if (strlen($bio) > 1000) {
        die('Bio too long');
    }
}
```

### Mistake 3: Trusting "Hidden" Inputs

```php
// HTML form
<input type="hidden" name="user_id" value="123">
<input type="hidden" name="is_admin" value="0">
```

```php
// BAD - Trusting hidden inputs
$userId = $_POST['user_id']; // Attacker can change this!
$isAdmin = $_POST['is_admin']; // Attacker can set to 1!
```

**Problem**: Hidden inputs can be modified by anyone with dev tools!

**Solution**: Never trust hidden inputs for sensitive data. Use sessions:

```php
// GOOD - Use session for user ID
$userId = $_SESSION['user_id']; // Can't be tampered with

// Don't send is_admin from form - check it server-side
$isAdmin = $db->query("SELECT is_admin FROM users WHERE id = ?", [$userId]);
```

---

## Quick Reference: Validation Checklist

For every input, ask yourself:

- [ ] **Required?** Is this field mandatory?
- [ ] **Type?** String, integer, float, boolean?
- [ ] **Length?** Min and max length?
- [ ] **Format?** Does it match expected pattern (email, date, phone, etc.)?
- [ ] **Range?** For numbers, what's the valid range?
- [ ] **Allowed values?** For selects/radios, is it in the whitelist?
- [ ] **Exists?** For IDs, does the record exist in database?
- [ ] **Authorized?** Can this user access this data?

---

## Practice Exercise

Create a file `validation-test.php` and validate this registration form:

```php
<?php
// TODO: Create Validator class (from above)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: Validate these fields:
    // - username: required, 3-20 chars, alphanumeric + underscore/dash only
    // - email: required, valid email format
    // - password: required, 8-128 chars
    // - age: required, integer, 13-120
    // - country: required, must be in ['US', 'CA', 'UK', 'FR', 'DE', 'JP', 'VN']
    // - website: optional, but if provided must be valid URL
    // - bio: optional, max 500 chars

    // If valid: show success message
    // If invalid: show all errors
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" value="<?= $_POST['username'] ?? '' ?>"><br>
    <input type="email" name="email" placeholder="Email" value="<?= $_POST['email'] ?? '' ?>"><br>
    <input type="password" name="password" placeholder="Password"><br>
    <input type="number" name="age" placeholder="Age" value="<?= $_POST['age'] ?? '' ?>"><br>
    <select name="country">
        <option value="">Select Country</option>
        <option value="US">United States</option>
        <option value="CA">Canada</option>
        <option value="VN">Vietnam</option>
    </select><br>
    <input type="url" name="website" placeholder="Website (optional)" value="<?= $_POST['website'] ?? '' ?>"><br>
    <textarea name="bio" placeholder="Bio (optional)"><?= $_POST['bio'] ?? '' ?></textarea><br>
    <button type="submit">Register</button>
</form>
```

**Try to break your validation**:
- Send negative numbers
- Send extremely long strings
- Leave required fields empty
- Send SQL injection attempts
- Send XSS payloads like `<script>alert('XSS')</script>`

Does your validation catch all of these?

---

## Key Takeaways

1. **Never trust user input** - Validate everything from users
2. **Whitelist over blacklist** - Define what's allowed, reject everything else
3. **Validate on the server** - Client-side validation is for UX only
4. **Use multiple validation layers** - Type, length, format, range, allowed values
5. **Validate all inputs** - Forms, URLs, cookies, files, headers
6. **PHP's filter functions** - Use `filter_var()` for common validations
7. **Create reusable validators** - Build a validation class for consistency
8. **Hidden inputs aren't hidden** - Never trust them for sensitive data

---

## What's Next?

In the next lesson, we'll learn about **sanitization** - how to clean user input to prevent attacks.

**Validation asks**: "Is this data acceptable?"
**Sanitization asks**: "How do I make this data safe?"

Both are essential for security!
