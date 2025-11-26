# Lesson 6: PHPUnit Introduction

**Duration**: 3-4 hours
**Prerequisites**: Module 17 - Livewire
**Objective**: Learn the fundamentals of automated testing with PHPUnit and Laravel's testing tools

---

## Introduction

Imagine building a house without ever checking if the walls are straight, the doors close properly, or the electricity works. You'd wait until move-in day to discover problems. **That's coding without tests.**

Automated testing means writing code that tests your code. It sounds like extra work, but it:
- **Catches bugs early** - Before users find them
- **Saves time** - Faster than manual testing
- **Enables confidence** - Refactor without fear
- **Documents behavior** - Tests show how code should work
- **Prevents regressions** - Old bugs don't come back

Professional developers write tests. It's not optional - it's essential.

---

## What is PHPUnit?

**PHPUnit** is the standard testing framework for PHP. Laravel includes it by default and adds helpful testing tools on top.

Think of PHPUnit like a robot that:
1. Runs your code
2. Checks if it behaves correctly
3. Reports what passed or failed

### Types of Tests

| Type | Tests | Speed | Scope |
|------|-------|-------|-------|
| **Unit Tests** | Individual methods/classes | Very fast | Small (single function) |
| **Feature Tests** | Complete features/endpoints | Medium | Large (full request) |
| **Integration Tests** | Multiple components together | Slower | Medium |

We'll cover unit tests in this lesson, feature tests in the next.

---

## Your First Test

Laravel includes PHPUnit. Let's verify:

```bash
php artisan test
```

Or:

```bash
./vendor/bin/phpunit
```

You should see output like:

```
PASS  Tests\Unit\ExampleTest
✓ that true is true

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

Tests:  2 passed
Time:   0.12s
```

Congratulations! You just ran your first tests.

---

## Test Structure

Laravel tests live in the `tests/` directory:

```
tests/
├── Feature/          # Feature/integration tests
│   └── ExampleTest.php
├── Unit/             # Unit tests
│   └── ExampleTest.php
├── CreatesApplication.php
└── TestCase.php
```

### Anatomy of a Test

Open `tests/Unit/ExampleTest.php`:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_that_true_is_true(): void
    {
        $this->assertTrue(true);
    }
}
```

**Key parts**:
1. **Extends TestCase**: Provides testing functionality
2. **Method name starts with `test_`**: PHPUnit finds and runs these
3. **Assertion**: `$this->assertTrue(true)` - checks if true is true (always passes)

### Writing Your First Real Test

Create a simple calculator class to test:

```bash
php artisan make:class Calculator
```

In `app/Calculator.php`:

```php
<?php

namespace App;

class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }

    public function subtract(int $a, int $b): int
    {
        return $a - $b;
    }

    public function multiply(int $a, int $b): int
    {
        return $a * $b;
    }

    public function divide(int $a, int $b): float
    {
        if ($b === 0) {
            throw new \InvalidArgumentException('Cannot divide by zero');
        }

        return $a / $b;
    }
}
```

Create a test:

```bash
php artisan make:test CalculatorTest --unit
```

In `tests/Unit/CalculatorTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    /** @test */
    public function it_adds_two_numbers()
    {
        $calculator = new Calculator();

        $result = $calculator->add(2, 3);

        $this->assertEquals(5, $result);
    }

    /** @test */
    public function it_subtracts_two_numbers()
    {
        $calculator = new Calculator();

        $result = $calculator->subtract(10, 4);

        $this->assertEquals(6, $result);
    }

    /** @test */
    public function it_multiplies_two_numbers()
    {
        $calculator = new Calculator();

        $result = $calculator->multiply(3, 4);

        $this->assertEquals(12, $result);
    }

    /** @test */
    public function it_divides_two_numbers()
    {
        $calculator = new Calculator();

        $result = $calculator->divide(10, 2);

        $this->assertEquals(5, $result);
    }

    /** @test */
    public function it_throws_exception_when_dividing_by_zero()
    {
        $calculator = new Calculator();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot divide by zero');

        $calculator->divide(10, 0);
    }
}
```

**Run the tests**:

```bash
php artisan test --filter=CalculatorTest
```

Output:

```
PASS  Tests\Unit\CalculatorTest
✓ it adds two numbers
✓ it subtracts two numbers
✓ it multiplies two numbers
✓ it divides two numbers
✓ it throws exception when dividing by zero

