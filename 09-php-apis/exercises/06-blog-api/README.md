# Exercise 9.6 - Complete Blog API

## Objective

Build a complete RESTful API for a blog with authentication, CRUD operations, and relationships.

## Duration

6-8 hours

## Task

Create a comprehensive blog API that combines everything you've learned about APIs.

## Requirements

### Core Features
- [ ] User authentication (JWT or API keys)
- [ ] Full CRUD for posts
- [ ] Full CRUD for comments
- [ ] Categories and tags
- [ ] User profiles
- [ ] Post likes/reactions
- [ ] Search and filtering

### API Endpoints

**Authentication:**
- POST `/auth/register.php` - Register user
- POST `/auth/login.php` - Login and get token
- POST `/auth/refresh.php` - Refresh token
- POST `/auth/logout.php` - Logout (invalidate token)

**Posts:**
- GET `/posts` - Get all posts (with pagination)
- GET `/posts/:id` - Get single post
- POST `/posts` - Create post (auth required)
- PUT `/posts/:id` - Update post (auth + owner)
- DELETE `/posts/:id` - Delete post (auth + owner)
- GET `/posts/:id/comments` - Get post comments
- POST `/posts/:id/like` - Like/unlike post

**Comments:**
- GET `/comments` - Get all comments
- POST `/posts/:id/comments` - Add comment
- PUT `/comments/:id` - Update comment
- DELETE `/comments/:id` - Delete comment

**Categories:**
- GET `/categories` - Get all categories
- POST `/categories` - Create category (admin only)
- GET `/categories/:id/posts` - Get posts by category

**Users:**
- GET `/users/:id` - Get user profile
- PUT `/users/:id` - Update profile (auth + owner)
- GET `/users/:id/posts` - Get user's posts

**Search:**
- GET `/search?q=keyword` - Search posts
- GET `/posts?category=1&tag=php` - Filter posts

## Database Schema

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT NOT NULL,
    excerpt VARCHAR(500),
    category_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE post_tags (
    post_id INT,
    tag_id INT,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Features to Implement

### Pagination
```
GET /posts?page=1&limit=10
Response: {
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total_pages": 5,
    "total_items": 47,
    "per_page": 10
  }
}
```

### Filtering
```
GET /posts?category=tech&status=published&sort=created_at&order=desc
```

### Search
```
GET /search?q=laravel&type=posts
```

### Relationships
```json
{
  "id": 1,
  "title": "My Post",
  "author": {
    "id": 1,
    "name": "John Doe"
  },
  "category": {
    "id": 2,
    "name": "Technology"
  },
  "tags": ["php", "api"],
  "comments_count": 5,
  "likes_count": 12
}
```

## Checklist

- [ ] All endpoints implemented
- [ ] Authentication working
- [ ] CRUD operations functional
- [ ] Pagination implemented
- [ ] Search and filtering working
- [ ] Relationships properly loaded
- [ ] Input validation on all endpoints
- [ ] Error handling comprehensive
- [ ] Authorization checks (user can only edit own posts)
- [ ] SQL injection prevented
- [ ] Rate limiting implemented (optional)
- [ ] API documentation created

## Tips

- Organize code into folders: `/auth`, `/posts`, `/comments`, etc.
- Create reusable functions for common tasks
- Use a base controller/router
- Implement proper error handling
- Use HTTP status codes correctly
- Write clear API documentation
- Test each endpoint thoroughly
- Consider using Postman for testing
- Implement soft deletes (optional)
- Add API versioning (v1, v2) (optional)

## Bonus Features

- [ ] Email notifications for comments
- [ ] Post drafts and scheduling
- [ ] Image upload for posts
- [ ] User avatars
- [ ] Markdown support for content
- [ ] API documentation (Swagger/OpenAPI)
- [ ] Rate limiting per user
- [ ] Webhook support
- [ ] OAuth integration
