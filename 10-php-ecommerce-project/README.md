# Module 10 - E-Commerce Project (Pure PHP)

**Duration**: 3-4 weeks
**Prerequisites**: Modules 04-09 (All PHP modules)

---

## Project Overview

This is your **capstone project** for Pure PHP. You'll build a complete e-commerce website from scratch using everything you've learned:

- ✅ PHP OOP (Module 05)
- ✅ MySQL & PDO (Module 06)
- ✅ Sessions & Authentication (Module 07)
- ✅ Security & Validation (Module 08)
- ✅ APIs (Module 09 - optional admin API)

**No frameworks. No shortcuts. Pure PHP.**

This is the project that will make you truly understand web development!

---

## Why This Project?

**E-commerce sites have EVERYTHING:**
- User authentication
- Database relationships
- Shopping cart (sessions)
- Payment processing
- Admin panel
- Security concerns
- File uploads
- Email notifications
- Reports and analytics

After building this, you'll be ready for **any** web project!

---

## Project Requirements

### Core Features (Must Have)

#### 1. User Management
- User registration with validation
- Login/logout system
- Password reset functionality
- User profile page
- Order history

#### 2. Product Catalog
- Products with categories
- Product details page
- Product images (secure upload)
- Search functionality
- Filter by category
- Sort by price, name, date

#### 3. Shopping Cart
- Add to cart (using sessions)
- Update quantities
- Remove items
- Cart persists across pages
- Calculate subtotal, tax, total
- Empty cart option

#### 4. Checkout Process
- Guest checkout OR login
- Shipping address form
- Payment method selection
- Order review
- Place order button
- Order confirmation page
- Order confirmation email (simulated)

#### 5. Order Management
- Orders stored in database
- Order status (Pending, Processing, Shipped, Delivered)
- View order details
- Users can see their orders
- Admin can update status

#### 6. Admin Panel
- Admin login (separate from users)
- Dashboard with stats
- Manage products (CRUD)
- Manage categories (CRUD)
- Manage orders (view, update status)
- Manage users (view, block)
- Upload product images

#### 7. Security
- All forms have CSRF protection
- XSS prevention (htmlspecialchars everywhere)
- SQL injection prevention (prepared statements)
- Secure file uploads
- Password hashing
- Role-based access (admin vs user)
- Input validation

### Bonus Features (Optional)

- Product reviews and ratings
- Wishlist functionality
- Coupon/discount codes
- Inventory management
- Sales reports
- Image gallery per product
- Related products
- Recently viewed products
- Email notifications (real with PHPMailer)
- Export orders to CSV
- Simple API for mobile app

---

## Database Schema

```sql
-- Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    is_admin BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Orders
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Order Items
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Password Reset Tokens
CREATE TABLE password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    token VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## Project Structure

```
ecommerce/
├── config/
│   ├── database.php       (DB connection)
│   ├── session.php        (Session config)
│   └── constants.php      (Site constants)
│
├── classes/                (OOP approach!)
│   ├── Database.php
│   ├── User.php
│   ├── Product.php
│   ├── Category.php
│   ├── Cart.php
│   ├── Order.php
│   └── Auth.php
│
├── includes/
│   ├── functions.php      (Helper functions)
│   ├── header.php
│   ├── footer.php
│   └── nav.php
│
├── middleware/
│   ├── auth.php           (Require login)
│   ├── guest.php          (Require guest)
│   └── admin.php          (Require admin)
│
├── public/                (Public-facing pages)
│   ├── index.php          (Home page)
│   ├── products.php       (Product listing)
│   ├── product.php        (Single product)
│   ├── cart.php           (Shopping cart)
│   ├── checkout.php       (Checkout process)
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   ├── profile.php
│   └── orders.php         (User order history)
│
├── admin/                 (Admin panel)
│   ├── index.php          (Dashboard)
│   ├── products.php       (Manage products)
│   ├── orders.php         (Manage orders)
│   ├── users.php          (Manage users)
│   └── categories.php     (Manage categories)
│
├── uploads/               (Product images - outside web root in production!)
│   └── products/
│
├── assets/
│   ├── css/
│   │   └── style.css      (Tailwind or custom)
│   └── js/
│       └── main.js
│
└── database/
    └── schema.sql         (Database structure)
