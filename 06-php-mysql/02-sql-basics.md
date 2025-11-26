# Lesson 02 - SQL Basics: Your First Queries

## What is SQL?

**SQL** (Structured Query Language) is the language you use to talk to databases.

Think of it like this:
- **English** is how you talk to humans
- **PHP** is how you tell the server what to do
- **SQL** is how you ask the database for data

SQL looks almost like English:

```sql
SELECT * FROM users WHERE age > 18;
```

**Reads like:** "Select everything from users where age is greater than 18"

---

## The Four Core Operations: CRUD

Almost everything you do with a database falls into one of these categories:

| Operation | SQL Command | What it does |
|-----------|-------------|--------------|
| **C**reate | INSERT | Add new data |
| **R**ead | SELECT | Get data |
| **U**pdate | UPDATE | Modify existing data |
| **D**elete | DELETE | Remove data |

**CRUD = Create, Read, Update, Delete**

Every app you build will use these operations constantly.

---

## Creating a Table

Before we can add data, we need a table. Let's create a simple `users` table:

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    age INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Breaking it down:**

- `CREATE TABLE users` - Make a new table called "users"
- `id INT PRIMARY KEY AUTO_INCREMENT` - Unique ID that auto-increments (1, 2, 3...)
- `name VARCHAR(100) NOT NULL` - Name, max 100 chars, required
- `email VARCHAR(255) NOT NULL UNIQUE` - Email, required, must be unique
- `age INT` - Age, optional (no NOT NULL)
- `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP` - Auto-set to current time

**Try it in TablePlus or phpMyAdmin!**

---

## INSERT: Creating Data

Add new rows to your table:

### Basic INSERT

```sql
INSERT INTO users (name, email, age)
VALUES ('Kieu', 'kieu@example.com', 28);
```

**Result:** New user with id=1 is created!

### Insert Multiple Rows

```sql
INSERT INTO users (name, email, age)
VALUES
    ('John', 'john@example.com', 32),
    ('Sarah', 'sarah@example.com', 25),
    ('Mike', 'mike@example.com', 30);
```

**Note:** We don't specify `id` because it auto-increments. We don't specify `created_at` because it has a default value.

### Insert Without Some Columns

```sql
-- Age is optional, so we can skip it
INSERT INTO users (name, email)
VALUES ('Anna', 'anna@example.com');
```

Anna's age will be `NULL` (empty).

---

## SELECT: Reading Data

This is the most common operation - getting data from the database.

### Get Everything

```sql
SELECT * FROM users;
```

**`*` means "all columns"**

Result:
```
id | name  | email              | age | created_at
---+-------+--------------------+-----+-------------------
1  | Kieu  | kieu@example.com   | 28  | 2025-01-15 10:00
2  | John  | john@example.com   | 32  | 2025-01-15 10:01
3  | Sarah | sarah@example.com  | 25  | 2025-01-15 10:02
4  | Mike  | mike@example.com   | 30  | 2025-01-15 10:03
5  | Anna  | anna@example.com   | NULL| 2025-01-15 10:04
```

### Select Specific Columns

```sql
SELECT name, email FROM users;
```

Result:
```
name  | email
------+------------------
Kieu  | kieu@example.com
John  | john@example.com
...
```

**Pro tip:** Only select columns you need. Faster and uses less memory!

### WHERE Clause: Filtering Data

Get only rows that match a condition:

```sql
-- Find user with id 3
SELECT * FROM users WHERE id = 3;

-- Find user by email
SELECT * FROM users WHERE email = 'kieu@example.com';

-- Find users older than 25
SELECT * FROM users WHERE age > 25;

-- Find users 25 or younger
SELECT * FROM users WHERE age <= 25;

-- Find users exactly 28 years old
SELECT * FROM users WHERE age = 28;

-- Find users NOT 28
SELECT * FROM users WHERE age != 28;
```

### Comparison Operators

| Operator | Meaning |
|----------|---------|
| `=` | Equal |
| `!=` or `<>` | Not equal |
| `>` | Greater than |
| `<` | Less than |
| `>=` | Greater than or equal |
| `<=` | Less than or equal |

### Combining Conditions with AND/OR

