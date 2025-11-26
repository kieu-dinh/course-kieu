# Exercise 9.1 - Simple JSON API

## Objective

Create your first JSON API that responds with data in JSON format.

## Duration

1-2 hours

## Task

Build a simple API that returns user data as JSON.

## Requirements

- [ ] Create an API endpoint: `api.php`
- [ ] Return JSON response with proper headers
- [ ] Create endpoints for:
  - GET `/api.php?action=users` - Return list of users
  - GET `/api.php?action=user&id=1` - Return single user
  - GET `/api.php?action=stats` - Return statistics
- [ ] Use proper HTTP status codes (200, 404, 400)
- [ ] Include appropriate error messages
- [ ] Set Content-Type header to `application/json`

## Starter Files

Work in `api.php` - see starter code there.

## Expected Output

**GET /api.php?action=users**
```json
{
  "success": true,
  "data": [
    {"id": 1, "name": "John Doe", "email": "john@example.com"},
    {"id": 2, "name": "Jane Smith", "email": "jane@example.com"}
  ]
}
```

**GET /api.php?action=user&id=1**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user"
  }
}
```

**Error Response**
```json
{
  "success": false,
  "error": "User not found"
}
```

## Checklist

- [ ] JSON responses formatted correctly
- [ ] Content-Type header set
- [ ] HTTP status codes used appropriately
- [ ] Error handling implemented
- [ ] Different actions supported
- [ ] Code is clean and organized

## Tips

- Use `header('Content-Type: application/json')` for JSON
- Use `json_encode($data)` to convert PHP arrays to JSON
- Set status code: `http_response_code(404)`
- Use `exit()` after sending response
- Always validate input parameters
- Return consistent response format
