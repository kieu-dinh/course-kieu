# Exercise 7.6 - Password Reset

## Objective

Implement a secure password reset system with email tokens.

## Duration

3-4 hours

## Task

Create a password reset flow: request reset → receive email with token → reset password.

## Requirements

- [ ] Create "Forgot Password" form (email input)
- [ ] Generate unique reset token
- [ ] Store token in database with expiration (1 hour)
- [ ] Send reset link via email (or display it for testing)
- [ ] Create reset password form (validates token)
- [ ] Update password in database
- [ ] Invalidate token after use
- [ ] Handle expired tokens
- [ ] Show appropriate success/error messages

## Database Schema

```sql
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Starter Files

Work in `forgot-password.php`, `reset-password.php`, `send-email.php` - see starter code there.

## Expected Output

**Step 1 - Request Reset:**
```
Password reset link sent to your email!
(For testing, display the link)
```

**Step 2 - Reset Password:**
```
Password has been reset successfully!
You can now login with your new password.
```

**Errors:**
```
- Email not found
- Token expired (please request a new one)
- Token invalid
- Passwords do not match
```

## Checklist

- [ ] Forgot password form works
- [ ] Token is generated and stored
- [ ] Email is sent (or link displayed for testing)
- [ ] Reset form validates token
- [ ] Token expires after 1 hour
- [ ] Password is updated successfully
- [ ] Token is deleted after use
- [ ] Old tokens are cleaned up

## Tips

- Use `bin2hex(random_bytes(32))` for secure token
- Set token expiration: `DATE_ADD(NOW(), INTERVAL 1 HOUR)`
- For testing, display the reset link instead of emailing
- In production, use `mail()` or a service like SendGrid
- Delete all reset tokens for a user when they reset password
- Clean up expired tokens with a cron job or on each request
