# Exercise 7.2 - User Registration

## Objective

Build a secure user registration system with validation and password hashing.

## Duration

2-3 hours

## Task

Create a registration form that validates input, hashes passwords, and stores users in a database.

## Requirements

- [ ] Create registration form with: name, email, password, confirm password
- [ ] Validate email format
- [ ] Check if email already exists in database
- [ ] Validate password strength (min 8 chars, uppercase, number, special char)
- [ ] Check that passwords match
- [ ] Hash password using `password_hash()` with PASSWORD_BCRYPT
- [ ] Store user in `users` table
- [ ] Display success/error messages
- [ ] Redirect to login page after successful registration

## Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Starter Files

Work in `register.php` and `db.php` - see starter code there.

## Expected Output

**Success:**
```
Registration successful! Redirecting to login...
```

**Errors:**
```
- Email is already registered
- Password must be at least 8 characters
- Passwords do not match
```

## Checklist

- [ ] Form validates all inputs
- [ ] Email uniqueness is checked
- [ ] Password is hashed (never stored plain text)
- [ ] User is stored in database
- [ ] Error messages are displayed clearly
- [ ] Success redirects to login page
- [ ] Code prevents SQL injection

## Tips

- Use `password_hash($password, PASSWORD_BCRYPT)` to hash passwords
- Use prepared statements to prevent SQL injection
- Validate on both client-side (HTML5) and server-side (PHP)
- Use `filter_var($email, FILTER_VALIDATE_EMAIL)` for email validation
- Store validation errors in an array and display them