```sql
-- Users older than 25 AND named Kieu
SELECT * FROM users
WHERE age > 25 AND name = 'Kieu';

-- Users named Kieu OR John
SELECT * FROM users
WHERE name = 'Kieu' OR name = 'John';

-- Complex: (age > 25 and name is Kieu) OR email contains gmail
SELECT * FROM users
WHERE (age > 25 AND name = 'Kieu')
   OR email LIKE '%gmail.com';
```

### LIKE: Pattern Matching

```sql
-- Find emails ending in gmail.com
SELECT * FROM users WHERE email LIKE '%gmail.com';

-- Find names starting with 'K'
SELECT * FROM users WHERE name LIKE 'K%';

-- Find names containing 'oh' (John, Johanna)
SELECT * FROM users WHERE name LIKE '%oh%';

-- Underscore matches one character
-- Find names like 'Mike' or 'Mika'
SELECT * FROM users WHERE name LIKE 'Mik_';
```

**`%` = zero or more characters**
**`_` = exactly one character**

### IN: Match Multiple Values

```sql
-- Users named Kieu, John, or Sarah
SELECT * FROM users
WHERE name IN ('Kieu', 'John', 'Sarah');

-- Instead of:
SELECT * FROM users
WHERE name = 'Kieu' OR name = 'John' OR name = 'Sarah';
```

### BETWEEN: Range Matching

```sql
-- Users aged 25 to 30 (inclusive)
SELECT * FROM users WHERE age BETWEEN 25 AND 30;

-- Instead of:
SELECT * FROM users WHERE age >= 25 AND age <= 30;
```

### IS NULL / IS NOT NULL

```sql
-- Find users who didn't provide age
SELECT * FROM users WHERE age IS NULL;

-- Find users who DID provide age
SELECT * FROM users WHERE age IS NOT NULL;
```

**Note:** Don't use `= NULL`, it won't work! Always use `IS NULL`.

---

## ORDER BY: Sorting Results

```sql
-- Sort by name A-Z
SELECT * FROM users ORDER BY name ASC;

-- Sort by name Z-A
SELECT * FROM users ORDER BY name DESC;

-- Sort by age, youngest first
SELECT * FROM users ORDER BY age ASC;

-- Sort by age, oldest first
SELECT * FROM users ORDER BY age DESC;

-- Sort by multiple columns
-- First by age (oldest first), then by name (A-Z)
SELECT * FROM users ORDER BY age DESC, name ASC;
```

**ASC = Ascending (low to high, A-Z)**
**DESC = Descending (high to low, Z-A)**

---

## LIMIT: Restrict Number of Results

```sql
-- Get only first 3 users
SELECT * FROM users LIMIT 3;

-- Get 5 users, starting from the 10th (pagination!)
SELECT * FROM users LIMIT 5 OFFSET 10;

-- Shorthand for offset
SELECT * FROM users LIMIT 10, 5;  -- Skip 10, get 5
```

**Common use: Pagination**
```sql
-- Page 1 (users 1-10)
SELECT * FROM users LIMIT 10 OFFSET 0;

-- Page 2 (users 11-20)
SELECT * FROM users LIMIT 10 OFFSET 10;

-- Page 3 (users 21-30)
SELECT * FROM users LIMIT 10 OFFSET 20;
```

---

## UPDATE: Modifying Data

Change existing rows:

### Update One Row

```sql
-- Change Kieu's age
UPDATE users
SET age = 29
WHERE id = 1;
```

**CRITICAL:** Always use WHERE! Without it, you update ALL rows!

```sql
-- DANGER: This sets everyone's age to 29!
UPDATE users SET age = 29;  -- L Forgot WHERE!
```

### Update Multiple Columns

```sql
UPDATE users
SET name = 'Kieu Nguyen', age = 29
WHERE id = 1;
```

### Update Based on Conditions

```sql
-- Add 1 year to everyone over 25
UPDATE users
SET age = age + 1
WHERE age > 25;

-- Update all gmail users
UPDATE users
SET email = CONCAT('new_', email)
WHERE email LIKE '%gmail.com';
```

---

## DELETE: Removing Data

Remove rows from the table:

### Delete One Row

```sql
DELETE FROM users WHERE id = 5;
```

### Delete Based on Conditions

```sql
-- Delete users under 18
DELETE FROM users WHERE age < 18;

-- Delete users with no age
DELETE FROM users WHERE age IS NULL;
```

### Delete Everything (Dangerous!)

```sql
--   DANGER: Deletes ALL users!
DELETE FROM users;

-- Better: TRUNCATE is faster for deleting all
TRUNCATE TABLE users;  -- Resets auto-increment too
```

