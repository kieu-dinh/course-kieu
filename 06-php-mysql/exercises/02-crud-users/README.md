# Exercise 6.2 - CRUD Operations with Users

## Objective

Learn Create, Read, Update, and Delete (CRUD) operations using PDO with a users table.

## Duration

2-2.5 hours

## Task

Create a User management system with full CRUD functionality.

## Requirements

- [ ] Create `users` table in MySQL with fields: id, name, email, phone, created_at, updated_at
- [ ] Create `Database.php` with connection class
- [ ] Create `User.php` class with CRUD methods
- [ ] Implement `create()` method - insert new user
- [ ] Implement `read($id)` method - fetch single user
- [ ] Implement `readAll()` method - fetch all users
- [ ] Implement `update($id, $data)` method - update user data
- [ ] Implement `delete($id)` method - delete user
- [ ] Use prepared statements for all queries
- [ ] Implement proper error handling

## Starter Files

Work in `Database.php`, `User.php`, and `test-crud.php` - see starter code there.

## SQL Table Definition

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Expected Output

```
php test-crud.php

=== User CRUD Operations ===

Creating new user...
User created with ID: 1

Reading user (ID: 1)...
Name: John Doe
Email: john@example.com
Phone: 555-0123

Updating user...
User updated successfully!

Reading updated user...
Name: John D.
Email: john@example.com

Listing all users...
1. John D. (john@example.com)

Deleting user...
User deleted successfully!

All users after deletion:
(empty)
```

## Checklist

- [ ] Database table created with correct schema
- [ ] Database class handles connection
- [ ] User class created with CRUD methods
- [ ] create() inserts user and returns ID
- [ ] read() fetches single user
- [ ] readAll() returns array of all users
- [ ] update() modifies user data
- [ ] delete() removes user from database
- [ ] All queries use prepared statements
- [ ] Error handling for duplicate emails and invalid data
- [ ] Code runs without errors

## Tips

- Use id AUTO_INCREMENT for primary key
- Use UNIQUE constraint for email field
- Prepared statements use ? or :name for placeholders
- bindParam() or bindValue() bind values to placeholders
- execute() runs the prepared statement
- fetch() gets single result, fetchAll() gets all results
- Test each CRUD operation before moving to next
