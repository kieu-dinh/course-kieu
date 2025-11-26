# Exercise 18.3 - Unit Tests

## Objective

Learn to write unit tests for your application logic and models.

---

## Task

Write unit tests for your Item model and related business logic.

### Requirements

1. **Model Unit Tests**
   - Test model creation with valid data
   - Test model validation (invalid data should fail)
   - Test model relationships if applicable
   - Test model accessor/mutator methods

2. **Business Logic Tests**
   - Test helper functions or utility methods
   - Test model methods (custom methods on model)
   - Test calculation methods (price calculations, etc.)

3. **Test Structure**
   - Use PHPUnit test structure
   - One assertion per test or logical group
   - Descriptive test names (test_* or it_*)
   - Setup (arrange) data before testing (act)
   - Assert expected outcomes

4. **Mock/Stub Data**
   - Create test data using factories
   - Use setUp() method for common test setup
   - Clean database between tests

---

## Starter Code

```bash
# Generate test file
php artisan make:test ItemTest --unit
```

```php
// tests/Unit/ItemTest.php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Item;

class ItemTest extends TestCase
{
    public function test_item_can_be_created()
    {
        $item = new Item([
            'name' => 'Test Item',
            'description' => 'Test Description'
        ]);

        $this->assertEquals('Test Item', $item->name);
    }

    public function test_item_has_correct_fields()
    {
        // Test that model has expected properties
    }

    public function test_item_validation()
    {
        // Test that invalid data is rejected
    }
}
```

---

## Using Factories

```php
// Create factory
php artisan make:factory ItemFactory

// database/factories/ItemFactory.php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}

// Use in test
use App\Models\Item;

public function test_can_create_item_with_factory()
{
    $item = Item::factory()->create();
    $this->assertDatabaseHas('items', ['id' => $item->id]);
}
```

---

## Run Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Unit/ItemTest.php

# Run with verbose output
php artisan test --verbose

# Run tests matching pattern
php artisan test --filter test_item_
```

---

## Example Tests

```php
public function test_item_name_is_required()
{
    $item = Item::factory()->make(['name' => null]);
    $this->assertNull($item->name);
}

public function test_item_has_timestamps()
{
    $item = Item::factory()->create();
    $this->assertNotNull($item->created_at);
    $this->assertNotNull($item->updated_at);
}

public function test_can_update_item()
{
    $item = Item::factory()->create(['name' => 'Original']);
    $item->update(['name' => 'Updated']);
    $this->assertEquals('Updated', $item->name);
}

public function test_can_delete_item()
{
    $item = Item::factory()->create();
    $item->delete();
    $this->assertDatabaseMissing('items', ['id' => $item->id]);
}
```

---

## Bonus Challenges

1. Test model scopes (if any)
2. Test model relationships
3. Test custom model methods
4. Test attribute casting
5. Test model events (creating, updating, deleting)
6. Achieve 80%+ code coverage

---

## Checklist

- [ ] ItemTest file created
- [ ] ItemFactory created with realistic data
- [ ] At least 5 unit tests written
- [ ] Tests use descriptive names
- [ ] Tests use setUp() for common setup
- [ ] All tests pass
- [ ] Can run tests with: php artisan test

---

## Solution Check

Run tests:
```bash
php artisan test tests/Unit/ItemTest.php
```

Expected output:
```
✓ test_item_can_be_created
✓ test_item_has_correct_fields
✓ test_item_validation
✓ test_can_update_item
✓ test_can_delete_item

5 passed
```
