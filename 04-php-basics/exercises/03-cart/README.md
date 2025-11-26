# Exercise 03 - Shopping Cart

## Objective

Practice arrays and loops with a shopping cart system.

---

## Task

Create `cart.php` with shopping cart functionality using arrays.

### Requirements

1. Cart is an array of products
2. Each product has: name, price, quantity
3. Functions to manipulate cart

---

## Starter Code

```php
<?php

$cart = [];

function addToCart(array &$cart, string $name, float $price, int $quantity = 1): void {
    // Check if product already exists
    // If yes: increase quantity
    // If no: add new product
}

function removeFromCart(array &$cart, string $name): bool {
    // Remove product by name
    // Return true if removed, false if not found
}

function updateQuantity(array &$cart, string $name, int $quantity): bool {
    // Update quantity of product
    // If quantity is 0 or less, remove product
    // Return true if updated, false if not found
}

function getCartTotal(array $cart): float {
    // Calculate total: sum of (price * quantity) for all products
}

function getItemCount(array $cart): int {
    // Return total number of items (sum of all quantities)
}

function displayCart(array $cart): void {
    if (empty($cart)) {
        echo "Cart is empty\n";
        return;
    }

    echo "=== Shopping Cart ===\n";
    foreach ($cart as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        echo "{$item['name']} x{$item['quantity']} @ {$item['price']}€ = {$subtotal}€\n";
    }
    echo "---------------------\n";
    echo "Total: " . getCartTotal($cart) . "€\n";
    echo "Items: " . getItemCount($cart) . "\n";
}

// Test the cart
echo "Adding items...\n";
addToCart($cart, "Apple", 1.50, 3);
addToCart($cart, "Bread", 2.00, 1);
addToCart($cart, "Milk", 1.20, 2);
displayCart($cart);

echo "\nAdding more apples...\n";
addToCart($cart, "Apple", 1.50, 2);  // Should increase quantity to 5
displayCart($cart);

echo "\nUpdating milk quantity...\n";
updateQuantity($cart, "Milk", 4);
displayCart($cart);

echo "\nRemoving bread...\n";
removeFromCart($cart, "Bread");
displayCart($cart);
```

---

## Expected Output

```
Adding items...
=== Shopping Cart ===
Apple x3 @ 1.5€ = 4.5€
Bread x1 @ 2€ = 2€
Milk x2 @ 1.2€ = 2.4€
---------------------
Total: 8.9€
Items: 6

Adding more apples...
=== Shopping Cart ===
Apple x5 @ 1.5€ = 7.5€
Bread x1 @ 2€ = 2€
Milk x2 @ 1.2€ = 2.4€
---------------------
Total: 11.9€
Items: 8

Updating milk quantity...
=== Shopping Cart ===
Apple x5 @ 1.5€ = 7.5€
Bread x1 @ 2€ = 2€
Milk x4 @ 1.2€ = 4.8€
---------------------
Total: 14.3€
Items: 10

Removing bread...
=== Shopping Cart ===
Apple x5 @ 1.5€ = 7.5€
Milk x4 @ 1.2€ = 4.8€
---------------------
Total: 12.3€
Items: 9
```

---

## Hints

```php
// Find product index by name
function findProductIndex(array $cart, string $name): ?int {
    foreach ($cart as $index => $item) {
        if ($item['name'] === $name) {
            return $index;
        }
    }
    return null;
}
```

---

## Bonus Challenges

1. Add `applyDiscount(array &$cart, float $percentage): void`
2. Add `clearCart(array &$cart): void`
3. Add `getMostExpensiveItem(array $cart): ?array`

---

## Checklist

- [ ] Can add new products
- [ ] Adding existing product increases quantity
- [ ] Can remove products
- [ ] Can update quantities
- [ ] Total calculates correctly
- [ ] Item count is correct
