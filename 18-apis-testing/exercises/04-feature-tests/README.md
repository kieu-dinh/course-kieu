# Exercise 18.4 - Feature Tests

## Objective

Learn to write feature/integration tests for your API endpoints.

---

## Task

Write comprehensive feature tests for all API endpoints from Exercise 18.1.

### Requirements

1. **API Endpoint Tests**
   - Test GET list endpoint (returns 200, has items)
   - Test POST endpoint (creates item, returns 201)
   - Test GET single endpoint (returns 200, has item data)
   - Test PUT endpoint (updates item, returns 200)
   - Test DELETE endpoint (deletes item, returns correct status)

2. **Error Cases**
   - Test 404 when item not found
   - Test 422 when validation fails
   - Test proper error message format

3. **Database Testing**
   - Verify database changes after API calls
   - Use assertDatabaseHas() and assertDatabaseMissing()
   - Verify counts and relationships

4. **Response Validation**
   - Check response status codes
   - Validate response JSON structure
   - Check presence of required fields
   - Verify data integrity

---

## Starter Code

```bash
# Generate feature test
php artisan make:test ItemApiTest
```

```php
// tests/Feature/ItemApiTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_items()
    {
        $items = Item::factory(3)->create();

        $response = $this->getJson('/api/items');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_create_item()
    {
        $data = [
            'name' => 'New Item',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/items', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Item');

        $this->assertDatabaseHas('items', $data);
    }

    public function test_can_show_item()
    {
        $item = Item::factory()->create();

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $item->id);
    }

    public function test_can_update_item()
    {
        $item = Item::factory()->create();

        $response = $this->putJson("/api/items/{$item->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('items', ['id' => $item->id, 'name' => 'Updated Name']);
    }

    public function test_can_delete_item()
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson("/api/items/{$item->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }
}
```

---

## RefreshDatabase Trait

The `RefreshDatabase` trait automatically:
- Migrates fresh database before each test
- Rolls back migrations after each test
- Keeps tests isolated from each other

---

## Common Assertions

```php
// Status codes
$response->assertStatus(200);
$response->assertOk(); // 200
$response->assertCreated(); // 201
$response->assertNotFound(); // 404

// JSON assertions
$response->assertJson(['name' => 'Item']);
$response->assertJsonPath('data.name', 'Item');
$response->assertJsonCount(3, 'data');
$response->assertJsonStructure(['data', 'message']);

// Database assertions
$this->assertDatabaseHas('items', ['name' => 'Item']);
$this->assertDatabaseMissing('items', ['id' => 999]);
$this->assertDatabaseCount('items', 5);
```

---

## Run Tests

```bash
# Run all feature tests
php artisan test tests/Feature

# Run specific test
php artisan test tests/Feature/ItemApiTest.php

# Run single test method
php artisan test --filter test_can_list_items

# Run with detailed output
php artisan test --verbose
```

---

## Bonus Challenges

1. Test validation errors with invalid data
2. Test that non-existent item returns 404
3. Test that list endpoint returns paginated results
4. Test filtering and sorting
5. Test with authenticated users (if using auth)
6. Test rate limiting
7. Write tests for edge cases (empty lists, null values)

---

## Checklist

- [ ] ItemApiTest file created
- [ ] Test for listing items (GET)
- [ ] Test for creating item (POST)
- [ ] Test for showing item (GET /id)
- [ ] Test for updating item (PUT)
- [ ] Test for deleting item (DELETE)
- [ ] Test for 404 errors
- [ ] Test for validation errors
- [ ] All tests pass

---

## Solution Check

Run tests:
```bash
php artisan test tests/Feature/ItemApiTest.php
```

Expected output:
```
✓ test_can_list_items
✓ test_can_create_item
✓ test_can_show_item
✓ test_can_update_item
✓ test_can_delete_item
✓ test_returns_404_for_missing_item
✓ test_returns_422_for_invalid_data

7 passed
```
