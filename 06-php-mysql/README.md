# Module 06 - PHP & MySQL

**Duration**: 2-3 weeks
**Prerequisites**: Module 05 - PHP OOP

---

## Learning Objectives

By the end of this module, you will be able to:
- Understand relational databases and SQL
- Connect to MySQL using PDO (PHP Data Objects)
- Perform CRUD operations (Create, Read, Update, Delete)
- Write secure SQL queries with prepared statements
- Prevent SQL injection attacks
- Work with relationships (one-to-many, many-to-many)
- Use JOIN queries effectively
- Design database schemas
- Combine OOP with database operations

---

## Why Databases?

So far, your data disappears when the script ends. Databases let you:
- **Persist data** - Save information permanently
- **Query data** - Find exactly what you need
- **Relate data** - Connect users to their posts, orders to products
- **Scale** - Handle millions of records efficiently

---

## What You'll Learn

### 1. Database Fundamentals
- What is a relational database?
- Tables, rows, columns
- Primary keys and foreign keys
- Data types in MySQL
- Database design principles

### 2. SQL Basics
- CREATE, ALTER, DROP tables
- INSERT, SELECT, UPDATE, DELETE
- WHERE clauses
- ORDER BY, LIMIT
- Aggregate functions (COUNT, SUM, AVG)

### 3. PHP & PDO
- Why PDO over mysqli?
- Connecting to database
- Error handling with try-catch
- PDO fetch modes

### 4. CRUD Operations
- Create records (INSERT)
- Read records (SELECT)
- Update records (UPDATE)
- Delete records (DELETE)
- Prepared statements (security!)

### 5. Relationships
- One-to-many (User has many Posts)
- Many-to-many (Posts have many Tags)
- JOIN queries (INNER, LEFT)
- Foreign keys

### 6. Security
- **SQL Injection** - The #1 database threat
- Prepared statements with placeholders
- Never trust user input
- Password hashing (bcrypt)

### 7. OOP + Database
- Database classes
- Models representing tables
- Active Record pattern basics
- Separation of concerns

---

## Lessons

1. **01-database-intro.md** - What are databases?
2. **02-sql-basics.md** - Your first SQL queries
3. **03-pdo-connection.md** - Connecting PHP to MySQL
4. **04-crud-operations.md** - Create, Read, Update, Delete
5. **05-prepared-statements.md** - Security with placeholders
6. **06-relationships.md** - Foreign keys and JOINs
7. **07-joins.md** - Combining data from multiple tables
8. **08-database-design.md** - Designing good schemas
9. **09-oop-database.md** - Database classes and models
10. **10-transactions.md** - Atomic operations

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 6.1 | Database Connection | Connect to MySQL with PDO | 1 hour |
| 6.2 | CRUD for Users | Create, Read, Update, Delete users | 2-3 hours |
| 6.3 | Blog with Database | Posts table with CRUD | 3 hours |
| 6.4 | Relationships | Users, Posts, Comments with JOIN | 3-4 hours |
| 6.5 | Product Catalog | Products, Categories, complete system | 4-6 hours |

---

## Project

**Blog Application**
Build a complete blog with:
- Users table (authentication coming in Module 07!)
- Posts table (title, content, author, timestamps)
- Categories table
- Comments table
- Relationships between all tables
- CRUD for all entities
- OOP structure (User class, Post class, etc.)

This project prepares you for Module 07 where you'll add authentication!

---

## Important Security Note

**NEVER do this:**
```php
$sql = "SELECT * FROM users WHERE email = '$email'"; // VULNERABLE!
```

**ALWAYS do this:**
```php
$sql = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]); // SAFE!
```

SQL injection is one of the most common attacks. Prepared statements protect you!

---

## Tools You'll Use

- **TablePlus** or **phpMyAdmin** - Visual database management
- **MySQL** - Database server (comes with Laravel Herd)
- **PDO** - PHP's database abstraction layer

---

## Resources

- [PHP PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [SQL Tutorial](https://www.w3schools.com/sql/)

---

## Next Module

**Module 07 - Sessions & Authentication**: Learn to build login/register systems with sessions!
