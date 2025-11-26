# Lesson 07 - Migrations and Databases

**Duration**: 60 minutes
**Difficulty**: Beginner

---

## What are Migrations?

**Migrations** are version control for your database - like Git, but for database structure.

### The Pure PHP Problem (Module 06)

**How you managed databases in pure PHP:**

```sql
-- Create tables manually in phpMyAdmin or terminal
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE posts ADD COLUMN author_id INT;

-- Email this SQL to your team
-- Hope they run it correctly
-- Hope everyone has the same database structure
```

**Problems:**
- **No history**: Can't track what changed when
- **Manual process**: Each developer runs SQL manually
- **No rollback**: Can't easily undo changes
- **Team coordination**: Hard to sync database changes
- **Production risk**: Forget to run migration = broken app

### Laravel Migrations

**Migrations are PHP classes** that define database structure:

```php
// database/migrations/2024_01_01_000000_create_posts_table.php
public function up()
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('posts');
}
```

**Run with one command:**
```bash
php artisan migrate
```

**Benefits:**
- **Version controlled**: Migrations are in Git
- **Automatic**: One command runs all migrations
- **Rollback**: Easy to undo with `migrate:rollback`
- **Team-friendly**: Everyone has same database structure
- **Database-agnostic**: Works with MySQL, PostgreSQL, SQLite, etc.

---

## Database Configuration

### The `.env` File

Open `.env` in your project:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_laravel_app
DB_USERNAME=root
DB_PASSWORD=
```

**Change these for your setup!**

**With Laravel Herd:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_laravel_app
DB_USERNAME=root
DB_PASSWORD=
```

### Creating the Database

**Option 1: Via Terminal**
```bash
mysql -u root
CREATE DATABASE my_laravel_app;
exit
```

**Option 2: Via TablePlus**
- Open TablePlus
- Connect to localhost
- Right-click → New Database
- Name: `my_laravel_app`

**Option 3: Laravel Command (MySQL only)**
```bash
php artisan db:create
```

### Testing Connection

```bash
php artisan db:show
```

Shows:
- Database name
- Connection status
- Tables
- Database size

---

## Creating Migrations

### Generate Migration

```bash
php artisan make:migration create_posts_table
```

Creates: `/database/migrations/2024_01_01_120000_create_posts_table.php`

**File naming:**
- `2024_01_01_120000` - Timestamp (when created)
- `create_posts_table` - Descriptive name

**Timestamp ensures migrations run in order!**

### Migration Naming Patterns

**Laravel is smart about names:**

```bash
# Creates a table
php artisan make:migration create_posts_table
php artisan make:migration create_users_table

# Adds columns to existing table
php artisan make:migration add_status_to_posts_table

# Removes columns
php artisan make:migration remove_draft_from_posts_table

# Creates pivot table for many-to-many
php artisan make:migration create_post_tag_table
```

Laravel generates appropriate boilerplate based on the name!

---

## Migration Structure

Open a migration file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

### `up()` Method

**What happens when migration runs:**

```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        // Define table structure
    });
}
```

### `down()` Method

**What happens when migration is rolled back:**

```php
public function down(): void
{
    Schema::dropIfExists('posts');
}
```

**Always make migrations reversible!**

---

## Column Types

Laravel provides methods for every SQL column type:

### Primary Keys

```php
$table->id();  // BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
$table->uuid('id')->primary();  // UUID primary key
```

**`id()` replaces:**
```sql
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
```

### String Columns

```php
$table->string('title');              // VARCHAR(255)
$table->string('title', 100);         // VARCHAR(100)
$table->text('content');              // TEXT
$table->longText('content');          // LONGTEXT
$table->char('code', 10);             // CHAR(10)
```

**Pure SQL:**
```sql
title VARCHAR(255),
content TEXT,
code CHAR(10)
```

### Integer Columns

```php
$table->integer('views');             // INT
$table->tinyInteger('status');        // TINYINT
$table->smallInteger('priority');     // SMALLINT
$table->bigInteger('big_number');     // BIGINT

$table->unsignedInteger('positive');  // INT UNSIGNED
$table->unsignedBigInteger('user_id'); // BIGINT UNSIGNED (for foreign keys)
```

### Decimal/Float Columns

```php
$table->decimal('price', 8, 2);       // DECIMAL(8,2) - for money
$table->float('rating');              // FLOAT
$table->double('latitude');           // DOUBLE
```

