# Exercise 7.5 - Remember Me

## Objective

Implement a "Remember Me" feature using cookies to keep users logged in across browser sessions.

## Duration

2-3 hours

## Task

Add "Remember Me" functionality that stores a secure token in a cookie and database.

## Requirements

- [ ] Add "Remember Me" checkbox to login form
- [ ] Generate a random secure token when "Remember Me" is checked
- [ ] Store token in database with user ID and expiration date
- [ ] Set a cookie with the token (30 days expiration)
- [ ] On page load, check for remember token cookie
- [ ] If valid token exists, auto-login the user
- [ ] Invalidate token on logout
- [ ] Use secure, HTTP-only cookies

## Database Schema

```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Starter Files

Work in `login.php`, `auth-check.php`, `logout.php` - see starter code there.

## Expected Output

**With Remember Me:**
```
User stays logged in even after closing browser
Cookie expires in 30 days
```

**Without Remember Me:**
```
User logs out when browser closes
```

## Checklist

- [ ] Checkbox added to login form
- [ ] Random token generated (use bin2hex(random_bytes(32)))
- [ ] Token stored in database
- [ ] Cookie set with secure flags
- [ ] Auto-login works from cookie
- [ ] Tokens are invalidated on logout
- [ ] Old/expired tokens are cleaned up

## Tips

- Use `bin2hex(random_bytes(32))` for secure tokens
- Set cookie with `httponly` and `secure` flags
- Hash tokens before storing in database (optional but recommended)
- Clean up expired tokens periodically
- Token should expire after 30 days
- Always regenerate session ID after auto-login
