# Exercise 18.2 - API Resources

## Objective

Learn how to use Laravel API Resources to transform and format API responses consistently.

---

## Task

Refactor your API from Exercise 18.1 to use API Resources for transforming model data into JSON responses.

### Requirements

1. **Create API Resources**
   - Create a Resource class for single item display
   - Create a ResourceCollection for multiple items
   - Define which fields to include in API responses

2. **Transform Model Data**
   - Hide sensitive fields (passwords, tokens, etc.)
   - Add computed fields if needed
   - Format data appropriately (dates, numbers)

3. **Use Resources in Controller**
   - Return resources from all controller methods
   - Use ResourceCollection for list endpoints
   - Use Resource for single item endpoints

4. **Conditional Data**
   - Show different fields based on conditions
   - Include related data using with() method
   - Use when() for conditional field inclusion

5. **Nested Resources**
   - If your model has relationships, include related data
   - Use nested resources for related models

---

## Starter Code

```php
// app/Http/Resources/ItemResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}

// app/Http/Resources/ItemCollection.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ItemCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}

// In Controller:
public function index()
{
    return new ItemCollection(Item::all());
}

public function show(Item $item)
{
    return new ItemResource($item);
}
```

---

## Setup

```bash
# Create resource
php artisan make:resource ItemResource

# Create collection
php artisan make:resource ItemCollection --collection
```

---

## Example Response

Before (raw model):
```json
{
  "id": 1,
  "name": "Item 1",
  "description": "Description",
  "password": "hashed_password",
  "created_at": "2024-01-15 10:30:00",
  "updated_at": "2024-01-15 10:30:00"
}
```

After (using resource):
```json
{
  "id": 1,
  "name": "Item 1",
  "description": "Description",
  "created_at": "2024-01-15"
}
```

---

## Bonus Challenges

1. Add a category relationship and include it in responses
2. Use when() to conditionally include admin-only fields
3. Format dates as readable strings (e.g., "January 15, 2024")
4. Add computed fields (e.g., full_name from first_name + last_name)
5. Create separate resource classes for admin and user views
6. Add pagination metadata to collection responses

---

## Checklist

- [ ] ItemResource created with selected fields
- [ ] ItemCollection created
- [ ] All controller methods return resources
- [ ] API responses match expected format
- [ ] Sensitive fields are hidden
- [ ] Conditional fields work correctly
- [ ] Relationships are properly included

---

## Solution Check

API responses should:
- Include only necessary fields
- Have consistent date format
- Show relationships when applicable
- Not expose sensitive data
- Match documented format
