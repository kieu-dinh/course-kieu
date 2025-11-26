# Exercise 6.3 - Blog Database System

## Objective

Build a blog system with posts, categories, and tags using normalized database design.

## Duration

2-2.5 hours

## Task

Create a blog management system with multiple interconnected tables.

## Requirements

- [ ] Create `categories` table: id, name, description, slug
- [ ] Create `posts` table: id, title, content, category_id, author, views, published, created_at, updated_at
- [ ] Create `Post.php` class with CRUD methods
- [ ] Create `Category.php` class with CRUD methods
- [ ] Implement post creation with category
- [ ] Implement fetching posts by category
- [ ] Implement fetching popular posts (by views)
- [ ] Add increment views functionality
- [ ] Use foreign key constraints

## Starter Files

Work in `Database.php`, `Category.php`, `Post.php`, and `test-blog.php`

## SQL Table Definitions

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    category_id INT NOT NULL,
    author VARCHAR(100) NOT NULL,
    views INT DEFAULT 0,
    published BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
```

## Expected Output

```
php test-blog.php

=== Blog System ===

Creating categories...
Category created: Technology (ID: 1)
Category created: Lifestyle (ID: 2)

Creating posts...
Post created: PHP Tips and Tricks (ID: 1)
Post created: Healthy Living Guide (ID: 2)

Fetching all posts...
1. PHP Tips and Tricks by Author (4 views)
   Category: Technology

2. Healthy Living Guide by Author (2 views)
   Category: Lifestyle

Fetching posts by category (Technology)...
- PHP Tips and Tricks

Incrementing views...
Post views updated to: 5
```

## Checklist

- [ ] Categories table created with proper schema
- [ ] Posts table created with foreign key constraint
- [ ] Category class implements CRUD
- [ ] Post class implements CRUD
- [ ] Posts can be created with category
- [ ] Posts can be fetched by category
- [ ] Views counter increments properly
- [ ] Popular posts sorted correctly
- [ ] Queries use prepared statements
- [ ] Error handling for invalid categories
- [ ] Code runs without errors

## Tips

- Use foreign key constraints to maintain data integrity
- Use JOINs to fetch posts with category names
- Category slug can be used for URL-friendly references
- Views should be integer type for counting
- Consider indexing frequently queried columns
- Test both category and post operations
