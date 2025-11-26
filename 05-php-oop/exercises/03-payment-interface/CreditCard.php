<?php

require_once 'PaymentInterface.php';

// TODO: Create a CreditCard class that implements PaymentInterface
// - Properties: $cardNumber, $cardHolder, $expiryDate, $cvv (all private)
// - Create a constructor to initialize these properties
// - Implement processPayment($amount) method
// - Implement refund($amount) method
// - Implement getPaymentMethod() method
// - Add validation: card number format, amount > 0

class CreditCard implements PaymentInterface
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Implement processPayment() method
    public function processPayment($amount)
    {

    }

    // TODO: Implement refund() method
    public function refund($amount)
    {

    }

    // TODO: Implement getPaymentMethod() method
    public function getPaymentMethod()
    {

    }

    // Helper method to mask card number
    private function maskCardNumber()
    {
        // TODO: Return masked card number like ****1234
        return "";
    }
}


// Test your class (don't modify this part)
echo "=== Credit Card Payment ===\n";
$creditCard = new CreditCard("4532123412341234", "John Doe", "12/25", "123");

echo "Processing payment of \$99.99\n";
$creditCard->processPayment(99.99);
echo "Method: " . $creditCard->getPaymentMethod() . "\n";
echo "Card: " . "4532****1234\n";
echo "Status: Payment approved!\n\n";

echo "Processing refund of \$50.00\n";
$creditCard->refund(50.00);
echo "Refund approved!\n";
