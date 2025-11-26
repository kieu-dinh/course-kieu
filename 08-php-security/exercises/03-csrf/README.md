# Exercise 8.3 - CSRF Protection

## Objective

Learn to prevent Cross-Site Request Forgery (CSRF) attacks using tokens.

## Duration

2-3 hours

## Task

Create a form that demonstrates CSRF vulnerability and implements token-based protection.

## Requirements

- [ ] Create TWO versions:
  - **Vulnerable version** (csrf-vulnerable.php) - no CSRF protection
  - **Secure version** (csrf-secure.php) - with CSRF tokens
- [ ] Create a form that changes user settings (email, name)
- [ ] In vulnerable version: accept any POST request
- [ ] In secure version: generate and validate CSRF tokens
- [ ] Create an attack simulation page (attacker.php)
- [ ] Demonstrate how CSRF works against vulnerable version
- [ ] Show how tokens prevent CSRF in secure version

## Starter Files

Work in `csrf-vulnerable.php`, `csrf-secure.php`, `attacker.php` - see starter code there.

## How CSRF Works

1. User is logged into your site (has session)
2. User visits attacker's site (in another tab)
3. Attacker's site submits form to your site
4. Your site processes request (thinks it's from user)
5. User's account is compromised without their knowledge

## CSRF Token Flow

1. Server generates random token
2. Store token in session
3. Include token in form as hidden field
4. On submit, verify token matches session
5. Reject request if tokens don't match

## Expected Behavior

**Vulnerable Version:**
- Attack from attacker.php succeeds
- Settings changed without user action

**Secure Version:**
- Attack from attacker.php fails
- Token validation prevents unauthorized requests

## Checklist

- [ ] Both versions created
- [ ] Vulnerable version accepts any POST
- [ ] Secure version validates CSRF tokens
- [ ] Tokens are random and unpredictable
- [ ] Tokens stored in session
- [ ] Attack simulation demonstrates vulnerability
- [ ] Clear documentation of protection mechanism

## Tips

- Generate token: `bin2hex(random_bytes(32))`
- Store in session: `$_SESSION['csrf_token']`
- Include in form: `<input type="hidden" name="csrf_token" value="...">`
- Validate: `$_POST['csrf_token'] === $_SESSION['csrf_token']`
- Regenerate token after each use (optional but recommended)
- Use same-site cookie attribute (additional protection)
