# Module 05 - PHP Object-Oriented Programming

**Duration**: 2-3 weeks
**Prerequisites**: Module 04 - PHP Basics

---

## Learning Objectives

By the end of this module, you will be able to:
- Understand what Object-Oriented Programming (OOP) is and why it matters
- Create classes and objects in PHP
- Use properties and methods effectively
- Implement inheritance to reuse code
- Work with interfaces and abstract classes
- Understand and use traits
- Apply encapsulation principles (public, private, protected)
- Use namespaces to organize code
- Build real applications using OOP

---

## Why Object-Oriented Programming?

So far, you've learned procedural programming - writing functions that process data. But as applications grow, this becomes hard to manage.

**OOP helps you:**
- **Organize code** - Group related data and functions together
- **Reuse code** - Inherit from existing classes instead of rewriting
- **Maintain code** - Changes in one place don't break everything
- **Model real-world** - Think in terms of "things" (objects) and what they do

**Example**: Instead of scattered functions for a user:
```php
function createUser($name, $email) { }
function validateUser($user) { }
function saveUser($user) { }
```

With OOP:
```php
class User {
    public function create() { }
    public function validate() { }
    public function save() { }
}
```

Everything related to a User is in one place!

---

## What You'll Learn

### 1. Classes and Objects
- What is a class? (Blueprint)
- What is an object? (Instance of the blueprint)
- Properties (data)
- Methods (functions)
- Constructor (`__construct`)

### 2. Encapsulation
- Public, private, protected visibility
- Getters and setters
- Why hide data?

### 3. Inheritance
- Extending classes
- Parent and child classes
- Overriding methods
- `parent::` keyword

### 4. Interfaces
- Defining contracts
- Implementing interfaces
- When to use interfaces

### 5. Abstract Classes
- What are abstract classes?
- Abstract methods
- When to use them vs interfaces

### 6. Traits
- Code reuse without inheritance
- Using multiple traits
- Trait conflicts

### 7. Namespaces
- Organizing code into namespaces
- Autoloading with Composer
- Use statements

### 8. Magic Methods
- `__construct`, `__toString`, `__get`, `__set`
- When to use them

---

## Lessons

1. **01-classes-objects.md** - Your first class and object
2. **02-properties-methods.md** - Working with data and behavior
3. **03-constructor.md** - Initializing objects properly
4. **04-encapsulation.md** - Public, private, protected
5. **05-inheritance.md** - Reusing code through inheritance
6. **06-interfaces.md** - Defining contracts
7. **07-abstract-classes.md** - Partial implementations
8. **08-traits.md** - Mixins and code reuse
9. **09-namespaces.md** - Organizing larger applications
10. **10-magic-methods.md** - Special PHP methods
11. **11-oop-best-practices.md** - SOLID principles introduction

---

## Exercises

| ID | Exercise | Description | Duration |
|----|----------|-------------|----------|
| 5.1 | Product Class | Create a Product with properties and methods | 1-2 hours |
| 5.2 | Inheritance - Vehicles | Create Vehicle parent, Car/Bike children | 2 hours |
| 5.3 | Interface Implementation | PaymentInterface with multiple implementations | 2 hours |
| 5.4 | Library System | Build a complete OOP library management | 4-6 hours |
| 5.5 | E-Commerce Classes | Products, Cart, Order with relationships | 4-6 hours |

---

## Project

**Mini Library System**
Build a library management system using OOP:
- Book class
- Member class
- Library class
- Borrowing system
- Late fees calculation

This will prepare you for the database module where you'll persist this data!

---

## Resources

- [PHP OOP Documentation](https://www.php.net/manual/en/language.oop5.php)
- [PHP The Right Way - OOP](https://phptherightway.com/#object-oriented-programming)

---

## Next Module

**Module 06 - PHP & MySQL**: You'll learn to save your objects to a database using PDO!
