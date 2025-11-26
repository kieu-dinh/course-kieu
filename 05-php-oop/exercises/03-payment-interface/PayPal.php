<?php

require_once 'PaymentInterface.php';

// TODO: Create a PayPal class that implements PaymentInterface
// - Properties: $email, $password (all private)
// - Create a constructor to initialize these properties
// - Implement processPayment($amount) method
// - Implement refund($amount) method
// - Implement getPaymentMethod() method
// - Add validation: valid email format, amount > 0

class PayPal implements PaymentInterface
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

    // Helper method to validate email
    private function isValidEmail()
    {
        // TODO: Return true if email is valid
        return true;
    }
}


// Test your class (don't modify this part)
require_once 'CreditCard.php';
require_once 'PayPal.php';

echo "\n=== PayPal Payment ===\n";
$paypal = new PayPal("user@example.com", "password123");

echo "Processing payment of \$50.00\n";
$paypal->processPayment(50.00);
echo "Method: " . $paypal->getPaymentMethod() . "\n";
echo "Email: user@example.com\n";
echo "Status: Payment approved!\n\n";

echo "Processing refund of \$25.00\n";
$paypal->refund(25.00);
echo "Refund approved!\n";
