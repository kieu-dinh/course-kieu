# Exercise 6.5 - Product Catalog with Categories

## Objective

Build a complete product catalog system with categories, filtering, searching, and inventory management.

## Duration

2.5-3 hours

## Task

Create a full-featured product management system with advanced queries and relationships.

## Requirements

- [ ] Create `categories` table: id, name, description, slug
- [ ] Create `products` table: id, name, description, price, category_id, stock, rating, created_at
- [ ] Create `Category.php` class for category management
- [ ] Create `Product.php` class for product management
- [ ] Implement product search by name/description
- [ ] Implement filtering by category
- [ ] Implement price range filtering
- [ ] Implement stock status checking
- [ ] Implement rating system
- [ ] Implement inventory management (decrease/increase stock)
- [ ] Use proper indexing for search performance

## Starter Files

Work in `Database.php`, `Category.php`, `Product.php`, and `test-catalog.php`

## SQL Table Definitions

```sql
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description LONGTEXT,
    price DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    stock INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    FULLTEXT INDEX idx_search (name, description)
);
```

## Expected Output

```
php test-catalog.php

=== Product Catalog System ===

Categories created...

Products created and added to catalog...

All Products:
1. Laptop - $999.99 (Electronics) - In Stock
2. Mouse - $29.99 (Electronics) - In Stock
3. Running Shoes - $89.99 (Sports) - In Stock
4. Basketball - $39.99 (Sports) - In Stock

Products in Electronics:
- Laptop (Rating: 4.50)
- Mouse (Rating: 4.20)

Products by Price ($30-$100):
- Mouse - $29.99
- Running Shoes - $89.99

Search Results for "Laptop":
- Laptop - $999.99

Stock Update:
Stock updated to: 8
```

## Checklist

- [ ] Categories and Products tables created
- [ ] Category management working
- [ ] Product CRUD operations implemented
- [ ] Search functionality works
- [ ] Category filtering works
- [ ] Price range filtering works
- [ ] Stock management (add/reduce) works
- [ ] Rating system implemented
- [ ] All queries use prepared statements
- [ ] Proper indexing for search performance
- [ ] Error handling for invalid operations
- [ ] Code runs without errors

## Tips

- Use FULLTEXT INDEX for efficient searching
- Use indexes on commonly filtered columns
- DECIMAL(10,2) is appropriate for currency
- Use BETWEEN for price range queries
- Check stock before selling
- Rating should be 0-5 scale
- Use JOINs to get category names with products
- Test search with various keywords