**Pro tip:** Always use WHERE unless you really want to delete everything!

**Test safely:** Run the SELECT first to see what will be deleted:
```sql
-- First, check what you'll delete
SELECT * FROM users WHERE age < 18;

-- If it looks right, change SELECT to DELETE
DELETE FROM users WHERE age < 18;
```

---

## Aggregate Functions: Math on Data

Get statistics about your data:

### COUNT: How Many Rows

```sql
-- Total number of users
SELECT COUNT(*) FROM users;

-- Users over 25
SELECT COUNT(*) FROM users WHERE age > 25;

-- Non-null ages (excludes NULL values)
SELECT COUNT(age) FROM users;
```

### SUM: Add Up Values

```sql
-- Total age of all users (weird example, but shows concept)
SELECT SUM(age) FROM users;

-- More realistic: total of order amounts
SELECT SUM(amount) FROM orders;
```

### AVG: Average Value

```sql
-- Average age
SELECT AVG(age) FROM users;

-- Average age of users over 25
SELECT AVG(age) FROM users WHERE age > 25;
```

### MIN and MAX: Smallest and Largest

```sql
-- Youngest user's age
SELECT MIN(age) FROM users;

-- Oldest user's age
SELECT MAX(age) FROM users;

-- Earliest created user
SELECT MIN(created_at) FROM users;
```

### Combining Aggregates

```sql
SELECT
    COUNT(*) as total_users,
    AVG(age) as average_age,
    MIN(age) as youngest,
    MAX(age) as oldest
FROM users;
```

Result:
```
total_users | average_age | youngest | oldest
------------+-------------+----------+--------
5           | 28.75       | 25       | 32
```

---

## GROUP BY: Grouping Data

Group rows by a column and run aggregates on each group:

```sql
-- Count users by age
SELECT age, COUNT(*) as count
FROM users
GROUP BY age;
```

Result:
```
age  | count
-----+------
25   | 1
28   | 1
30   | 1
32   | 1
NULL | 1
```

### Real-World Example: Posts Per User

```sql
SELECT
    user_id,
    COUNT(*) as post_count
FROM posts
GROUP BY user_id;
```

Result:
```
user_id | post_count
--------+-----------
1       | 5
2       | 3
3       | 8
```

### HAVING: Filter Groups

`WHERE` filters rows. `HAVING` filters groups.

```sql
-- Users with more than 3 posts
SELECT
    user_id,
    COUNT(*) as post_count
FROM posts
GROUP BY user_id
HAVING COUNT(*) > 3;
```

Result:
```
user_id | post_count
--------+-----------
1       | 5
3       | 8
```

**Remember:** WHERE before GROUP BY, HAVING after GROUP BY.

---

## DISTINCT: Unique Values Only

```sql
-- All unique ages (no duplicates)
SELECT DISTINCT age FROM users;

-- Unique email domains
SELECT DISTINCT
    SUBSTRING_INDEX(email, '@', -1) as domain
FROM users;
```

---

## String Functions

Manipulate text in SQL:

```sql
-- Uppercase names
SELECT UPPER(name) FROM users;
-- Result: KIEU, JOHN, SARAH...

-- Lowercase emails
SELECT LOWER(email) FROM users;

-- Concatenate strings
SELECT CONCAT(name, ' - ', email) as user_info FROM users;
-- Result: Kieu - kieu@example.com

-- String length
SELECT name, LENGTH(name) as name_length FROM users;

-- First 3 characters
SELECT LEFT(name, 3) FROM users;
-- Result: Kie, Joh, Sar...

-- Replace text
SELECT REPLACE(email, 'example.com', 'newdomain.com') FROM users;
```

---

## Date Functions

Work with dates and times:

```sql
-- Current date and time
SELECT NOW();  -- 2025-01-15 14:30:00

-- Current date only
SELECT CURDATE();  -- 2025-01-15

-- Current time only
SELECT CURTIME();  -- 14:30:00

-- Extract parts of a date
SELECT
    YEAR(created_at) as year,
    MONTH(created_at) as month,
    DAY(created_at) as day
FROM users;

-- Users created today
SELECT * FROM users
WHERE DATE(created_at) = CURDATE();

-- Users created in last 7 days
SELECT * FROM users
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Format dates
SELECT DATE_FORMAT(created_at, '%M %d, %Y') FROM users;
-- Result: January 15, 2025
```

