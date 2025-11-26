# Lesson 08 - Database Design: Building Good Schemas

## Why Database Design Matters

Bad database design leads to:
- Data duplication and wasted space
- Slow queries and poor performance
- Data integrity issues and bugs
- Difficult to maintain and extend
- Confused developers and angry users

**Good database design prevents all of this!**

---

## The Database Design Process

### 1. Understand Requirements

**Ask:**
- What data do we need to store?
- How will the data be queried?
- What relationships exist?
- What are the business rules?

**Example: Blog Application**
- Users can register and login
- Users can create posts
- Users can comment on posts
- Posts can have multiple tags
- Posts belong to categories

### 2. Identify Entities

**Entities** are the "things" you're storing. Usually they become tables.

**Blog entities:**
- User
- Post
- Comment
- Tag
- Category

### 3. Define Attributes

**Attributes** are properties of entities. These become columns.

**User attributes:**
- id, name, email, password, created_at

**Post attributes:**
- id, user_id, category_id, title, content, created_at, updated_at

### 4. Identify Relationships

- User **has many** Posts (one-to-many)
- Post **belongs to** Category (many-to-one)
- Post **has many** Comments (one-to-many)
- Post **has many** Tags (many-to-many)

### 5. Normalize (Avoid Duplication)

Remove redundant data. More on this below!

### 6. Add Constraints

- Primary keys
- Foreign keys
- Unique constraints
- NOT NULL constraints
- Default values

---

## Normalization: Removing Redundancy

**Normalization** is the process of organizing data to minimize duplication.

### Before Normalization (Bad)

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY,
    customer_name VARCHAR(100),
    customer_email VARCHAR(255),
    customer_address TEXT,
    product_name VARCHAR(255),
    product_price DECIMAL(10,2),
    quantity INT
);
```

**Sample data:**
```
id | customer_name | customer_email    | customer_address | product_name | product_price | quantity
---+---------------+-------------------+------------------+--------------+---------------+---------
1  | Kieu          | kieu@example.com  | 123 Main St      | Laptop       | 1299.99       | 1
2  | Kieu          | kieu@example.com  | 123 Main St      | Mouse        | 29.99         | 2
3  | John          | john@example.com  | 456 Oak Ave      | Laptop       | 1299.99       | 1
```

**Problems:**
- Kieu's info duplicated in rows 1 and 2
- If Kieu changes email, must update multiple rows
- "Laptop" price duplicated - inconsistency risk
- Can't store customers without orders
- Can't store products without orders

### After Normalization (Good)

```sql
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    email VARCHAR(255),
    address TEXT
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    price DECIMAL(10,2)
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    order_date TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT,
    price DECIMAL(10,2),  -- Price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

**Benefits:**
- Customer info stored once
- Product info stored once
- Can store customers before they order
- Can store products before they're ordered
- Historical pricing (price in order_items might differ from current product price)

---

## Normal Forms (Don't Worry Too Much)

**1NF (First Normal Form):** Each column contains atomic values (no arrays or lists)

```sql
-- L Not 1NF - tags is a list
CREATE TABLE posts (
    id INT,
    title VARCHAR(255),
    tags VARCHAR(255)  -- "PHP, MySQL, Tutorial"
);

--  1NF - separate table for tags
CREATE TABLE posts (id INT, title VARCHAR(255));
CREATE TABLE tags (id INT, name VARCHAR(50));
CREATE TABLE post_tag (post_id INT, tag_id INT);
```

**2NF (Second Normal Form):** No partial dependencies (all non-key columns depend on the entire primary key)

