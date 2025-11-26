# Exercise 8.2 - XSS Prevention

## Objective

Learn to prevent Cross-Site Scripting (XSS) attacks by properly escaping output.

## Duration

2-3 hours

## Task

Create a comment system that demonstrates XSS vulnerabilities and their fixes.

## Requirements

- [ ] Create a simple comment system (no database, use sessions)
- [ ] Create TWO versions:
  - **Vulnerable version** (xss-vulnerable.php) - shows how XSS works
  - **Secure version** (xss-secure.php) - properly prevents XSS
- [ ] Allow users to submit comments with name and comment text
- [ ] Display all comments below the form
- [ ] In vulnerable version: display comments WITHOUT escaping
- [ ] In secure version: escape ALL output with htmlspecialchars()
- [ ] Include examples of XSS payloads to test
- [ ] Add warning messages explaining the vulnerability

## Starter Files

Work in `xss-vulnerable.php` and `xss-secure.php` - see starter code there.

## Expected Behavior

**Vulnerable Version:**
- Script tags execute
- User can inject malicious code
- Shows alert boxes, can steal cookies, etc.

**Secure Version:**
- Script tags are displayed as text
- No code execution
- Safe from XSS attacks

## XSS Payloads to Test

Try these in the comment field:
```html
<script>alert('XSS')</script>
<img src=x onerror="alert('XSS')">
<svg onload="alert('XSS')">
<iframe src="javascript:alert('XSS')">
```

## Checklist

- [ ] Both versions created
- [ ] Vulnerable version shows XSS in action
- [ ] Secure version prevents all XSS
- [ ] htmlspecialchars() used on ALL output
- [ ] Warning messages explain the risk
- [ ] Comments persist in session
- [ ] Clear documentation of the vulnerability

## Tips

- ALWAYS use `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')` for output
- Escape ALL user input before displaying
- Never use `echo $_POST['data']` directly
- Be careful with JSON output too
- Use Content Security Policy headers (bonus)
- Remember: filter input, escape output