**Use `decimal` for money!** Float/double can have precision issues.

### Boolean Columns

```php
$table->boolean('published');         // BOOLEAN (TINYINT(1))
```

### Date/Time Columns

```php
$table->date('birth_date');           // DATE
$table->time('alarm_time');           // TIME
$table->dateTime('published_at');     // DATETIME
$table->timestamp('created_at');      // TIMESTAMP

$table->timestamps();                 // created_at & updated_at
$table->softDeletes();                // deleted_at (for soft deletes)
```

**`timestamps()` creates:**
```sql
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL
```

### JSON Columns

```php
$table->json('options');              // JSON
```

### Enum Columns

```php
$table->enum('status', ['draft', 'published', 'archived']);
```

### Foreign Keys

```php
$table->foreignId('user_id')->constrained();
```

**Expands to:**
```sql
user_id BIGINT UNSIGNED,
FOREIGN KEY (user_id) REFERENCES users(id)
```

---

## Column Modifiers

Add constraints and defaults:

### Nullable

```php
$table->string('middle_name')->nullable();
```

**Without `nullable()`, column is `NOT NULL` by default.**

### Default Values

```php
$table->boolean('published')->default(false);
$table->integer('views')->default(0);
$table->string('status')->default('draft');
$table->timestamp('published_at')->useCurrent();  // NOW()
```

### Unsigned

```php
$table->integer('age')->unsigned();
```

### Unique

```php
$table->string('email')->unique();
$table->string('slug')->unique();
```

**Pure SQL:**
```sql
email VARCHAR(255) UNIQUE
```

### Index

```php
$table->string('email')->index();
$table->index('email');  // Alternative
```

**Speeds up queries on that column.**

### Comments

```php
$table->string('title')->comment('The post title');
```

### After (Column Position)

```php
$table->string('middle_name')->after('first_name');
```

### Combining Modifiers

```php
$table->string('email')->unique()->nullable()->comment('User email address');
$table->integer('views')->unsigned()->default(0);
```

---

## Complete Example: Posts Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Basic columns
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');

            // Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // Metadata
            $table->integer('views')->unsigned()->default(0);
            $table->boolean('featured')->default(false);

            // Foreign key
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamps();        // created_at, updated_at
            $table->softDeletes();       // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

**Pure SQL equivalent:**
```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    views INT UNSIGNED DEFAULT 0,
    featured TINYINT(1) DEFAULT 0,
    user_id BIGINT UNSIGNED NOT NULL,
    meta_title VARCHAR(255) NULL,
    meta_description TEXT NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Laravel's version is more readable!**

---

## Running Migrations

### Run All Pending Migrations

```bash
php artisan migrate
```

**First time:**
```
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (0.5ms)
Migrating: 2024_01_01_000000_create_posts_table
Migrated:  2024_01_01_000000_create_posts_table (0.3ms)
```

**Already up to date:**
```
Nothing to migrate.
```

### Check Migration Status

```bash
php artisan migrate:status
```

Output:
```
Migration name ................................. Batch / Status
2014_10_12_000000_create_users_table ........... [1] Ran
2024_01_01_000000_create_posts_table ........... [1] Ran
```

### Rollback Last Batch

```bash
php artisan migrate:rollback
```

**Runs `down()` method** of last batch.

**Rollback last 3 batches:**
```bash
php artisan migrate:rollback --step=3
```

### Reset All Migrations

```bash
php artisan migrate:reset
```

**Rolls back ALL migrations** (empties database).

### Fresh Migration

**Drop all tables and re-run:**
```bash
php artisan migrate:fresh
```

**With seeders:**
```bash
php artisan migrate:fresh --seed
```

**⚠️ Warning:** Deletes all data! Only use in development.

### Refresh Migration

**Rollback all and re-run:**
```bash
php artisan migrate:refresh
```

Equivalent to:
```bash
php artisan migrate:reset
php artisan migrate
```

---

## Modifying Tables

### Adding Columns

```bash
php artisan make:migration add_subtitle_to_posts_table
```

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('subtitle')->nullable()->after('title');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('subtitle');
    });
}
```

**Note:** `Schema::table` (not `create`!)

### Removing Columns

```bash
php artisan make:migration remove_draft_from_posts_table
```

```php
public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->dropColumn('draft');
    });
}

public function down(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->boolean('draft')->default(false);
    });
}
```

