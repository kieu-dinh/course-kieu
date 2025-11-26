# Exercise 7.7 - Complete Authentication System

## Objective

Build a complete, production-ready authentication system combining all concepts from this module.

## Duration

4-6 hours

## Task

Create a full authentication system with registration, login, protected pages, remember me, and password reset.

## Requirements

### Core Features
- [ ] User registration with validation
- [ ] User login with password verification
- [ ] Session management
- [ ] Protected pages with auth middleware
- [ ] Logout functionality
- [ ] "Remember Me" with secure tokens
- [ ] Password reset flow
- [ ] Profile page (editable)
- [ ] Admin dashboard (for admin users only)

### Security Features
- [ ] Password hashing (bcrypt)
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (htmlspecialchars)
- [ ] Session regeneration after login
- [ ] Secure cookie flags (httponly, secure)
- [ ] Token expiration
- [ ] Rate limiting for login attempts (optional)

### User Experience
- [ ] Clear error/success messages
- [ ] Form data persistence on errors
- [ ] Redirect back to intended page after login
- [ ] Email validation
- [ ] Password strength requirements

## Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## File Structure

```
/complete-system/
├── config/
│   └── db.php                 # Database connection
├── includes/
│   ├── auth.php              # Auth middleware
│   ├── functions.php         # Helper functions
│   └── header.php            # Navigation header
├── register.php              # Registration page
├── login.php                 # Login page
├── logout.php                # Logout handler
├── dashboard.php             # User dashboard
├── profile.php               # User profile (editable)
├── admin.php                 # Admin-only page
├── forgot-password.php       # Request reset
├── reset-password.php        # Reset form
└── index.php                 # Homepage
```

## Starter Files

See individual starter files in the exercise folder.

## Expected Features

### Public Pages
- Homepage with login/register links
- Registration form
- Login form (with remember me)
- Forgot password
- Reset password

### Protected Pages
- Dashboard (all authenticated users)
- Profile page (editable: name, email, password)
- Admin page (only for admin role)

### Navigation
- Show different nav based on auth status
- Guest: Home | Login | Register
- User: Dashboard | Profile | Logout
- Admin: Dashboard | Profile | Admin | Logout

## Checklist

- [ ] All pages work correctly
- [ ] Authentication flow is complete
- [ ] Security best practices implemented
- [ ] Remember me works across sessions
- [ ] Password reset works end-to-end
- [ ] Admin page only accessible to admins
- [ ] Form validation is thorough
- [ ] Error handling is robust
- [ ] Code is organized and clean
- [ ] Database schema is properly created

## Tips

- Start with the database schema
- Build incrementally: register → login → protected pages → extras
- Reuse code with includes (auth.php, functions.php)
- Test each feature thoroughly before moving on
- Use a consistent coding style
- Comment complex logic
- Handle edge cases (expired tokens, invalid data, etc.)

## Bonus Features (Optional)

- [ ] Email verification on registration
- [ ] Two-factor authentication (TOTP)
- [ ] Login history tracking
- [ ] Account lockout after failed attempts
- [ ] User avatar upload
- [ ] Activity logs
- [ ] Social login (Google, GitHub)