Tests:  5 passed
Time:   0.05s
```

All green! Your code works correctly.

---

## Test Naming Conventions

### Method Names

Two styles are accepted:

**1. Snake case with `test_` prefix** (recommended by Laravel):

```php
public function test_it_creates_a_user(): void
{
    // ...
}
```

**2. Camel case with `@test` annotation**:

```php
/** @test */
public function itCreatesAUser(): void
{
    // ...
}
```

**Best practice**: Use snake_case with descriptive names:

```php
// ✅ GOOD - Descriptive, clear
test_it_adds_two_numbers
test_user_can_register
test_admin_can_delete_post

// ❌ BAD - Vague, unclear
test_add
test_user
test_delete
```

### Class Names

```php
// ✅ GOOD
CalculatorTest.php
UserTest.php
PostControllerTest.php

// ❌ BAD
TestCalculator.php
Test.php
```

---

## Assertions: The Heart of Testing

Assertions check if your code behaves correctly. PHPUnit provides many assertion methods.

### Common Assertions

```php
// Equality
$this->assertEquals(5, $actual);        // Loose comparison (5 == "5")
$this->assertSame(5, $actual);          // Strict comparison (5 === 5)
$this->assertNotEquals(5, $actual);

// Boolean
$this->assertTrue($value);
$this->assertFalse($value);

// Null
$this->assertNull($value);
$this->assertNotNull($value);

// Strings
$this->assertStringContains('hello', $string);
$this->assertStringStartsWith('Hello', $string);
$this->assertStringEndsWith('world', $string);

// Arrays
$this->assertCount(3, $array);
$this->assertContains('apple', $array);
$this->assertArrayHasKey('name', $array);

// Empty
$this->assertEmpty($value);
$this->assertNotEmpty($value);

// Instances
$this->assertInstanceOf(User::class, $user);

// Greater/Less
$this->assertGreaterThan(5, $value);
$this->assertLessThan(10, $value);
```

### Example: Testing a User Class

```php
class UserTest extends TestCase
{
    /** @test */
    public function it_creates_a_user_with_valid_data()
    {
        $user = new User([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user);
    }

    /** @test */
    public function it_returns_full_name()
    {
        $user = new User([
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);

        $fullName = $user->getFullName();

        $this->assertEquals('John Doe', $fullName);
        $this->assertIsString($fullName);
        $this->assertStringContains('John', $fullName);
    }

    /** @test */
    public function it_checks_if_user_is_admin()
    {
        $admin = new User(['role' => 'admin']);
        $user = new User(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }
}
```

---

## Testing Exceptions

Test that your code throws exceptions when it should:

```php
/** @test */
public function it_throws_exception_for_invalid_email()
{
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid email format');

    new User(['email' => 'not-an-email']);
}

/** @test */
public function it_throws_exception_when_user_not_found()
{
    $this->expectException(ModelNotFoundException::class);

    User::findOrFail(99999);
}
```

---

## Setup and Teardown

Sometimes you need to prepare data before tests or clean up after.

### setUp() and tearDown()

```php
class CalculatorTest extends TestCase
{
    protected Calculator $calculator;

    // Runs BEFORE each test
    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new Calculator();
    }

    // Runs AFTER each test
    protected function tearDown(): void
    {
        unset($this->calculator);
        parent::tearDown();
    }

    /** @test */
    public function it_adds_two_numbers()
    {
        // $this->calculator is already initialized
        $result = $this->calculator->add(2, 3);
        $this->assertEquals(5, $result);
    }
}
```

**Why use setUp()?**
- Avoid repeating initialization code
- Each test starts with fresh state
- Keep tests DRY (Don't Repeat Yourself)

### setUpBeforeClass() and tearDownAfterClass()

```php
class DatabaseTest extends TestCase
{
    // Runs ONCE before all tests in this class
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Setup expensive resources (database connection, etc.)
    }

    // Runs ONCE after all tests in this class
    public static function tearDownAfterClass(): void
    {
        // Cleanup
        parent::tearDownAfterClass();
    }
}
```

---

## Data Providers

Test the same logic with multiple inputs using data providers:

```php
class CalculatorTest extends TestCase
{
    /**
     * @test
     * @dataProvider additionProvider
     */
    public function it_adds_two_numbers($a, $b, $expected)
    {
        $calculator = new Calculator();
        $result = $calculator->add($a, $b);
        $this->assertEquals($expected, $result);
    }

