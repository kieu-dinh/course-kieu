# Lesson 01 - Introduction to Databases

## What You'll Learn

- What databases are and why they're essential
- Relational databases vs other types
- Tables, rows, and columns
- Primary keys and data types
- MySQL basics and TablePlus/phpMyAdmin

---

## The Problem: Data Disappears

So far, all your PHP data lives in variables and arrays:

```php
<?php
$users = [
    ['name' => 'Kieu', 'email' => 'kieu@example.com'],
    ['name' => 'John', 'email' => 'john@example.com']
];
```

**Problem**: When the script finishes, this data vanishes. Forever.

Every time someone visits your page, you start from scratch. No memory. No persistence.

---

## The Solution: Databases

A **database** is like a permanent storage system for your data. Think of it as a very smart filing cabinet that:

- **Saves data permanently** - It's there tomorrow, next week, next year
- **Organizes data efficiently** - Easy to find exactly what you need
- **Handles millions of records** - Your array can't do that
- **Allows multiple users** - Many people can access it at the same time
- **Ensures data integrity** - Prevents corruption and maintains relationships

```
WITHOUT DATABASE                    WITH DATABASE
┌──────────────────┐               ┌──────────────────┐
│  PHP Script      │               │  PHP Script      │
│  $users = [...]  │               │  ↕                │
│  (gone when      │               │  MySQL Database  │
│   script ends)   │               │  users table     │
└──────────────────┘               │  (permanent!)    │
                                   └──────────────────┘
```

---

## Types of Databases

There are two main categories:

### 1. Relational Databases (SQL)

Data is organized in **tables** with **relationships** between them.

**Examples**: MySQL, PostgreSQL, SQLite, Microsoft SQL Server

**Use when**: You need structured data with relationships (users, posts, comments, orders, products)

**This course focuses on relational databases with MySQL.**

### 2. NoSQL Databases

Data can be stored in various formats (documents, key-value pairs, graphs).

**Examples**: MongoDB, Redis, Firebase

**Use when**: You need flexibility or very specific use cases

**Note**: Most web applications use relational databases. Learn SQL first!

---

## What is MySQL?

**MySQL** is the most popular open-source relational database.

- Free and widely supported
- Used by Facebook, Twitter, YouTube, WordPress
- Works great with PHP
- Comes built into Laravel Herd (your development environment)

**Other options you'll hear about:**
- **PostgreSQL** - More advanced features, also great
- **SQLite** - File-based, good for small projects
- **MariaDB** - MySQL fork, almost identical

For this course, we use MySQL because it's industry standard and you already have it installed with Herd!

---

## Relational Database Concepts

### Tables

A **table** is like a spreadsheet - it has columns and rows.

```
users table
┌────┬──────────┬────────────────────┬──────────┐
│ id │ name     │ email              │ role     │
├────┼──────────┼────────────────────┼──────────┤
│ 1  │ Kieu     │ kieu@example.com   │ admin    │
│ 2  │ John     │ john@example.com   │ user     │
│ 3  │ Sarah    │ sarah@example.com  │ user     │
└────┴──────────┴────────────────────┴──────────┘
```

### Columns (Fields)

**Columns** define what type of data each field can hold.

