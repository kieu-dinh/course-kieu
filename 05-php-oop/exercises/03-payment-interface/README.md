# Exercise 5.3 - Payment Interface

## Objective

Learn about interfaces and polymorphism by creating a PaymentInterface and multiple payment implementation classes.

## Duration

1.5-2 hours

## Task

Create a payment system that supports multiple payment methods through a common interface.

## Requirements

- [ ] Create a `PaymentInterface.php` that defines payment contract
- [ ] Interface should have methods: `processPayment($amount)`, `refund($amount)`, `getPaymentMethod()`
- [ ] Create a `CreditCard.php` class implementing PaymentInterface
- [ ] CreditCard properties: `cardNumber`, `cardHolder`, `expiryDate`, `cvv`
- [ ] Create a `PayPal.php` class implementing PaymentInterface
- [ ] PayPal properties: `email`, `password`
- [ ] Both classes must implement all interface methods
- [ ] Implement payment processing with validation
- [ ] Track processed transactions

## Starter Files

Work in `PaymentInterface.php`, `CreditCard.php`, and `PayPal.php` - see starter code there.

## Expected Output

```
php CreditCard.php

=== Credit Card Payment ===
Processing payment of $99.99
Method: Credit Card
Card: 4532****1234
Status: Payment approved!

Processing refund of $50.00
Refund approved!

=== PayPal Payment ===
Processing payment of $50.00
Method: PayPal
Email: user@example.com
Status: Payment approved!

Processing refund of $25.00
Refund approved!
```

## Checklist

- [ ] PaymentInterface created with required methods
- [ ] CreditCard class implements PaymentInterface
- [ ] PayPal class implements PaymentInterface
- [ ] Both classes implement all required methods
- [ ] processPayment() validates and processes payments
- [ ] refund() processes refunds correctly
- [ ] getPaymentMethod() returns correct payment type
- [ ] Error handling for invalid amounts
- [ ] Code runs without errors

## Tips

- Use `interface` keyword to define contracts
- Use `implements` keyword to implement interfaces
- All interface methods must be implemented in implementing classes
- Interfaces define the contract but not the implementation
- Polymorphism: same method name, different behaviors
