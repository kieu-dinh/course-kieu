# Exercise 9.3 - API Authentication

## Objective

Add authentication to your API using API keys.

## Duration

2-3 hours

## Task

Secure your API endpoints with API key authentication.

## Requirements

- [ ] Generate unique API keys for users
- [ ] Store API keys in database
- [ ] Validate API key on each request
- [ ] Return 401 Unauthorized for invalid/missing keys
- [ ] Create endpoint to generate new API key
- [ ] Create endpoint to revoke API key
- [ ] Rate limit API requests per key (optional)
- [ ] Log API usage

## Database Schema

```sql
CREATE TABLE api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    api_key VARCHAR(64) UNIQUE NOT NULL,
    name VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT,
    endpoint VARCHAR(255),
    method VARCHAR(10),
    status_code INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id)
);
```

## Starter Files

Work in `api.php`, `auth.php`, `generate-key.php` - see starter code there.

## API Key Usage

**Request with API Key (Header):**
```
GET /api/products.php
X-API-Key: abc123def456...
```

**Request with API Key (Query):**
```
GET /api/products.php?api_key=abc123def456...
```

**Error Response:**
```json
{
  "success": false,
  "error": "Invalid or missing API key"
}
```

## Checklist

- [ ] API keys generated securely
- [ ] Keys validated on each request
- [ ] Unauthorized requests blocked
- [ ] Keys can be revoked
- [ ] Usage logging implemented
- [ ] Keys stored securely (hashed optional)
- [ ] Clear error messages

## Tips

- Generate key: `bin2hex(random_bytes(32))`
- Accept key in header: `$_SERVER['HTTP_X_API_KEY']`
- Or in query string: `$_GET['api_key']`
- Hash keys before storing (optional but recommended)
- Update last_used_at on each request
- Return consistent 401 responses
- Don't reveal if key exists or is invalid
