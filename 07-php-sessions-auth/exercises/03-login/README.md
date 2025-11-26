# Exercise 7.3 - User Login

## Objective

Create a login system that authenticates users and creates sessions.

## Duration

2-3 hours

## Task

Build a login form that verifies credentials using `password_verify()` and creates a session for authenticated users.

## Requirements

- [ ] Create login form with email and password
- [ ] Query database for user by email
- [ ] Verify password using `password_verify()`
- [ ] Create session on successful login
- [ ] Store user ID and name in session
- [ ] Redirect to dashboard/welcome page
- [ ] Display error message for invalid credentials
- [ ] Implement "Account not found" vs "Wrong password" messages

## Starter Files

Work in `login.php`, `dashboard.php`, and `db.php` - see starter code there.

## Expected Output

**Success:**
```
Welcome back, John!
You are logged in.
```

**Error:**
```
Invalid email or password.
```

## Checklist

- [ ] Form submits to itself
- [ ] User is fetched from database by email
- [ ] Password is verified with `password_verify()`
- [ ] Session is created on success
- [ ] User is redirected to dashboard
- [ ] Errors are displayed clearly
- [ ] Code is secure against SQL injection

## Tips

- Use `password_verify($password, $hashedPassword)` to check passwords
- Never reveal whether email or password was wrong (security)
- Store minimal user info in session (id, name, email)
- Use `header('Location: dashboard.php')` to redirect
- Always use `exit()` after `header()` redirect
