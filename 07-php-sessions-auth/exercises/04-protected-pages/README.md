# Exercise 7.4 - Protected Pages

## Objective

Learn how to protect pages and redirect unauthorized users.

## Duration

1-2 hours

## Task

Create an authentication middleware system that protects pages from unauthorized access.

## Requirements

- [ ] Create an `auth.php` include file that checks if user is logged in
- [ ] If not logged in, redirect to login page with return URL
- [ ] Create multiple protected pages (profile.php, settings.php)
- [ ] Create logout functionality
- [ ] After login, redirect back to originally requested page
- [ ] Display different navigation for logged in vs logged out users

## Starter Files

Work in `auth.php`, `profile.php`, `settings.php`, `logout.php` - see starter code there.

## Expected Output

**When not logged in:**
```
Redirects to: login.php?return=/profile.php
```

**After login:**
```
Redirects back to: profile.php
```

## Checklist

- [ ] auth.php file checks session
- [ ] Unauthorized users are redirected
- [ ] Return URL is preserved
- [ ] Logout destroys session
- [ ] Navigation shows appropriate links
- [ ] Code is clean and reusable

## Tips

- Use `include 'auth.php';` at top of protected pages
- Store return URL in query string: `?return=/page.php`
- Use `$_SERVER['PHP_SELF']` to get current page
- `session_destroy()` + `session_regenerate_id()` for logout
- Create a function `isLoggedIn()` for reusability
