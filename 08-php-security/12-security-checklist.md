# Lesson 12 - Complete Security Checklist & Audit

**Duration**: 60 minutes

---

## Introduction: Security as a Process

Security isn't a one-time implementation - it's an ongoing process. This lesson provides a comprehensive checklist to audit your applications and ensure you've covered all security bases.

**Use this checklist**:
- When starting a new project (security by design)
- Before deploying to production
- During regular security audits
- After discovering vulnerabilities
- When inheriting existing code

---

## The Complete Security Checklist

### 1. Input Validation & Sanitization

#### Validation
- [ ] All user input is validated server-side (never trust client-side only)
- [ ] Input validation uses whitelist approach (allow known good, deny everything else)
- [ ] Data types are validated (strings, integers, booleans, etc.)
- [ ] String lengths are validated (min/max)
- [ ] Numeric ranges are validated
- [ ] Email addresses validated with `filter_var($email, FILTER_VALIDATE_EMAIL)`
- [ ] URLs validated with `filter_var($url, FILTER_VALIDATE_URL)` and protocol check
- [ ] Dates validated with `DateTime::createFromFormat()`
- [ ] File uploads validated (extension, MIME type, file signature)
- [ ] Hidden form fields are not trusted (re-validate server-side)

#### Sanitization
- [ ] All output uses `htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`
- [ ] URL parameters use `urlencode()`
- [ ] JavaScript data uses `json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP)`
- [ ] User input is not sanitized before storage (store raw, sanitize on output)
- [ ] Passwords are never sanitized (hashed as-is)
- [ ] Filenames are sanitized before storage
- [ ] Rich text content uses HTML Purifier or Markdown library

### 2. XSS (Cross-Site Scripting) Prevention

