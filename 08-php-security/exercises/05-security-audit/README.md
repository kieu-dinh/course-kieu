# Exercise 8.5 - Security Audit

## Objective

Conduct a comprehensive security audit of a vulnerable application and fix all security issues.

## Duration

4-6 hours

## Task

You're given a vulnerable mini-application. Your job is to find and fix ALL security vulnerabilities.

## The Application

A simple blog system with:
- User registration/login
- Create/edit/delete posts
- Comments on posts
- User profile

## Known Vulnerabilities (Find and Fix)

### SQL Injection
- [ ] Login form vulnerable to SQL injection
- [ ] Search functionality vulnerable to SQL injection
- [ ] Post/comment queries use unsafe string concatenation

### XSS (Cross-Site Scripting)
- [ ] Post titles and content not escaped
- [ ] Comments not escaped
- [ ] User profiles display unescaped data
- [ ] Search results show unescaped queries

### CSRF (Cross-Site Request Forgery)
- [ ] No CSRF tokens on forms
- [ ] Delete actions have no protection
- [ ] Profile update has no CSRF protection

### Authentication/Authorization
- [ ] Passwords stored in plain text
- [ ] No session regeneration after login
- [ ] Users can edit/delete other users' posts
- [ ] No logout functionality
- [ ] Weak password requirements

### File Upload
- [ ] Avatar upload accepts any file type
- [ ] No file size limits
- [ ] Original filenames used

### Information Disclosure
- [ ] Detailed error messages shown to users
- [ ] Database credentials in public files
- [ ] Debug mode enabled

### Other Issues
- [ ] No input validation
- [ ] Insecure direct object references (IDOR)
- [ ] Missing security headers
- [ ] Session fixation vulnerability

## Your Tasks

1. **Audit the Code**
   - Read through all files
   - Document every vulnerability found
   - Create a vulnerability report

2. **Fix the Vulnerabilities**
   - Fix SQL injection (use prepared statements)
   - Fix XSS (escape all output)
   - Add CSRF protection
   - Implement proper authentication
   - Secure file uploads
   - Add input validation
   - Fix authorization issues

3. **Test Your Fixes**
   - Try to exploit each vulnerability
   - Verify fixes work correctly
   - Ensure app still functions

4. **Document Changes**
   - List all changes made
   - Explain why each change improves security

## Starter Files

The vulnerable application is in the `app/` folder:
- `config.php` - Database configuration
- `index.php` - Homepage with posts
- `login.php` - Login form
- `register.php` - Registration
- `post.php` - View/create posts
- `profile.php` - User profile
- `search.php` - Search posts

## Expected Deliverables

1. **VULNERABILITIES.md** - List of all vulnerabilities found
2. **FIXES.md** - Description of all fixes applied
3. **Fixed application code** - Secure version of all files

## Checklist

### SQL Injection
- [ ] All queries use prepared statements
- [ ] No string concatenation in SQL
- [ ] Input properly escaped

### XSS
- [ ] All output uses htmlspecialchars()
- [ ] User content properly escaped
- [ ] No innerHTML with user data

### CSRF
- [ ] All forms have CSRF tokens
- [ ] Tokens validated on submit
- [ ] Tokens regenerated after use

### Authentication
- [ ] Passwords hashed with password_hash()
- [ ] Session regenerated after login
- [ ] Proper logout implemented
- [ ] Password strength enforced

### Authorization
- [ ] Users can only edit own posts
- [ ] Role-based access control
- [ ] Proper permission checks

### File Upload
- [ ] File type validation
- [ ] File size limits
- [ ] Random filenames
- [ ] Protected upload directory

### General
- [ ] Input validation on all forms
- [ ] Error handling without info disclosure
- [ ] Security headers added
- [ ] Sensitive data protected

## Tips

- Start by reading all the code
- Make a list of vulnerabilities before fixing
- Fix one category at a time (SQL, XSS, CSRF, etc.)
- Test after each fix
- Use a systematic approach
- Document everything
- Learn from each vulnerability

## Bonus Challenges

- [ ] Add rate limiting to login
- [ ] Implement two-factor authentication
- [ ] Add security.txt file
- [ ] Implement Content Security Policy
- [ ] Add logging and monitoring
- [ ] Implement account lockout
- [ ] Add email verification
