# Exercise 19.2 - Repository Pattern

## Objective

Learn the Repository pattern to abstract data access logic from business logic.

---

## Task

Implement the Repository pattern for data access, decoupling models from services.

### Requirements

1. **Create Repository Interface**
   - Define contract for data access methods
   - Make repositories swappable

2. **Create Repository Implementation**
   - Implement actual database queries
   - Handle all query logic here (filters, sorting, pagination)
   - No business logic in repository

3. **Use Repository in Service**
   - Service depends on repository interface
   - Service uses repository for data access
   - Service implements business logic

4. **Query Methods**
   - getAll() - get all records with filters/pagination
   - getById(id) - get single record
   - create(data) - create new record
   - update(id, data) - update record
   - delete(id) - delete record
   - findBy(field, value) - find by custom field

5. **Advanced Queries**
   - Complex filters
   - Relationship loading
   - Sorting and pagination
   - Custom scopes

---

## Starter Code

```bash
# Create repository interface and class
mkdir -p app/Repositories
```

```php
// app/Repositories/ItemRepository.php (Implementation)
namespace App\Repositories;

use App\Models\Item;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class ItemRepository implements ItemRepositoryInterface
{
    private Item $model;

    public function __construct(Item $model)
    {
        $this->model = $model;
    }

    /**
     * Get all items with pagination
     */
    public function getAll(int $page = 1, int $perPage = 15): Paginator
    {
        return $this->model
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, '*', 'page', $page);
    }

    /**
     * Get item by ID
     */
    public function getById(int $id): ?Item
    {
        return $this->model->find($id);
    }

    /**
     * Create new item
     */
    public function create(array $data): Item
    {
        return $this->model->create($data);
    }

    /**
     * Update item
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->find($id)?->update($data) ?? false;
    }

    /**
     * Delete item
     */
    public function delete(int $id): bool
    {
        return (bool) $this->model->destroy($id);
    }

    /**
     * Find by field
     */
    public function findBy(string $field, $value): ?Item
    {
        return $this->model->where($field, $value)->first();
    }

    /**
     * Filter items
     */
    public function filter(array $filters): Collection
    {
        $query = $this->model->newQuery();

        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->get();
    }

    /**
     * Get with relationships
     */
    public function getWithRelations(int $id, array $relations): ?Item
    {
        return $this->model
            ->with($relations)
            ->find($id);
    }

    /**
     * Count total records
     */
    public function count(): int
    {
        return $this->model->count();
    }
}

// app/Repositories/ItemRepositoryInterface.php (Contract)
namespace App\Repositories;

use App\Models\Item;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

interface ItemRepositoryInterface
{
    public function getAll(int $page = 1, int $perPage = 15): Paginator;
    public function getById(int $id): ?Item;
    public function create(array $data): Item;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function findBy(string $field, $value): ?Item;
    public function filter(array $filters): Collection;
    public function getWithRelations(int $id, array $relations): ?Item;
    public function count(): int;
}
```

---

## Service Using Repository

```php
// app/Services/ItemService.php
namespace App\Services;

use App\Repositories\ItemRepositoryInterface;

class ItemService
{
    private ItemRepositoryInterface $repository;

    public function __construct(ItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllItems(array $filters = [])
    {
        if (!empty($filters)) {
            return $this->repository->filter($filters);
        }

        return $this->repository->getAll();
    }

    public function getItemDetails(int $id)
    {
        return $this->repository->getWithRelations($id, [
            'category', 'tags', 'reviews'
        ]);
    }

    public function createItem(array $data)
    {
        // Business logic here
        if (empty($data['name'])) {
            throw new \Exception('Name required');
        }

        return $this->repository->create($data);
    }

    public function updatePrice(int $id, float $newPrice)
    {
        // Complex business logic
        if ($newPrice <= 0) {
            throw new \Exception('Price must be positive');
        }

        return $this->repository->update($id, ['price' => $newPrice]);
    }
}
```

---

## Service Provider Registration

```php
// app/Providers/RepositoryServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ItemRepositoryInterface;
use App\Repositories\ItemRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ItemRepositoryInterface::class,
            ItemRepository::class
        );
    }
}

// config/app.php - Add to providers
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```

---

## Benefits

- **Data Access Abstraction**: Can swap implementations (DB → Cache → API)
- **Testability**: Mock repository in tests easily
- **Reusability**: Use same repository in multiple services
- **Maintainability**: All queries in one place
- **Clean Architecture**: Clear separation of concerns

---

## Testing with Repository

```php
// tests/Unit/ItemServiceTest.php
namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ItemService;
use App\Repositories\ItemRepositoryInterface;
use Mockery\MockInterface;

class ItemServiceTest extends TestCase
{
    public function test_service_uses_repository()
    {
        $this->mock(ItemRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('getAll')
                 ->andReturn([]);
        });

        $service = new ItemService(
            app(ItemRepositoryInterface::class)
        );

        $result = $service->getAllItems();

        $this->assertIsArray($result);
    }
}
```

---

## Bonus Challenges

1. Create BaseRepository abstract class for shared methods
2. Add caching layer to repository
3. Implement query builder pattern in repository
4. Add soft deletes support
5. Create repository for related model (Category)
6. Add pagination helper methods

---

## Checklist

- [ ] ItemRepositoryInterface created
- [ ] ItemRepository implemented
- [ ] All CRUD methods working
- [ ] Service uses repository
- [ ] Service provider configured
- [ ] Can swap repository implementation
- [ ] Tests mock repository
- [ ] API endpoints still work

---

## Solution Check

The flow should be:
Controller → Service → Repository → Model → Database

Each layer has clear responsibilities:
- Controller: HTTP
- Service: Business logic
- Repository: Data queries
- Model: ORM mapping