---

## Aliases: Making Output Readable

Use `AS` to rename columns in output:

```sql
SELECT
    name AS user_name,
    email AS user_email,
    age AS user_age
FROM users;
```

Or shorter (AS is optional):

```sql
SELECT
    name user_name,
    email user_email,
    COUNT(*) total_users
FROM users;
```

---

## Comments in SQL

Document your queries:

```sql
-- Single line comment
SELECT * FROM users;  -- Get all users

/*
  Multi-line comment
  This query finds active users
  created in the last month
*/
SELECT * FROM users
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH);
```

---

## SQL Query Order Matters!

SQL keywords must be in this order:

```sql
SELECT columns
FROM table
WHERE conditions
GROUP BY columns
HAVING group_conditions
ORDER BY columns
LIMIT number;
```

**You can skip steps, but can't rearrange them:**

```sql
--  Correct
SELECT * FROM users WHERE age > 25 ORDER BY name;

-- L Wrong - ORDER BY before WHERE
SELECT * FROM users ORDER BY name WHERE age > 25;
```

---

## Practice Exercises

Let's practice! Create a `products` table:

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Insert some data:

```sql
INSERT INTO products (name, price, stock, category) VALUES
('Laptop', 1299.99, 15, 'Electronics'),
('Mouse', 29.99, 50, 'Electronics'),
('Desk Chair', 199.99, 8, 'Furniture'),
('Monitor', 399.99, 12, 'Electronics'),
('Notebook', 4.99, 100, 'Stationery'),
('Pen Set', 12.99, 75, 'Stationery'),
('Standing Desk', 599.99, 5, 'Furniture');
```

### Exercise 1: Basic Queries

Write queries to:
1. Get all products
2. Get only product names and prices
3. Find the product with id 3
4. Find all products in the 'Electronics' category
5. Find products priced over $100

<details>
<summary>Solutions</summary>

```sql
-- 1. All products
SELECT * FROM products;

-- 2. Names and prices only
SELECT name, price FROM products;

-- 3. Product with id 3
SELECT * FROM products WHERE id = 3;

-- 4. Electronics
SELECT * FROM products WHERE category = 'Electronics';

-- 5. Over $100
SELECT * FROM products WHERE price > 100;
```
</details>

### Exercise 2: Filtering and Sorting

Write queries to:
1. Find products with stock less than 10
2. Find products priced between $10 and $50
3. Find products in either 'Furniture' or 'Stationery'
4. Sort products by price (cheapest first)
5. Get the 3 most expensive products

<details>
<summary>Solutions</summary>

```sql
-- 1. Low stock
SELECT * FROM products WHERE stock < 10;

-- 2. Between $10 and $50
SELECT * FROM products WHERE price BETWEEN 10 AND 50;

-- 3. Furniture or Stationery
SELECT * FROM products
WHERE category IN ('Furniture', 'Stationery');

-- 4. Cheapest first
SELECT * FROM products ORDER BY price ASC;

-- 5. Top 3 most expensive
SELECT * FROM products ORDER BY price DESC LIMIT 3;
```
</details>

### Exercise 3: Aggregates

Write queries to:
1. Count total number of products
2. Count products in 'Electronics' category
3. Calculate average product price
4. Find the most expensive product price
5. Calculate total value of inventory (price × stock for all products)

<details>
<summary>Solutions</summary>

```sql
-- 1. Total products
SELECT COUNT(*) FROM products;

-- 2. Electronics count
SELECT COUNT(*) FROM products WHERE category = 'Electronics';

-- 3. Average price
SELECT AVG(price) FROM products;

-- 4. Highest price
SELECT MAX(price) FROM products;

-- 5. Total inventory value
SELECT SUM(price * stock) as total_inventory_value FROM products;
```
</details>

### Exercise 4: Updates and Deletes

Write queries to:
1. Increase the price of all Electronics by 10%
2. Add 5 stock to all products with stock less than 10
3. Change the category of 'Pen Set' to 'Office Supplies'
4. Delete products with 0 stock
5. Delete all products priced under $5

<details>
<summary>Solutions</summary>