```

---

## Development Phases

### Week 1: Foundation
- [ ] Set up database schema
- [ ] Create OOP classes (User, Product, etc.)
- [ ] Build authentication system
- [ ] Create basic page templates

### Week 2: Product Catalog
- [ ] Product listing page with pagination
- [ ] Product detail page
- [ ] Category filtering
- [ ] Search functionality
- [ ] Image upload for products

### Week 3: Shopping & Checkout
- [ ] Shopping cart functionality
- [ ] Add/update/remove items
- [ ] Checkout flow
- [ ] Order placement
- [ ] Order confirmation

### Week 4: Admin & Polish
- [ ] Admin dashboard
- [ ] Product management (CRUD)
- [ ] Order management
- [ ] Security audit
- [ ] Testing & bug fixes
- [ ] UI/UX improvements

---

## Key Challenges You'll Solve

### Challenge 1: Shopping Cart
How do you store cart items when user isn't logged in?
**Answer**: Sessions! Cart data in `$_SESSION['cart']`

### Challenge 2: Inventory Management
What happens if two users try to buy the last item?
**Answer**: Check stock before order placement, use database transactions

### Challenge 3: Security
How to prevent admin pages from being accessed by regular users?
**Answer**: Middleware that checks `$_SESSION['is_admin']`

### Challenge 4: Order Total Calculation
Where to calculate total? Client or server?
**Answer**: Server! Never trust client-side calculations

### Challenge 5: Image Upload
How to prevent malicious file uploads?
**Answer**: Validate type, rename file, store outside web root

---

## Coding Standards

- ✅ Use OOP (classes for User, Product, Cart, etc.)
- ✅ Prepared statements for ALL database queries
- ✅ `htmlspecialchars()` for ALL user-generated output
- ✅ CSRF tokens on ALL forms
- ✅ Password hashing with `password_hash()`
- ✅ Validate ALL user input
- ✅ Meaningful variable and function names
- ✅ Comments for complex logic
- ✅ DRY (Don't Repeat Yourself) principle

---

## Testing Checklist

### Functionality
- [ ] Users can register and login
- [ ] Products display correctly
- [ ] Adding to cart works
- [ ] Cart quantities update
- [ ] Checkout creates order
- [ ] Admin can manage products
- [ ] Admin can update order status
- [ ] Search works
- [ ] Filtering works

### Security
- [ ] Can't access admin without admin role
- [ ] Can't SQL inject anywhere
- [ ] Can't XSS anywhere
- [ ] Can't upload PHP files as images
- [ ] Can't bypass CSRF protection
- [ ] Can't see other users' orders

### Edge Cases
- [ ] Empty cart checkout blocked
- [ ] Out-of-stock items can't be ordered
- [ ] Invalid product IDs handled
- [ ] Negative quantities prevented
- [ ] SQL errors don't expose data

---

## What You'll Learn

By completing this project, you'll have:

1. **Built a real application** - Not just tutorials, a working e-commerce site
2. **Understood every line** - You wrote it all, you know how it works
3. **Solved real problems** - Cart management, security, file uploads
4. **Practiced OOP** - Used classes extensively
5. **Mastered databases** - Complex queries, relationships, transactions
6. **Implemented security** - XSS, CSRF, SQL injection prevention
7. **Managed sessions** - Cart, authentication, remember me
8. **Created an admin panel** - Different user roles and permissions

**Most importantly**: When you learn Laravel next, you'll appreciate how much it simplifies!

---

## Comparison: Now vs Later (Laravel)

**What you build now (Pure PHP):**
- 3-4 weeks of intense coding
- ~3000-5000 lines of code
- Manual routing, sessions, validation
- Build everything from scratch

**What Laravel does (Module 17):**
- Same features in 1-2 weeks
- ~500-1000 lines of code
- Built-in auth, validation, ORM
- Focus on business logic, not plumbing

**But**: You'll understand WHAT Laravel is doing for you!

---

## Resources

- Your previous modules (04-09)!
- [PHP Manual](https://www.php.net/manual/en/)
- [Tailwind CSS](https://tailwindcss.com/docs) for styling
- [Stripe Payment Docs](https://stripe.com/docs/payments) (if adding real payments)

---

## Submission & Review

When done, you should be able to:
1. Demo the site (user flow + admin flow)
2. Explain the code structure
3. Walk through the database schema
4. Show security measures implemented
5. Discuss challenges faced and solutions

---

## Next Phase

**Congratulations!** After this project, you'll move to:
**Module 11 - JavaScript Basics**

Then eventually rebuild parts of this in Laravel and see the difference!

---

**Ready to build something real? Let's do this!** 🚀