**3NF (Third Normal Form):** No transitive dependencies (non-key columns don't depend on other non-key columns)

**Practical advice:** Just ask yourself:
1. Is any data duplicated? ’ Separate it into another table
2. Does this column depend on something other than the primary key? ’ Move it

**Don't obsess over normal forms.** Focus on avoiding duplication!

---

## Choosing Data Types

### Strings

```sql
-- Fixed length (faster, wastes space)
CHAR(10)          -- Exactly 10 characters (padded)

-- Variable length (saves space)
VARCHAR(255)      -- Up to 255 characters
VARCHAR(100)      -- Up to 100 characters

-- Long text
TEXT              -- Up to 65,535 characters
MEDIUMTEXT        -- Up to 16 million characters
LONGTEXT          -- Up to 4GB
```

**Rules:**
- Use VARCHAR for known-length data (names, emails, titles)
- Use TEXT for long content (posts, bios, descriptions)
- Choose appropriate lengths (don't use VARCHAR(255) for everything)

```sql
--  Good
name VARCHAR(100)
email VARCHAR(255)
title VARCHAR(200)
content TEXT

-- L Wasteful
name VARCHAR(255)
email VARCHAR(500)
title VARCHAR(1000)
```

### Numbers

```sql
-- Integers
TINYINT           -- -128 to 127 (or 0-255 unsigned)
SMALLINT          -- -32,768 to 32,767
INT               -- -2 billion to 2 billion
BIGINT            -- Huge numbers

-- Decimals
DECIMAL(10, 2)    -- 10 digits total, 2 after decimal (99999999.99)
FLOAT             -- Approximate decimals (don't use for money!)
DOUBLE            -- More precise float
```

**Rules:**
- Use INT for most numbers (IDs, quantities, ages)
- Use DECIMAL for money (never FLOAT!)
- Use BIGINT for very large numbers (view counts on viral posts)
- Use TINYINT for small numbers (0-100) to save space

```sql
--  Good
user_id INT
age TINYINT
price DECIMAL(10, 2)
views BIGINT

-- L Wrong types
user_id VARCHAR(50)   -- IDs should be INT
price FLOAT           -- Rounding errors with money!
age INT               -- Wastes space, TINYINT is enough
```

### Dates and Times

```sql
DATE              -- 2025-01-15
DATETIME          -- 2025-01-15 14:30:00
TIMESTAMP         -- Auto-updates, good for created_at/updated_at
TIME              -- 14:30:00
YEAR              -- 2025
```

**Rules:**
- Use TIMESTAMP for created_at, updated_at (auto-updates)
- Use DATE for birthdays, event dates
- Use DATETIME for specific points in time

```sql
--  Good
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
birth_date DATE
event_datetime DATETIME
```

### Booleans

```sql
BOOLEAN           -- Actually TINYINT(1): 0 or 1
TINYINT(1)        -- Same as BOOLEAN
```

**Rules:**
- Use for yes/no, true/false, active/inactive
- Prefix with `is_` or `has_`

```sql
is_active BOOLEAN DEFAULT TRUE
is_published BOOLEAN DEFAULT FALSE
has_verified_email BOOLEAN DEFAULT FALSE
```

### Enums (Use Carefully)

```sql
role ENUM('admin', 'user', 'moderator')
status ENUM('draft', 'published', 'archived')
```

**Pros:** Enforced values, space-efficient

**Cons:** Hard to change, better to use a lookup table:

```sql
-- Better approach
CREATE TABLE roles (
    id INT PRIMARY KEY,
    name VARCHAR(50)
);

CREATE TABLE users (
    id INT PRIMARY KEY,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);
```

---

## Naming Conventions

### Tables
- **Lowercase, plural** - `users`, `posts`, `products`
- **Use underscores** - `blog_posts`, `order_items`
- **Descriptive** - `comments` not `c`

### Columns
- **Lowercase** - `name`, `email`
- **Use underscores** - `created_at`, `is_active`
- **Descriptive** - `title` not `t`
- **Booleans**: `is_*`, `has_*` - `is_published`, `has_avatar`

### Foreign Keys
- **`table_id`** - `user_id`, `post_id`, `category_id`

### Indexes
- **`idx_table_column`** - `idx_users_email`, `idx_posts_created_at`

### Foreign Key Constraints
- **`fk_table_referenced`** - `fk_posts_user`, `fk_comments_post`

**Consistency matters more than the exact convention!**

---

## Indexes: Making Queries Fast

### What are Indexes?

Indexes are like a book's index - help find data quickly.

**Without index:**
```sql
SELECT * FROM users WHERE email = 'kieu@example.com';
-- MySQL scans ALL rows (slow!)
```

**With index:**
```sql
CREATE INDEX idx_users_email ON users(email);
SELECT * FROM users WHERE email = 'kieu@example.com';
-- MySQL uses index (fast!)
```

### When to Add Indexes

**Always index:**
- Primary keys (automatic)
- Foreign keys
- Columns in WHERE clauses
- Columns in ORDER BY
- Columns in JOIN conditions

```sql
-- Foreign keys
CREATE INDEX idx_posts_user_id ON posts(user_id);

-- WHERE conditions
CREATE INDEX idx_users_email ON users(email);

-- ORDER BY
CREATE INDEX idx_posts_created_at ON posts(created_at);
```

### Unique Indexes

```sql
-- Email must be unique
CREATE UNIQUE INDEX idx_users_email ON users(email);

-- Or in table definition
CREATE TABLE users (
    id INT PRIMARY KEY,
    email VARCHAR(255) UNIQUE
);
```

### Composite Indexes (Multiple Columns)

```sql
-- Queries that filter by user_id AND created_at
CREATE INDEX idx_posts_user_created ON posts(user_id, created_at);
```

**Order matters!** This index helps:
- `WHERE user_id = 1` 
- `WHERE user_id = 1 AND created_at > '2025-01-01'` 
- `WHERE created_at > '2025-01-01'` L (doesn't use the index)

### When NOT to Index

- Small tables (< 1000 rows) - no benefit
- Columns that change frequently - index maintenance overhead
- Too many indexes - slows down INSERT/UPDATE

**Rule of thumb:** Index columns you query often, but don't go crazy.

---

## Constraints: Enforcing Rules

### NOT NULL

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,     -- Required
    bio TEXT                         -- Optional
);
```

### UNIQUE

```sql
email VARCHAR(255) NOT NULL UNIQUE
```

### DEFAULT

```sql
is_active BOOLEAN DEFAULT TRUE
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
role VARCHAR(50) DEFAULT 'user'
```

### CHECK (MySQL 8.0+)

```sql
CREATE TABLE products (
    id INT PRIMARY KEY,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    CHECK (price > 0),
    CHECK (stock >= 0)
);
```

---

## Auto-Incrementing IDs

**Always use auto-increment for primary keys:**

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100)
);
```

**Benefits:**
- Unique automatically
- No need to specify on INSERT
- Works great for foreign keys

**Don't do this:**
```sql
-- L Bad - you manage IDs manually
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100)
);

INSERT INTO users (id, name) VALUES (1, 'Kieu');  -- Tedious!
```

---

## Timestamps: Track Changes

**Every table should have:**

```sql
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Benefits:**
- Know when record was created
- Know when it was last modified
- Audit trail
- Can order by recency

---

## Soft Deletes

Instead of deleting records, mark them as deleted:

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    deleted_at TIMESTAMP NULL
);

-- "Delete" a post
UPDATE posts SET deleted_at = NOW() WHERE id = 1;

-- Get active posts
SELECT * FROM posts WHERE deleted_at IS NULL;

-- Get deleted posts
SELECT * FROM posts WHERE deleted_at IS NOT NULL;
```

**Benefits:**
- Can recover deleted data
- Audit trail
- Preserve referential integrity

**Drawbacks:**
- Requires filtering in every query
- Database grows larger

---

## Common Design Patterns

### Polymorphic Relationships

One table relates to multiple tables:

```sql
-- Comments can be on posts OR products
CREATE TABLE comments (
    id INT PRIMARY KEY,
    commentable_id INT,
    commentable_type VARCHAR(50),  -- 'post' or 'product'
    content TEXT
);
```

**Better approach:** Separate tables:

```sql
CREATE TABLE post_comments (
    id INT PRIMARY KEY,
    post_id INT,
    content TEXT,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);

CREATE TABLE product_comments (
    id INT PRIMARY KEY,
    product_id INT,
    content TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id)
);
```

### Settings/Options Table

Store key-value pairs:

```sql
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(100) UNIQUE,
    value TEXT
);

INSERT INTO settings (key, value) VALUES
    ('site_name', 'My Blog'),
    ('posts_per_page', '10'),
    ('email_notifications', 'true');
```

---

## Real-World Example: E-commerce Schema

```sql
-- Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_email (email)
);

-- Categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT,
    FOREIGN KEY (parent_id) REFERENCES categories(id)
);

-- Products
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_products_category (category_id),
    INDEX idx_products_slug (slug),
    CHECK (price > 0),
    CHECK (stock >= 0)
);

-- Orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    total DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_orders_user (user_id),
    INDEX idx_orders_status (status)
);

-- Order Items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,  -- Price at time of order
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    CHECK (quantity > 0)
);
```

---

## Common Mistakes

### 1. No Primary Key

```sql
-- L Bad
CREATE TABLE logs (
    message TEXT,
    created_at TIMESTAMP
);

--  Good
CREATE TABLE logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message TEXT,
    created_at TIMESTAMP
);
```

### 2. Wrong Data Types

```sql
-- L Bad
price VARCHAR(20)  -- "19.99" as string
is_active INT      -- 0/1 when BOOLEAN is clearer

--  Good
price DECIMAL(10,2)
is_active BOOLEAN
```

### 3. No Foreign Keys

```sql
-- L Bad - no constraint
CREATE TABLE posts (
    id INT PRIMARY KEY,
    user_id INT
);

--  Good
CREATE TABLE posts (
    id INT PRIMARY KEY,
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### 4. Storing Arrays as Strings

```sql
-- L Bad
tags VARCHAR(255)  -- "PHP,MySQL,Web"

--  Good - many-to-many relationship
CREATE TABLE tags (id INT, name VARCHAR(50));
CREATE TABLE post_tag (post_id INT, tag_id INT);
```

---

## Practice Exercise: Design a Social Network

Design the database for a simple social network with these features:

**Requirements:**
- Users can register and have profiles
- Users can create posts
- Users can like posts
- Users can comment on posts
- Users can follow other users
- Posts can include images

**Your task:**
1. Identify entities (tables)
2. Define attributes (columns)
3. Identify relationships
4. Write CREATE TABLE statements

<details>
<summary>Possible Solution</summary>

```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE likes (
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE follows (
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (follower_id, following_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE
);
```
</details>

---

## Key Takeaways

1. **Normalize to avoid duplication** - Each piece of data stored once
2. **Choose appropriate data types** - DECIMAL for money, not FLOAT
3. **Use constraints** - NOT NULL, UNIQUE, FOREIGN KEY
4. **Index foreign keys and WHERE columns** - Makes queries fast
5. **Always have created_at/updated_at** - Track changes
6. **Follow naming conventions** - Consistency matters
7. **Primary keys are always AUTO_INCREMENT** - Let database handle IDs
8. **Think about relationships early** - One-to-many, many-to-many

---

**Next Lesson:** [09 - OOP with Databases: Models and Classes](./09-oop-database.md)