**Drop multiple columns:**
```php
$table->dropColumn(['draft', 'subtitle', 'excerpt']);
```

### Modifying Columns

**Change column type or attributes:**

```php
use Doctrine\DBAL\Types\Type;

public function up(): void
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('title', 500)->change();  // Increase length
        $table->text('content')->nullable()->change();  // Make nullable
    });
}
```

**Rename column:**
```php
$table->renameColumn('draft', 'is_draft');
```

---

## Foreign Keys

### Creating Foreign Keys

**Simple:**
```php
$table->foreignId('user_id')->constrained();
```

**Expands to:**
- Column: `user_id BIGINT UNSIGNED`
- Foreign key to `users.id`

**Custom table name:**
```php
$table->foreignId('author_id')->constrained('users');
```

**Full control:**
```php
$table->unsignedBigInteger('user_id');
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade')
    ->onUpdate('cascade');
```

### OnDelete Actions

**What happens when parent is deleted:**

```php
->onDelete('cascade')     // Delete child rows
->onDelete('set null')    // Set foreign key to NULL
->onDelete('restrict')    // Prevent deletion if children exist
->onDelete('no action')   // Same as restrict
```

**Example:**
```php
// When user is deleted, delete their posts
$table->foreignId('user_id')
    ->constrained()
    ->onDelete('cascade');

// When category is deleted, set posts.category_id to NULL
$table->foreignId('category_id')
    ->nullable()
    ->constrained()
    ->onDelete('set null');
```

### Dropping Foreign Keys

```php
$table->dropForeign(['user_id']);  // Pass column name
```

**Laravel auto-generates foreign key name:**
- Pattern: `{table}_{column}_foreign`
- Example: `posts_user_id_foreign`

---

## Indexes

**Speed up queries** on specific columns.

### Creating Indexes

```php
// Single column
$table->string('email')->index();
$table->index('email');  // Alternative

// Multiple columns (compound index)
$table->index(['user_id', 'created_at']);

// Unique index
$table->string('email')->unique();
$table->unique('email');  // Alternative

// Named index
$table->index('email', 'users_email_index');
```

### Dropping Indexes

```php
$table->dropIndex(['email']);             // Drop index
$table->dropUnique(['email']);            // Drop unique
$table->dropIndex('users_email_index');   // Drop by name
```

### Full-Text Index (MySQL)

```php
$table->fullText('content');
```

---

## Raw SQL in Migrations

Sometimes you need raw SQL:

```php
use Illuminate\Support\Facades\DB;

public function up(): void
{
    DB::statement('ALTER TABLE posts ADD FULLTEXT INDEX posts_content_fulltext (content)');
}
```

**Use sparingly!** Database-specific SQL reduces portability.

---

## Checking Table/Column Existence

```php
if (Schema::hasTable('users')) {
    // Table exists
}

if (Schema::hasColumn('users', 'email')) {
    // Column exists
}
```

**Useful in migrations:**
```php
public function up(): void
{
    if (!Schema::hasColumn('posts', 'subtitle')) {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('subtitle')->nullable();
        });
    }
}
```

---

## Database Seeders