```sql
-- 1. Increase Electronics prices by 10%
UPDATE products
SET price = price * 1.10
WHERE category = 'Electronics';

-- 2. Add 5 stock to low-stock items
UPDATE products
SET stock = stock + 5
WHERE stock < 10;

-- 3. Change Pen Set category
UPDATE products
SET category = 'Office Supplies'
WHERE name = 'Pen Set';

-- 4. Delete out-of-stock
DELETE FROM products WHERE stock = 0;

-- 5. Delete cheap products
DELETE FROM products WHERE price < 5;
```
</details>

---

## Common Mistakes to Avoid

### 1. Forgetting WHERE in UPDATE/DELETE

```sql
-- L DANGER: Updates ALL rows!
UPDATE users SET age = 30;

--  Correct: Updates specific row
UPDATE users SET age = 30 WHERE id = 1;
```

### 2. Using = NULL Instead of IS NULL

```sql
-- L Wrong: Returns nothing
SELECT * FROM users WHERE age = NULL;

--  Correct
SELECT * FROM users WHERE age IS NULL;
```

### 3. Forgetting Quotes Around Strings

```sql
-- L Wrong: Syntax error
SELECT * FROM users WHERE name = Kieu;

--  Correct: Strings need quotes
SELECT * FROM users WHERE name = 'Kieu';
```

### 4. Wrong Order of Keywords

```sql
-- L Wrong: ORDER BY must come after WHERE
SELECT * FROM users ORDER BY name WHERE age > 25;

--  Correct
SELECT * FROM users WHERE age > 25 ORDER BY name;
```

### 5. Aggregate Without GROUP BY

```sql
-- L Wrong: Can't mix aggregate and non-aggregate
SELECT name, COUNT(*) FROM users;

--  Correct: Group by the non-aggregate column
SELECT name, COUNT(*) FROM users GROUP BY name;
```

---

## SQL Style Tips

Make your queries readable:

**Uppercase keywords:**
```sql
--  Good
SELECT * FROM users WHERE age > 25;

-- = Works but harder to read
select * from users where age > 25;
```

**Line breaks for complex queries:**
```sql
--  Readable
SELECT
    name,
    email,
    age
FROM users
WHERE age > 25
ORDER BY name ASC
LIMIT 10;

-- = Hard to read
SELECT name, email, age FROM users WHERE age > 25 ORDER BY name ASC LIMIT 10;
```

**Indent for readability:**
```sql
SELECT
    u.name,
    COUNT(p.id) as post_count
FROM users u
LEFT JOIN posts p ON u.id = p.user_id
WHERE u.age > 25
GROUP BY u.id
HAVING COUNT(p.id) > 3
ORDER BY post_count DESC;
```

---

## Quick Reference

### Basic Structure
```sql
SELECT columns FROM table WHERE condition;
INSERT INTO table (columns) VALUES (values);
UPDATE table SET column = value WHERE condition;
DELETE FROM table WHERE condition;
```

### Comparison Operators
`=`, `!=`, `>`, `<`, `>=`, `<=`, `LIKE`, `IN`, `BETWEEN`, `IS NULL`

### Sorting & Limiting
```sql
ORDER BY column ASC|DESC
LIMIT number OFFSET number
```

### Aggregates
`COUNT()`, `SUM()`, `AVG()`, `MIN()`, `MAX()`

### Grouping
```sql
GROUP BY column
HAVING condition
```

---

## What's Next?

You now know SQL basics! You can create tables, insert data, query it, update it, and delete it.

**But we have a problem:** You're running SQL directly in TablePlus or phpMyAdmin. In a real app, your PHP code needs to send these SQL commands.

**In the next lesson**, you'll learn how to connect PHP to MySQL using PDO (PHP Data Objects) so your website can interact with the database!

---

## Key Takeaways

1. **SQL is the language of databases** - It reads almost like English
2. **CRUD operations** - Create (INSERT), Read (SELECT), Update (UPDATE), Delete (DELETE)
3. **WHERE filters rows** - Use comparison operators to find specific data
4. **ORDER BY sorts results** - ASC (ascending) or DESC (descending)
5. **LIMIT restricts results** - Great for pagination
6. **Aggregates give statistics** - COUNT, SUM, AVG, MIN, MAX
7. **GROUP BY groups data** - For aggregates per category
8. **ALWAYS use WHERE** - In UPDATE and DELETE to avoid changing everything
9. **Test with SELECT first** - Before running UPDATE or DELETE

---

**Next Lesson:** [03 - PDO Connection: Connecting PHP to MySQL](./03-pdo-connection.md)
