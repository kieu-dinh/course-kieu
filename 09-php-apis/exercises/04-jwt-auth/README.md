# Exercise 9.4 - JWT Authentication

## Objective

Implement JWT (JSON Web Tokens) for stateless API authentication.

## Duration

3-4 hours

## Task

Build a JWT-based authentication system for your API.

## Requirements

- [ ] Install JWT library (Firebase PHP-JWT or similar)
- [ ] Create login endpoint that returns JWT
- [ ] Validate JWT on protected endpoints
- [ ] Include user data in JWT payload
- [ ] Set token expiration (1 hour)
- [ ] Implement token refresh mechanism
- [ ] Handle expired tokens gracefully
- [ ] Return appropriate error messages

## Installation

```bash
composer require firebase/php-jwt
```

## Starter Files

Work in `login.php`, `api.php`, `middleware/auth.php` - see starter code there.

## JWT Flow

1. User logs in with credentials
2. Server validates and creates JWT
3. Client receives JWT
4. Client includes JWT in subsequent requests
5. Server validates JWT and processes request

## API Endpoints

**POST /login.php**
```json
Input: {"email": "user@example.com", "password": "password123"}
Response: {
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIs...",
  "expires_in": 3600
}
```

**GET /api/profile.php**
```
Headers: Authorization: Bearer eyJhbGciOiJIUzI1NiIs...
Response: {
  "success": true,
  "data": {"id": 1, "name": "John", "email": "john@example.com"}
}
```

**POST /refresh.php**
```json
Input: {"refresh_token": "..."}
Response: {
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIs..."
}
```

## JWT Structure

**Header:**
```json
{"alg": "HS256", "typ": "JWT"}
```

**Payload:**
```json
{
  "user_id": 1,
  "email": "user@example.com",
  "role": "user",
  "iat": 1640000000,
  "exp": 1640003600
}
```

## Checklist

- [ ] JWT library installed
- [ ] Login generates valid JWT
- [ ] JWT validated on protected routes
- [ ] Token expiration enforced
- [ ] Refresh token implemented
- [ ] Bearer token accepted in header
- [ ] Error handling for invalid/expired tokens
- [ ] Secret key stored securely

## Tips

- Use strong secret key (store in env variable)
- Set reasonable expiration (1-24 hours)
- Include minimal data in JWT payload
- Validate token signature
- Check token expiration
- Use Bearer token format: `Authorization: Bearer <token>`
- Parse token: `$jwt = str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION'])`
- Decode: `JWT::decode($jwt, new Key($secret, 'HS256'))`