**Seeders** populate tables with dummy data (we'll cover in detail in Module 15).

### Quick Preview

```bash
php artisan make:seeder PostSeeder
```

```php
use App\Models\Post;

public function run(): void
{
    Post::create([
        'title' => 'First Post',
        'content' => 'This is my first post.',
    ]);
}
```

**Run seeder:**
```bash
php artisan db:seed --class=PostSeeder
```

---

## Migration Best Practices

### 1. Never Edit Existing Migrations

**Bad:**
```php
// Edit migration that's already run
$table->string('title', 500);  // Changed from 255
```

**Good:**
```bash
# Create new migration
php artisan make:migration increase_title_length_in_posts_table
```

**Why?** Other developers and production already ran the old migration!

### 2. Always Make Migrations Reversible

**Bad:**
```php
public function down(): void
{
    // Empty - can't rollback!
}
```

**Good:**
```php
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        // ...
    });
}

public function down(): void
{
    Schema::dropIfExists('posts');
}
```

### 3. Use Descriptive Names

**Bad:**
```bash
php artisan make:migration update_posts
```

**Good:**
```bash
php artisan make:migration add_published_at_to_posts_table
```

### 4. One Change Per Migration

**Bad:**
```php
public function up(): void
{
    // Create posts table
    Schema::create('posts', ...);

    // Also create comments table
    Schema::create('comments', ...);

    // And modify users table
    Schema::table('users', ...);
}
```

**Good:**
```bash
php artisan make:migration create_posts_table
php artisan make:migration create_comments_table
php artisan make:migration add_avatar_to_users_table
```

### 5. Use Foreign Keys

**Bad:**
```php
$table->unsignedBigInteger('user_id');  // No foreign key constraint
```

**Good:**
```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
```

**Benefits:**
- Database enforces referential integrity
- Prevents orphaned records
- Clear relationships

### 6. Add Indexes for Queries

**If you query by a column frequently, add an index:**

```php
// Often search posts by status
$table->enum('status', ['draft', 'published'])->index();

// Often search by date
$table->timestamp('published_at')->index();

// Often search by foreign key (already indexed by foreignId)
$table->foreignId('user_id')->constrained();
```

### 7. Test Rollbacks

After creating a migration:

```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate
```

**Make sure both `up()` and `down()` work!**

---

## Common Migration Patterns

### User Table

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();  // For "Remember Me"
    $table->timestamps();
});
```

### Pivot Table (Many-to-Many)

```php
Schema::create('post_tag', function (Blueprint $table) {
    $table->foreignId('post_id')->constrained()->onDelete('cascade');
    $table->foreignId('tag_id')->constrained()->onDelete('cascade');

    $table->primary(['post_id', 'tag_id']);  // Composite primary key
});
```

### Polymorphic Relationship

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->text('content');
    $table->morphs('commentable');  // commentable_id, commentable_type
    $table->timestamps();
});
```

**Expands to:**
```php
$table->unsignedBigInteger('commentable_id');
$table->string('commentable_type');
$table->index(['commentable_id', 'commentable_type']);
```

---

## Summary

**What You Learned:**
- What migrations are and why they're important
- How to configure database connection
- Creating migrations with Artisan
- Column types and modifiers
- Running and rolling back migrations
- Adding/removing/modifying columns
- Foreign keys and relationships
- Indexes for performance
- Migration best practices

**Key Takeaways:**
1. **Migrations are version control** for database structure
2. **Always commit migrations** to Git
3. **`php artisan migrate`** runs pending migrations
4. **`up()` creates, `down()` reverses** - always reversible!
5. **Never edit existing migrations** - create new ones
6. **Use `foreignId()->constrained()`** for relationships
7. **Add indexes** to frequently queried columns

**Pure PHP vs Laravel:**
- Pure PHP: Manual SQL, no history, hard to sync
- Laravel: Version controlled, automatic, team-friendly

**Next Lesson:** We'll learn about Models and Eloquent ORM - how to interact with these database tables!

---

## Practice Exercise

**Create a blog database structure:**

```bash
# 1. Posts table
php artisan make:migration create_posts_table

# 2. Categories table
php artisan make:migration create_categories_table

# 3. Add category relationship to posts
php artisan make:migration add_category_id_to_posts_table

# 4. Tags table
php artisan make:migration create_tags_table

# 5. Pivot table for posts-tags (many-to-many)
php artisan make:migration create_post_tag_table
```

**Define structure:**
- Posts: id, title, slug, content, status, views, user_id, category_id, published_at, timestamps
- Categories: id, name, slug, timestamps
- Tags: id, name, slug, timestamps
- Post_Tag: post_id, tag_id

**Then run:**
```bash
php artisan migrate
```

---

## Quick Quiz

**1. What command creates a migration?**
```bash
php artisan make:migration create_posts_table
```

**2. What does `timestamps()` create?**
- `created_at` and `updated_at` columns

**3. How do you make a column nullable?**
```php
$table->string('subtitle')->nullable();
```

**4. How do you create a foreign key?**
```php
$table->foreignId('user_id')->constrained();
```

**5. What's the difference between `migrate:rollback` and `migrate:fresh`?**
- `rollback`: Undo last batch
- `fresh`: Drop all tables and re-run all migrations

**6. Why use migrations instead of manual SQL?**
- Version control, team collaboration, rollback capability, automation

**7. What goes in `down()` method?**
- The reverse of `up()` - usually `dropIfExists()` or `dropColumn()`

---

**Next**: [Lesson 08 - Models Introduction →](08-models-introduction.md)