- [ ] `htmlspecialchars()` used everywhere user data is displayed
- [ ] Using `ENT_QUOTES` parameter (encodes both " and ')
- [ ] Using `'UTF-8'` parameter (handles international characters)
- [ ] No inline event handlers with user input (`onclick`, `onerror`, etc.)
- [ ] JavaScript data passed via `json_encode()` with security flags
- [ ] Content-Security-Policy header is set
- [ ] CSP uses nonce or hash for inline scripts
- [ ] No `eval()`, `innerHTML`, or `document.write()` with user input
- [ ] Rich text content is processed with trusted library (HTML Purifier, Parsedown)
- [ ] User-uploaded files served with correct Content-Type and `X-Content-Type-Options: nosniff`

### 3. CSRF (Cross-Site Request Forgery) Prevention

- [ ] CSRF tokens generated for all sessions
- [ ] CSRF tokens use cryptographically secure random (`random_bytes()`)
- [ ] CSRF tokens included in all forms as hidden fields
- [ ] CSRF tokens validated on all POST/PUT/DELETE/PATCH requests
- [ ] Token validation uses `hash_equals()` (prevents timing attacks)
- [ ] Tokens regenerated after login and critical operations
- [ ] SameSite cookie attribute set (`Lax` or `Strict`)
- [ ] GET requests never perform state-changing operations
- [ ] Referer header checked as additional validation (optional)
- [ ] Critical operations require re-authentication (password confirmation)

### 4. SQL Injection Prevention

- [ ] All database queries use prepared statements (NO exceptions)
- [ ] Never building SQL with string concatenation
- [ ] PDO configured with `PDO::ATTR_EMULATE_PREPARES => false`
- [ ] Table names are whitelisted (can't be parameterized)
- [ ] Column names are whitelisted (can't be parameterized)
- [ ] LIKE wildcards are escaped when using user input
- [ ] IN clause values are validated (type and count)
- [ ] No second-order SQL injection (don't trust database data)
- [ ] ORM used correctly (Laravel Eloquent, etc.)
- [ ] Database user has minimum required permissions

### 5. Authentication Security

- [ ] Passwords hashed with `password_hash($password, PASSWORD_DEFAULT)`
- [ ] Never using MD5, SHA1, or plain text for passwords
- [ ] Password requirements enforced (min 8 chars, complexity)
- [ ] Rate limiting on login attempts (5-10 per 15 minutes)
- [ ] Account lockout after repeated failed attempts
- [ ] Session ID regenerated after login (`session_regenerate_id(true)`)
- [ ] Session cookies have `httponly` flag
- [ ] Session cookies have `secure` flag (HTTPS only)
- [ ] Session cookies have `samesite` attribute
- [ ] Session timeout implemented
- [ ] Remember-me tokens are random and hashed
- [ ] Multi-factor authentication available (optional but recommended)
- [ ] Password reset tokens are random, single-use, and time-limited

### 6. Authorization & Access Control

- [ ] Every protected page checks authentication (`if (!isset($_SESSION['user_id']))`)
- [ ] Every admin function checks admin role
- [ ] Resource ownership verified before allowing access
- [ ] Users can only view/edit/delete their own data
- [ ] Direct object references checked (user can't access other user's data by changing ID)
- [ ] Authorization checked server-side (not just UI hiding)
- [ ] Database queries include user_id in WHERE clause
- [ ] Role-based or permission-based access control implemented
- [ ] Principle of least privilege applied
- [ ] Access control failures logged

### 7. File Upload Security

- [ ] File extension validated (whitelist only)
- [ ] MIME type validated with `finfo_file()`
- [ ] File signature (magic bytes) validated
- [ ] File size limited (max upload size)
- [ ] Empty files rejected
- [ ] Original filename never used directly
- [ ] Unique filenames generated (random, not sequential)
- [ ] Files stored outside web root
- [ ] Upload directory has script execution disabled (`.htaccess` or Nginx config)
- [ ] File permissions set correctly (644 for files, 755 for directories)
- [ ] Images re-encoded to strip metadata
- [ ] Files served through PHP with access control (not direct URLs)
- [ ] Content-Disposition header set when serving files
- [ ] Virus scanning for document uploads (optional, ClamAV)

### 8. Session Security

- [ ] Session cookies have all security flags (`httponly`, `secure`, `samesite`)
- [ ] Session ID regenerated after login
- [ ] Session timeout implemented (idle and absolute)
- [ ] Session data validated (check if session belongs to logged-in user)
- [ ] Sessions destroyed completely on logout
- [ ] Session fixation prevented (regenerate ID)
- [ ] Session hijacking prevented (bind to IP/user agent - optional)
- [ ] Session data stored securely (database or Redis, not files in production)

### 9. Security Headers

- [ ] Content-Security-Policy header set
- [ ] X-Frame-Options: DENY or SAMEORIGIN
- [ ] X-Content-Type-Options: nosniff
- [ ] Strict-Transport-Security (HSTS) set (if using HTTPS)
- [ ] X-XSS-Protection: 1; mode=block
- [ ] Referrer-Policy: strict-origin-when-cross-origin
- [ ] Permissions-Policy set (restrict camera, microphone, etc.)
- [ ] X-Powered-By header removed
- [ ] Cache-Control set to no-cache for sensitive pages
- [ ] Headers tested with securityheaders.com (grade A)

### 10. HTTPS/TLS

- [ ] HTTPS enabled on entire site
- [ ] HTTP automatically redirects to HTTPS
- [ ] Valid SSL/TLS certificate installed
- [ ] Certificate not expired
- [ ] Certificate includes all subdomains
- [ ] TLS 1.2 or higher required (disable TLS 1.0/1.1)
- [ ] Strong cipher suites enabled
- [ ] HSTS header set (Strict-Transport-Security)
- [ ] Mixed content warnings resolved
- [ ] SSL Labs grade A or higher (ssllabs.com/ssltest)

### 11. Error Handling & Logging

- [ ] `display_errors` set to Off in production
- [ ] `error_reporting` set to E_ALL in development
- [ ] `log_errors` set to On
- [ ] Custom error pages for 404, 403, 500
- [ ] Error messages don't leak sensitive information
- [ ] Stack traces hidden from users in production
- [ ] Security events logged (failed logins, access denied, etc.)
- [ ] Logs stored securely (not in web root)
- [ ] Logs rotated and archived
- [ ] Log analysis performed regularly

### 12. Configuration Security

- [ ] Debug mode disabled in production
- [ ] Database credentials stored outside web root
- [ ] Environment variables used for secrets (`.env` file)
- [ ] `.env` file excluded from version control (`.gitignore`)
- [ ] File permissions correct (644 for files, 755 for directories)
- [ ] Directory listing disabled
- [ ] Unnecessary files removed (README, changelog, .git, etc.)
- [ ] PHP version up to date (latest stable)
- [ ] All dependencies up to date (composer update)
- [ ] Composer `--no-dev` flag used in production
- [ ] Unnecessary PHP modules disabled
- [ ] `allow_url_fopen` and `allow_url_include` disabled

### 13. Database Security

- [ ] Database user has minimum required permissions
- [ ] Separate users for different operations (read-only for reports)
- [ ] Database not accessible from public internet
- [ ] Database credentials not hardcoded (use environment variables)
- [ ] Database backups encrypted
- [ ] Sensitive data encrypted at rest (optional)
- [ ] Database connection uses SSL/TLS
- [ ] Local file access disabled (`local_infile=0`)
- [ ] Symbolic links disabled in MySQL

### 14. API Security

- [ ] API requires authentication (API keys, OAuth, JWT)
- [ ] Rate limiting implemented
- [ ] Input validation on all endpoints
- [ ] Output encoding on all responses
- [ ] CORS configured correctly (not `*` for sensitive APIs)
- [ ] HTTPS required for all API calls
- [ ] API keys transmitted in headers (not URL)
- [ ] API versioning implemented
- [ ] API documentation doesn't expose sensitive information
- [ ] API errors don't leak implementation details

### 15. Third-Party Dependencies

- [ ] All dependencies from trusted sources (Packagist, npm)
- [ ] Dependencies kept up to date
- [ ] Security advisories monitored (GitHub, Snyk)
- [ ] Composer lock file committed
- [ ] `composer audit` run regularly
- [ ] npm audit run regularly (if using JavaScript)
- [ ] Unused dependencies removed
- [ ] Dependencies reviewed before adding

### 16. Rate Limiting

- [ ] Login attempts rate limited (5-10 per 15 minutes)
- [ ] Password reset rate limited (3 per hour)
- [ ] Registration rate limited (3-5 per day per IP)
- [ ] API endpoints rate limited (based on plan)
- [ ] Contact form rate limited (5 per day)
- [ ] Comment submission rate limited
- [ ] Rate limit counters cleared on success
- [ ] Rate limit errors provide retry time

### 17. Deployment Security

- [ ] Production environment separate from development
- [ ] Access to production restricted (VPN, IP whitelist)
- [ ] Deployment automated (no manual FTP uploads)
- [ ] Deployment keys separate from development keys
- [ ] Server firewall configured
- [ ] SSH key-based authentication only
- [ ] Root login disabled
- [ ] Unused ports closed
- [ ] Server software up to date (OS, web server, PHP)
- [ ] Regular security updates applied

### 18. Monitoring & Incident Response

- [ ] Server monitoring enabled (uptime, performance)
- [ ] Log monitoring enabled (failed logins, errors)
- [ ] Alerts configured for security events
- [ ] Backup system in place
- [ ] Backup restoration tested
- [ ] Incident response plan documented
- [ ] Security contact information published
- [ ] Regular security audits scheduled

---

## Testing Your Security

### Manual Testing

#### 1. XSS Testing

Test every input field with:
```html
<script>alert('XSS')</script>
<img src=x onerror="alert('XSS')">
javascript:alert('XSS')
<svg onload="alert('XSS')">
```

**Expected**: No alert boxes, code displayed as text.

#### 2. SQL Injection Testing

Test with:
```sql
' OR '1'='1
' OR '1'='1' --
'; DROP TABLE users; --
admin'--
```

**Expected**: Query fails or returns no unexpected results.

#### 3. CSRF Testing

1. Create test HTML page with form submitting to your app
2. Open your app and log in
3. Visit test page
4. **Expected**: Request rejected (CSRF token invalid)

#### 4. Authorization Testing

1. Log in as User A
2. Note URL/ID for User A's resources
3. Log in as User B
4. Try to access User A's resources
5. **Expected**: Access denied

#### 5. File Upload Testing

Upload:
- `shell.php` (PHP file)
- `image.php.jpg` (double extension)
- `../../../etc/passwd` (path traversal)
- Large file (>max size)
- Empty file

**Expected**: All rejected or handled safely.

### Automated Testing Tools

#### Security Scanners

```bash
# OWASP ZAP
docker run -t owasp/zap2docker-stable zap-baseline.py -t https://your-site.com

# Nikto
nikto -h https://your-site.com

# SQLMap (for SQL injection)
sqlmap -u "https://your-site.com/page.php?id=1" --batch
```

#### Dependency Scanning

```bash
# PHP dependencies
composer audit

# npm dependencies
npm audit

# Check for known vulnerabilities
snyk test
```

#### Code Analysis

```bash
# PHPStan
phpstan analyse src/

# Psalm
psalm

# PHP_CodeSniffer (security rules)
phpcs --standard=Security src/
```

### Online Testing

- **SSL Labs**: https://www.ssllabs.com/ssltest/
- **Security Headers**: https://securityheaders.com/
- **Mozilla Observatory**: https://observatory.mozilla.org/

---

## Security by Development Phase

### Planning Phase
- [ ] Threat modeling completed
- [ ] Security requirements defined
- [ ] Data classification done (public, internal, confidential)
- [ ] Compliance requirements identified (GDPR, etc.)

### Development Phase
- [ ] Secure coding guidelines followed
- [ ] Input validation implemented
- [ ] Output encoding implemented
- [ ] Authentication/authorization implemented
- [ ] Security testing done during development
- [ ] Code reviews include security checks
- [ ] Version control used (Git)
- [ ] Secrets not committed to repository

### Testing Phase
- [ ] All items in checklist tested
- [ ] Penetration testing performed
- [ ] Automated security scans run
- [ ] Manual testing completed
- [ ] Third-party security audit (optional)
- [ ] Vulnerabilities documented and fixed

### Deployment Phase
- [ ] Production environment hardened
- [ ] Security headers configured
- [ ] HTTPS enabled
- [ ] Monitoring enabled
- [ ] Backups configured
- [ ] Incident response plan ready
- [ ] Security contact published

### Maintenance Phase
- [ ] Regular updates applied
- [ ] Security advisories monitored
- [ ] Logs reviewed regularly
- [ ] Security audits performed quarterly
- [ ] Backup restoration tested
- [ ] Incident response plan updated

---

## Common Vulnerabilities Quick Reference

### OWASP Top 10 (2021)

1. **Broken Access Control**
   - Missing authorization checks
   - Insecure direct object references
   - Fix: Check permissions on every request

2. **Cryptographic Failures**
   - Weak encryption
   - Plaintext passwords
   - Fix: Use `password_hash()`, HTTPS, strong encryption

3. **Injection**
   - SQL injection
   - Command injection
   - Fix: Use prepared statements, input validation

4. **Insecure Design**
   - Lack of security controls
   - Missing threat modeling
   - Fix: Security by design, threat modeling

5. **Security Misconfiguration**
   - Default credentials
   - Unnecessary features enabled
   - Fix: Hardening, remove defaults, disable unused features

6. **Vulnerable Components**
   - Outdated libraries
   - Known vulnerabilities
   - Fix: Update regularly, monitor advisories

7. **Identification & Authentication Failures**
   - Weak passwords
   - Missing MFA
   - Fix: Strong password policy, rate limiting, MFA

8. **Software & Data Integrity Failures**
   - Unsigned updates
   - Insecure CI/CD
   - Fix: Code signing, secure pipeline, SRI

9. **Logging & Monitoring Failures**
   - No logging
   - No alerts
   - Fix: Log security events, monitor, alert

10. **Server-Side Request Forgery (SSRF)**
    - Unvalidated URL fetch
    - Internal service access
    - Fix: Validate URLs, whitelist, isolate services

---

## Priority Levels

### Critical (Fix Immediately)
- SQL injection vulnerabilities
- Authentication bypass
- Remote code execution
- Password storage issues (plain text, MD5, SHA1)
- Missing CSRF protection
- XSS in admin panels
- File upload allowing PHP execution

### High (Fix Within Days)
- XSS in user content
- Missing authorization checks
- Session security issues
- Weak password requirements
- Missing rate limiting on login
- Insecure file uploads
- Information disclosure

### Medium (Fix Within Weeks)
- Missing security headers
- XSS in error messages
- Cache control issues
- Information leakage
- Missing HTTPS
- Outdated dependencies with known vulnerabilities

### Low (Fix When Possible)
- Missing `X-XSS-Protection` header
- No Content-Security-Policy
- Verbose error messages
- Directory listing enabled
- Non-security sensitive updates

---

## Security Checklist Template

Use this for regular audits:

```markdown
# Security Audit - [Project Name]
Date: [YYYY-MM-DD]
Auditor: [Name]

## Input Validation
- [ ] All inputs validated
- [ ] Whitelist approach used
- [ ] File uploads secure

## XSS Prevention
- [ ] htmlspecialchars() everywhere
- [ ] CSP header set
- [ ] No inline event handlers

## CSRF Prevention
- [ ] CSRF tokens generated
- [ ] Tokens validated
- [ ] SameSite cookies set

## SQL Injection
- [ ] Prepared statements only
- [ ] No string concatenation
- [ ] Table/column names whitelisted

## Authentication
- [ ] Passwords hashed correctly
- [ ] Rate limiting enabled
- [ ] Sessions secure

## Authorization
- [ ] Permission checks everywhere
- [ ] Ownership verified
- [ ] Direct object refs checked

## File Uploads
- [ ] Extension validated
- [ ] MIME type validated
- [ ] Stored outside web root

## Security Headers
- [ ] CSP set
- [ ] X-Frame-Options set
- [ ] HSTS set (if HTTPS)

## Configuration
- [ ] Debug mode off
- [ ] Errors not displayed
- [ ] Dependencies updated

## Testing
- [ ] Manual testing done
- [ ] Automated scans run
- [ ] Grade A on securityheaders.com

## Issues Found
1. [Issue description] - Priority: [Critical/High/Medium/Low]
2. ...

## Recommendations
1. [Recommendation]
2. ...

## Next Audit Date
[YYYY-MM-DD]
```

---

## Key Takeaways

1. **Security is a process** - Not a one-time implementation
2. **Use this checklist** - Before deployment and regularly
3. **Test everything** - Manual and automated testing
4. **Defense in depth** - Multiple security layers
5. **Stay updated** - Security threats evolve
6. **Monitor continuously** - Logs, alerts, audits
7. **Document everything** - Incident response, procedures
8. **Regular audits** - Quarterly security reviews
9. **Update dependencies** - Monitor for vulnerabilities
10. **Security by design** - Build security in from the start

---

## What's Next?

Congratulations! You've completed Module 08 - Security & Validation. You now have:
- Deep understanding of web security vulnerabilities
- Practical skills to prevent XSS, CSRF, SQL injection
- Knowledge of authentication and authorization
- File upload security implementation
- Security headers configuration
- Rate limiting implementation
- Comprehensive security audit checklist

**Next Module**: Module 09 - APIs in Pure PHP

You'll learn to:
- Build RESTful APIs from scratch
- Implement API authentication (keys, JWT)
- Handle JSON requests/responses
- API versioning
- API documentation
- Rate limiting for APIs
- CORS configuration

**Before moving on**:
- Review all lessons in this module
- Complete all exercises
- Practice with the security checklist
- Test your own projects for vulnerabilities

Stay secure, and remember: **security is everyone's responsibility!**