    public static function additionProvider(): array
    {
        return [
            [1, 2, 3],      // 1 + 2 = 3
            [0, 0, 0],      // 0 + 0 = 0
            [-1, 1, 0],     // -1 + 1 = 0
            [100, 200, 300], // 100 + 200 = 300
        ];
    }
}
```

**One test method, multiple scenarios!** PHPUnit runs the test once per dataset.

---

## Organizing Tests

### Test-Driven Development (TDD) Workflow

We'll cover TDD in detail in Lesson 10, but here's the basic cycle:

1. **Red**: Write a failing test
2. **Green**: Write minimal code to make it pass
3. **Refactor**: Improve the code while keeping tests green

**Example TDD workflow**:

```php
// Step 1: Write failing test
/** @test */
public function it_calculates_total_price_with_tax()
{
    $cart = new ShoppingCart();
    $cart->addItem('Laptop', 1000);

    $total = $cart->getTotalWithTax(0.2); // 20% tax

    $this->assertEquals(1200, $total);
}

// Run test → FAIL (method doesn't exist)

// Step 2: Write minimal code
class ShoppingCart
{
    protected array $items = [];

    public function addItem(string $name, float $price): void
    {
        $this->items[] = ['name' => $name, 'price' => $price];
    }

    public function getTotalWithTax(float $taxRate): float
    {
        $subtotal = array_sum(array_column($this->items, 'price'));
        return $subtotal * (1 + $taxRate);
    }
}

// Run test → PASS

// Step 3: Refactor if needed (tests still pass)
```

---

## Testing Best Practices

### 1. One Assertion Per Test (Ideal)

```php
// ❌ NOT IDEAL - Multiple unrelated assertions
/** @test */
public function it_tests_user()
{
    $user = new User(['name' => 'John']);
    $this->assertEquals('John', $user->name);
    $this->assertTrue($user->isActive());
    $this->assertCount(0, $user->posts);
}

// ✅ BETTER - Separate tests for separate concerns
/** @test */
public function it_has_a_name()
{
    $user = new User(['name' => 'John']);
    $this->assertEquals('John', $user->name);
}

/** @test */
public function it_is_active_by_default()
{
    $user = new User();
    $this->assertTrue($user->isActive());
}

/** @test */
public function it_has_no_posts_initially()
{
    $user = new User();
    $this->assertCount(0, $user->posts);
}
```

**Why?** Easier to identify what broke when a test fails.

### 2. Arrange-Act-Assert Pattern

Structure tests clearly:

```php
/** @test */
public function it_calculates_discount()
{
    // Arrange - Set up test data
    $product = new Product(['price' => 100]);
    $discount = 0.2; // 20%

    // Act - Execute the code being tested
    $discountedPrice = $product->applyDiscount($discount);

    // Assert - Verify the result
    $this->assertEquals(80, $discountedPrice);
}
```

### 3. Test Edge Cases

Don't just test happy paths:

```php
/** @test */
public function it_handles_empty_string()
{
    $result = strlen('');
    $this->assertEquals(0, $result);
}

/** @test */
public function it_handles_negative_numbers()
{
    $calculator = new Calculator();
    $result = $calculator->add(-5, -3);
    $this->assertEquals(-8, $result);
}

/** @test */
public function it_handles_large_numbers()
{
    $calculator = new Calculator();
    $result = $calculator->add(PHP_INT_MAX, 0);
    $this->assertEquals(PHP_INT_MAX, $result);
}
```

### 4. Use Descriptive Test Names

```php
// ✅ GOOD
test_user_can_update_their_profile
test_admin_can_delete_any_post
test_guest_cannot_access_dashboard

// ❌ BAD
test_update
test_delete
test_access
```

### 5. Keep Tests Fast

```php
// ❌ SLOW
/** @test */
public function it_sends_email()
{
    // Actually sends email (slow!)
    Mail::to('user@example.com')->send(new WelcomeEmail());
    // Wait for email...
}

// ✅ FAST
/** @test */
public function it_sends_email()
{
    // Fake email sending (instant!)
    Mail::fake();
    Mail::to('user@example.com')->send(new WelcomeEmail());
    Mail::assertSent(WelcomeEmail::class);
}
```

### 6. Tests Should Be Independent

Each test should work alone:

```php
// ❌ BAD - Tests depend on each other
/** @test */
public function it_creates_user()
{
    $user = User::create(['name' => 'John']);
    $this->assertNotNull($user->id);
}

/** @test */
public function it_finds_created_user()
{
    // Assumes previous test ran!
    $user = User::where('name', 'John')->first();
    $this->assertNotNull($user);
}

// ✅ GOOD - Independent tests
/** @test */
public function it_creates_user()
{
    $user = User::create(['name' => 'John']);
    $this->assertNotNull($user->id);
}

/** @test */
public function it_finds_user()
{
    // Create its own data
    $user = User::create(['name' => 'Jane']);
    $found = User::find($user->id);
    $this->assertEquals('Jane', $found->name);
}
```

---

## Running Tests

### Run All Tests

```bash
php artisan test
```

### Run Specific Test File

```bash
php artisan test tests/Unit/CalculatorTest.php
```

### Run Specific Test Method

```bash
php artisan test --filter=it_adds_two_numbers
```

### Run Tests by Group

```php
/**
 * @test
 * @group calculator
 */
public function it_adds_two_numbers()
{
    // ...
}
```

```bash
php artisan test --group=calculator
```

### Stop on First Failure

```bash
php artisan test --stop-on-failure
```

### Show Coverage (Requires Xdebug)

```bash
php artisan test --coverage
```

---

## Quick Quiz

1. **What is the difference between unit tests and feature tests?**

2. **What does `$this->assertEquals(5, $result)` do?**

3. **Why use setUp() method in tests?**

4. **What is the Arrange-Act-Assert pattern?**

5. **How do you test that code throws an exception?**

---

## Practice Exercise

**Build and test a ShoppingCart class**:

1. Create `app/ShoppingCart.php`:
   - `addItem(name, price, quantity)`
   - `removeItem(name)`
   - `getTotal()`
   - `applyDiscount(percentage)`
   - `getItemCount()`
   - `clear()`

2. Create `tests/Unit/ShoppingCartTest.php`:
   - Test adding items
   - Test removing items
   - Test calculating total
   - Test applying discount
   - Test counting items
   - Test clearing cart
   - Test edge cases (negative prices, empty cart, etc.)

**Requirements**:
- At least 10 tests
- Use setUp() to initialize cart
- Use data providers for discount testing
- Test edge cases
- Use descriptive test names
- Follow Arrange-Act-Assert pattern

---

## What's Next?

In the next lesson, you'll learn about **Unit Tests in Laravel** - testing models, services, and business logic with Laravel's testing tools and database features. You'll learn about factories, database transactions, and more!

Testing is a skill that takes practice. The more you write tests, the better you'll become at writing testable code!