In the example above:
- `id` - A unique number for each user
- `name` - Text (the user's name)
- `email` - Text (the user's email)
- `role` - Text (admin or user)

### Rows (Records)

**Rows** are individual entries in the table. Each row represents one user, one post, one product, etc.

In the example above, there are 3 rows (3 users).

### Primary Key

The **primary key** is a unique identifier for each row. Usually, it's an `id` column.

**Rules for primary keys:**
- Must be unique (no two rows can have the same ID)
- Can't be null (every row must have one)
- Usually auto-increments (1, 2, 3, 4...)

```php
// Think of it like this in PHP:
$users = [
    1 => ['name' => 'Kieu', 'email' => 'kieu@example.com'],  // ID 1
    2 => ['name' => 'John', 'email' => 'john@example.com'],  // ID 2
    3 => ['name' => 'Sarah', 'email' => 'sarah@example.com'] // ID 3
];

// You can quickly find a user by ID
$user = $users[2]; // Instant access to John
```

---

## Data Types in MySQL

Just like PHP has string, int, float, MySQL has types too:

### Text Types

| Type | Max Size | Use Case |
|------|----------|----------|
| **VARCHAR(n)** | n characters (max 65,535) | Names, emails, titles |
| **TEXT** | 65,535 characters | Articles, descriptions |
| **MEDIUMTEXT** | 16 million characters | Books, long content |
| **LONGTEXT** | 4 GB | Massive content |

**Example:**
```sql
name VARCHAR(100)      -- Max 100 characters
bio TEXT               -- Long bio
```

### Number Types

| Type | Range | Use Case |
|------|-------|----------|
| **INT** | -2 billion to +2 billion | IDs, counts, ages |
| **BIGINT** | Huge numbers | Large counts |
| **DECIMAL(10,2)** | Exact decimals | Money (10 digits, 2 after decimal) |
| **FLOAT/DOUBLE** | Approximate decimals | Scientific calculations |

**Example:**
```sql
age INT                -- Whole number
price DECIMAL(10, 2)   -- Like 99.99
views BIGINT           -- For viral posts!
```

**Important**: Always use DECIMAL for money, never FLOAT! Floats can have rounding errors.

### Date & Time Types

| Type | Format | Example |
|------|--------|---------|
| **DATE** | YYYY-MM-DD | 2025-01-15 |
| **TIME** | HH:MM:SS | 14:30:00 |
| **DATETIME** | YYYY-MM-DD HH:MM:SS | 2025-01-15 14:30:00 |
| **TIMESTAMP** | Auto-updates | For created_at, updated_at |

**Example:**
```sql
birth_date DATE
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### Boolean Type

MySQL doesn't have a true boolean. Use:
- **TINYINT(1)** - 0 for false, 1 for true
- Or just use **BOOLEAN** (it's an alias for TINYINT(1))

**Example:**
```sql
is_active BOOLEAN DEFAULT TRUE
is_verified TINYINT(1) DEFAULT 0
```

---

## Schema: The Blueprint

A **schema** is the structure of your database - which tables exist, what columns they have, what types those columns are.

**Example schema for a blog:**

```
posts table
┌────┬──────────┬─────────────────────┬───────────┬──────────────────────┐
│ id │ title    │ content             │ author_id │ created_at           │
├────┼──────────┼─────────────────────┼───────────┼──────────────────────┤
│ 1  │ Hello    │ My first post...    │ 1         │ 2025-01-15 10:00:00  │
│ 2  │ PHP Tips │ Here are 10 tips... │ 1         │ 2025-01-16 14:30:00  │
│ 3  │ MySQL    │ Databases are...    │ 2         │ 2025-01-17 09:15:00  │
└────┴──────────┴─────────────────────┴───────────┴──────────────────────┘

users table
┌────┬──────────┬────────────────────┐
│ id │ name     │ email              │
├────┼──────────┼────────────────────┤
│ 1  │ Kieu     │ kieu@example.com   │
│ 2  │ John     │ john@example.com   │
└────┴──────────┴────────────────────┘
```

Notice how `author_id` in the posts table refers to `id` in the users table? That's a **relationship**. We'll cover that in detail later!

---

## Why Not Just Use Files?

You might think: "Can't I just save data to a .txt or .json file?"

Yes, but:

| Files | Database |
|-------|----------|
| Slow with large data | Fast even with millions of records |
| Hard to query ("find all posts by user 5") | Easy queries with SQL |
| No built-in relationships | Built-in relationship support |
| Concurrent access problems | Handles multiple users safely |
| You write all the logic | Database handles complex operations |

**Use files for**: Configuration, logs, small datasets
**Use databases for**: User data, posts, products, orders - anything dynamic

---

## SQL: The Language of Databases

**SQL** (Structured Query Language) is how you talk to databases.

It looks like English:

```sql
-- Get all users
SELECT * FROM users;

-- Get user with id 5
SELECT * FROM users WHERE id = 5;

-- Create a new user
INSERT INTO users (name, email) VALUES ('Kieu', 'kieu@example.com');

-- Update a user
UPDATE users SET name = 'Kieu Nguyen' WHERE id = 1;

-- Delete a user
DELETE FROM users WHERE id = 3;
```

**Key SQL commands you'll learn:**
- **SELECT** - Read data
- **INSERT** - Create new data
- **UPDATE** - Modify existing data
- **DELETE** - Remove data
- **CREATE TABLE** - Define new table structure
- **ALTER TABLE** - Modify table structure
- **JOIN** - Combine data from multiple tables

Don't worry, we'll cover each of these in detail in the next lessons!

---

## MySQL Tools

You'll need a tool to interact with MySQL. Two great options:

### TablePlus (Recommended)

- Beautiful, modern interface
- Free version is great for learning
- Download: [tableplus.com](https://tableplus.com)
- Works with MySQL, PostgreSQL, and more

### phpMyAdmin

- Web-based (runs in your browser)
- Free and open-source
- Often included with hosting
- Can be slower for large databases

**For this course, use whichever you prefer!** I'll show examples with both.

---

## Connecting to MySQL with Herd

Laravel Herd includes MySQL. Here's how to connect:

**Connection details:**
```
Host: 127.0.0.1 (localhost)
Port: 3306
User: root
Password: (empty)
```

**In TablePlus:**
1. Open TablePlus
2. Click "Create a new connection"
3. Choose MySQL
4. Enter the details above
5. Click "Test" then "Connect"

**In phpMyAdmin:**
1. If you have phpMyAdmin installed, visit: http://localhost/phpmyadmin
2. Login with username `root` and no password

---

## Your First Database

Let's create a database for practice!

**In TablePlus:**
1. Right-click in the sidebar
2. Select "New Database"
3. Name it: `my_first_db`
4. Click "Create"

**In phpMyAdmin:**
1. Click "New" in the sidebar
2. Enter database name: `my_first_db`
3. Choose collation: `utf8mb4_general_ci` (supports emojis!)
4. Click "Create"

**What is collation?**
It defines how text is sorted and compared. `utf8mb4_general_ci` supports all languages including Vietnamese characters and emojis. Always use this!

---

## Database Naming Conventions

Follow these rules for clean, professional code:

**Database names:**
- Lowercase: `my_app`, `blog_db`
- Use underscores: `ecommerce_prod`

**Table names:**
- Lowercase, plural: `users`, `posts`, `products`
- Use underscores: `blog_posts`, `order_items`

**Column names:**
- Lowercase: `name`, `email`
- Use underscores: `created_at`, `is_active`
- Use `id` for primary keys
- Use `table_name_id` for foreign keys: `user_id`, `post_id`

**Example:**
```sql
-- Good
CREATE TABLE blog_posts (
    id INT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    created_at TIMESTAMP
);

-- Bad (inconsistent, hard to read)
CREATE TABLE BlogPost (
    ID int primary key,
    UserID int,
    PostTitle varchar(255),
    CreateDate timestamp
);
```

---

## Key Concepts Review

Let's make sure you understand these terms:

| Term | Definition | Example |
|------|------------|---------|
| **Database** | Collection of organized data | blog_db |
| **Table** | Spreadsheet-like structure | users, posts |
| **Row (Record)** | Single entry in a table | One user |
| **Column (Field)** | Attribute of data | name, email |
| **Primary Key** | Unique identifier | id |
| **Schema** | Database structure | Tables and columns definition |
| **SQL** | Language to interact with database | SELECT, INSERT, UPDATE |
| **MySQL** | The database system we use | Installed with Herd |

---

## Quick Quiz

Test your understanding:

1. What happens to array data in PHP when the script finishes?
2. What is the main advantage of a database over storing data in files?
3. What is a primary key and why is it important?
4. What data type should you use for storing money?
5. What is SQL?

<details>
<summary>Click to see answers</summary>

1. It disappears - variables only exist during script execution
2. Databases are fast, handle relationships, support queries, and allow concurrent access safely
3. A primary key is a unique identifier for each row - it lets you reference specific records
4. DECIMAL - never use FLOAT for money due to rounding issues
5. SQL (Structured Query Language) is the language used to interact with relational databases

</details>

---

## Real-World Analogy

Think of a database like a library:

- **Database** = The entire library building
- **Tables** = Different sections (fiction, non-fiction, magazines)
- **Rows** = Individual books
- **Columns** = Book attributes (title, author, ISBN, publication year)
- **Primary Key** = ISBN (unique identifier for each book)
- **SQL** = The librarian who fetches books for you

When you ask the librarian (SQL) "find me all books by Stephen King" (query), they know exactly where to look and can find them quickly, even though there are millions of books (rows)!

---

## Looking Ahead

In the next lessons, you'll learn:

- **Lesson 02** - Writing your first SQL queries
- **Lesson 03** - Connecting PHP to MySQL with PDO
- **Lesson 04** - Creating, reading, updating, and deleting data (CRUD)
- **Lesson 05** - Protecting against SQL injection attacks
- **Lesson 06** - Creating relationships between tables
- **Lesson 07** - Joining data from multiple tables
- **Lesson 08** - Designing good database schemas
- **Lesson 09** - Using OOP with databases
- **Lesson 10** - Transactions for data integrity

---

## Try It Yourself

Before moving to the next lesson:

1. Install TablePlus or set up phpMyAdmin
2. Connect to MySQL using the Herd credentials
3. Create a database called `learning_sql`
4. Look around the interface - don't be afraid to click things!

**Important**: You can't break anything! Worst case, you delete your practice database and create a new one. That's how you learn!

---

## Key Takeaways

1. **Databases persist data permanently** - Unlike PHP variables that disappear
2. **MySQL is a relational database** - Data organized in tables with relationships
3. **Tables have rows and columns** - Like spreadsheets but much more powerful
4. **Primary keys uniquely identify rows** - Usually an auto-incrementing `id`
5. **SQL is the language** - Used to interact with the database
6. **Choose the right data types** - VARCHAR for text, INT for numbers, DECIMAL for money
7. **Follow naming conventions** - Lowercase, underscores, plural table names

---

## Next Lesson

Ready to write your first SQL queries? Let's go!

[Lesson 02: SQL Basics](./02-sql-basics.md)
