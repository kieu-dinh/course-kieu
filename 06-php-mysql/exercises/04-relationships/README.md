# Exercise 6.4 - Database Relationships and JOINs

## Objective

Master database relationships (one-to-many, many-to-many) and learn SQL JOINs to query related data.

## Duration

2.5-3 hours

## Task

Create a blog system with users, posts, and comments demonstrating relationship queries with JOINs.

## Requirements

- [ ] Create `users` table: id, name, email, created_at
- [ ] Create `posts` table: id, title, content, user_id (FK), created_at
- [ ] Create `comments` table: id, content, post_id (FK), user_id (FK), created_at
- [ ] Implement INNER JOIN to get posts with author info
- [ ] Implement LEFT JOIN to get posts with comment count
- [ ] Implement multiple JOINs for full post with comments and authors
- [ ] Create methods to fetch related data
- [ ] Handle null values in optional relationships

## Starter Files

Work in `Database.php`, `User.php`, `Post.php`, `Comment.php`, and `test-relationships.php`

## SQL Table Definitions

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content TEXT NOT NULL,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

## Expected Output

```
php test-relationships.php

=== Database Relationships and JOINs ===

Created users, posts, and comments...

Posts with Authors (INNER JOIN):
1. First Post by John Doe
2. Second Post by Jane Smith

Posts with Comment Count (LEFT JOIN):
1. First Post - 2 comments
2. Second Post - 1 comment

Full Post Details:
First Post by John Doe
Comments:
  - Great article! by Alice Brown
  - Thanks for sharing! by Bob Wilson

Second Post by Jane Smith
Comments:
  - Nice insights! by Alice Brown
```

## Checklist

- [ ] Users, Posts, Comments tables created
- [ ] Foreign key constraints implemented
- [ ] INNER JOIN query fetches posts with authors
- [ ] LEFT JOIN query handles posts without comments
- [ ] Multiple JOINs work correctly
- [ ] All related data fetched in single query
- [ ] Null values handled properly
- [ ] Methods return properly formatted data
- [ ] Code runs without errors
- [ ] Query performance is reasonable

## Tips

- INNER JOIN only returns matching rows
- LEFT JOIN returns all rows from left table
- Multiple JOINs can connect more than 2 tables
- Use aliases (AS) for clearer column names
- Count() with GROUP BY for counting related records
- Test queries in database first, then implement in PHP
