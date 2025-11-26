# Exercise 5.1 - Product Class

## Objective

Create your first PHP class to represent a product in an e-commerce system.

## Duration

1-2 hours

## Task

Create a `Product` class with properties and methods.

## Requirements

- [ ] Create a `Product.php` file with a `Product` class
- [ ] Add properties: `name`, `price`, `stock`
- [ ] Add a constructor to initialize the properties
- [ ] Add a method `display()` that shows product information
- [ ] Add a method `isAvailable()` that returns true if stock > 0
- [ ] Add a method `buy($quantity)` that reduces stock
- [ ] Use proper visibility (public, private, protected)

## Starter File

Work in `Product.php` - see starter code there.

## Expected Output

```
php Product.php

Product: Laptop
Price: $999.99
Stock: 10
Available: Yes

After buying 3 units:
Stock: 7
```

## Checklist

- [ ] Class defined with proper syntax
- [ ] Constructor works correctly
- [ ] All methods implemented
- [ ] Stock decreases when buying
- [ ] isAvailable() returns correct boolean
- [ ] Code is clean and readable

## Tips

- Use `$this->` to access properties within the class
- Constructor is `__construct()`
- Public methods can be called from outside the class
