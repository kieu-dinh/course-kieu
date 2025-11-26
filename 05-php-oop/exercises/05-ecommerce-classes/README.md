# Exercise 5.5 - E-Commerce Classes

## Objective

Build a complete e-commerce system with Product, Cart, and Order classes that interact with each other.

## Duration

2-2.5 hours

## Task

Create interconnected classes that form the backbone of an e-commerce application.

## Requirements

- [ ] Create a `Product.php` class with properties: `id`, `name`, `price`, `description`, `stock`
- [ ] Product methods: `getInfo()`, `reduceStock()`, `addStock()`, getter methods
- [ ] Create a `Cart.php` class to hold shopping cart items
- [ ] Cart properties: `items` (array), `customerId`
- [ ] Cart methods: `addItem()`, `removeItem()`, `getItems()`, `getTotalPrice()`, `getItemCount()`, `clear()`
- [ ] Cart items should track product and quantity
- [ ] Create an `Order.php` class to represent completed orders
- [ ] Order properties: `id`, `customerId`, `items`, `totalPrice`, `orderDate`, `status`
- [ ] Order methods: `getOrderInfo()`, `changeStatus()`, `getItems()`, `getTotal()`
- [ ] Implement order creation from cart
- [ ] Stock management: prevent ordering more than available

## Starter Files

Work in `Product.php`, `Cart.php`, and `Order.php` - see starter code there.

## Expected Output

```
php Cart.php

=== E-Commerce System ===

Adding products to cart:
- Laptop ($999.99) x 1 = $999.99
- Mouse ($29.99) x 2 = $59.98

Cart Total: $1,059.97
Items in cart: 2

Creating order from cart:
Order #1001 created successfully!
Status: Pending

Order Details:
Order ID: 1001
Customer ID: 101
Items: 2
Total: $1,059.97
```

## Checklist

- [ ] Product class created with all properties
- [ ] Product stock management working correctly
- [ ] Cart class created and items management working
- [ ] addItem() adds products or increases quantity
- [ ] removeItem() removes products from cart
- [ ] getTotalPrice() calculates correct total
- [ ] Order class created with all properties
- [ ] Orders can be created from cart
- [ ] Stock is reduced when order is placed
- [ ] Order status can be changed
- [ ] Proper error handling for invalid operations

## Tips

- Cart items should be stored with product reference and quantity
- When adding same product twice, increase quantity instead of duplicating
- Calculate order total from items and quantities
- Order status might be: Pending, Processing, Shipped, Delivered, Cancelled
- Use array_key_exists() or isset() to check if product already in cart
- Consider using product ID as cart item key for easy lookup
