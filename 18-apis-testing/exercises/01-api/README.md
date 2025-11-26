# Exercise 18.1 - Laravel API Endpoints

## Objective

Build a complete REST API with Laravel following REST conventions and best practices.

---

## Task

Create a Laravel API for managing a simple resource (e.g., Books, Posts, or Products) with all CRUD operations.

### Requirements

1. **Model & Migration**
   - Create a Model and Migration for your resource
   - Add appropriate fields (name, description, price/status, timestamps)

2. **API Routes**
   - Create API routes in `routes/api.php`
   - Implement REST endpoints:
     - `GET /api/items` - List all items
     - `POST /api/items` - Create new item
     - `GET /api/items/{id}` - Show single item
     - `PUT /api/items/{id}` - Update item
     - `DELETE /api/items/{id}` - Delete item

3. **Controller**
   - Create a resource controller handling all CRUD operations
   - Return JSON responses with appropriate status codes:
     - 200 for successful GET/PUT/DELETE
     - 201 for successful POST
     - 404 for not found
     - 422 for validation errors

4. **Request Validation**
   - Create a Form Request class for validation
   - Validate required fields, data types, and lengths
   - Return validation errors as JSON

5. **API Response Format**
   - All responses should follow a consistent structure:
   ```json
   {
     "data": {},
     "message": "Success message"
   }
   ```
   - Errors should include error details

---

## Starter Code

```php
// routes/api.php
Route::apiResource('items', ItemController::class);

// app/Http/Controllers/ItemController.php
namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use Illuminate\Http\Response;

class ItemController extends Controller
{
    public function index()
    {
        // Return all items
    }

    public function store(StoreItemRequest $request)
    {
        // Create item and return 201
    }

    public function show(Item $item)
    {
        // Return single item
    }

    public function update(StoreItemRequest $request, Item $item)
    {
        // Update item
    }

    public function destroy(Item $item)
    {
        // Delete item
    }
}
```

---

## Setup

```bash
# Create model and migration
php artisan make:model Item -m

# Create controller
php artisan make:controller ItemController --resource --api

# Create form request
php artisan make:request StoreItemRequest

# Run migrations
php artisan migrate
```

---

## Testing Your API

### Using Postman or cURL:

```bash
# Create item
curl -X POST http://localhost:8000/api/items \
  -H "Content-Type: application/json" \
  -d '{"name":"My Item","description":"Test"}'

# Get all items
curl http://localhost:8000/api/items

# Get single item
curl http://localhost:8000/api/items/1

# Update item
curl -X PUT http://localhost:8000/api/items/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Name"}'

# Delete item
curl -X DELETE http://localhost:8000/api/items/1
```

---

## Bonus Challenges

1. Add pagination to the list endpoint
2. Add filtering by status/category
3. Add sorting by different fields
4. Implement soft deletes
5. Add timestamps to responses (created_at, updated_at)
6. Add search functionality

---

## Checklist

- [ ] Model and migration created
- [ ] All API endpoints work
- [ ] Validation works and returns proper errors
- [ ] Status codes are correct (201 for create, 404 for not found)
- [ ] JSON responses are consistent
- [ ] Can test with Postman/cURL without errors

---

## Solution Check

Test each endpoint:
- POST returns 201 with created data
- GET list returns 200 with array of items
- GET single returns 200 with item data
- PUT returns 200 with updated data
- DELETE returns 200/204 with success message
- Invalid data returns 422 with validation errors
