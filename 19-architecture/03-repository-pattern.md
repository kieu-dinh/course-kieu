# Lesson 03 - Repository Pattern

**Duration**: 3-4 hours

---

## Introduction

The **Repository Pattern** adds an abstraction layer between your business logic (services) and data access (database). Think of repositories as "data managers" that know how to fetch, store, and manipulate data without your services needing to know the details.

Imagine you're building a library application. Without repositories, your services would directly interact with bookshelves (database). With repositories, services ask a librarian (repository) to "get me books by this author" without caring which shelf they're on or how they're organized.

---

## The Problem: Direct Database Access

Let's look at a service that directly uses Eloquent:

```php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return User::where('status', 'active')
            ->where('email_verified_at', '!=', null)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function searchUsers(string $query): Collection
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }
}
```

**Problems:**

1. **Tight coupling to Eloquent** - What if you want to switch to MongoDB? Rewrite everything.
2. **Hard to test** - Must set up database for every test
3. **Duplicated queries** - Same queries repeated across services
4. **Mixed concerns** - Service contains both business logic AND data access logic
5. **Can't mock data access** - Service tests become slow integration tests

---

## The Solution: Repository Pattern

```php
// app/Repositories/Contracts/UserRepositoryInterface.php
namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function getActiveUsers(): Collection;
    public function search(string $query): Collection;
    public function update(User $user, array $data): bool;
    public function delete(User $user): bool;
}
```

```php
// app/Repositories/EloquentUserRepository.php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return User::where('status', 'active')
            ->whereNotNull('email_verified_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function search(string $query): Collection
    {
        return User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
```

```php
// app/Services/UserService.php
namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function createUser(array $data): User
    {
        // Business logic only - no database queries
        return $this->userRepository->create($data);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActiveUsers();
    }
}
```

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    $this->app->bind(
        UserRepositoryInterface::class,
        EloquentUserRepository::class
    );
}
```

---

## Benefits of Repository Pattern

### 1. Abstraction & Flexibility

Swap implementations without changing services.

```php
// Need to use MongoDB instead? Just create new implementation
class MongoUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return MongoDB::collection('users')
            ->where('email', $email)
            ->first();
    }
    // ... other methods
}

// Bind in service provider
$this->app->bind(
    UserRepositoryInterface::class,
    MongoUserRepository::class // Service code unchanged!
);
```

### 2. Testability

Easy to mock repositories in service tests.

```php
// tests/Unit/Services/UserServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_creates_user()
    {
        // Mock repository
        $mockRepo = Mockery::mock(UserRepositoryInterface::class);

        $mockRepo->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ])
            ->andReturn(new User([
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]));

        // Inject mock into service
        $service = new UserService($mockRepo);

        // Test service logic without database
        $user = $service->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
}
```

### 3. Reusability

Share complex queries across services.

```php
// Multiple services use the same repository method
class UserService
{
    public function getActiveUsers()
    {
        return $this->userRepository->getActiveUsers();
    }
}

class ReportService
{
    public function generateUserReport()
    {
        $activeUsers = $this->userRepository->getActiveUsers();
        // Generate report...
    }
}

class NotificationService
{
    public function sendBulkNotification()
    {
        $activeUsers = $this->userRepository->getActiveUsers();
        // Send notifications...
    }
}
```

### 4. Query Centralization

Complex queries defined once, used everywhere.

```php
public function getActiveUsersWithOrders(): Collection
{
    return User::where('status', 'active')
        ->whereNotNull('email_verified_at')
        ->whereHas('orders', function ($query) {
            $query->where('created_at', '>=', now()->subMonths(6));
        })
        ->with(['orders', 'profile'])
        ->orderBy('created_at', 'desc')
        ->get();
}

// If query changes, update once in repository
// All services automatically use updated version
```

---

## Repository Organization

### Basic Structure

```
app/
├── Repositories/
│   ├── Contracts/              # Interfaces
│   │   ├── UserRepositoryInterface.php
│   │   ├── OrderRepositoryInterface.php
│   │   └── ProductRepositoryInterface.php
│   ├── EloquentUserRepository.php
│   ├── EloquentOrderRepository.php
│   └── EloquentProductRepository.php
└── Providers/
    └── RepositoryServiceProvider.php  # Bind interfaces to implementations
```

### Repository Service Provider

```php
// app/Providers/RepositoryServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\EloquentOrderRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );

        $this->app->bind(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );
    }
}
```

Don't forget to register in `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\RepositoryServiceProvider::class,
],
```

---

## Real-World Example: Order Repository

Let's build a complete repository for orders.

### 1. Interface

```php
// app/Repositories/Contracts/OrderRepositoryInterface.php
namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function create(array $data): Order;
    public function findById(int $id): ?Order;
    public function findWithItems(int $id): ?Order;
    public function getByCustomer(int $customerId): Collection;
    public function getPendingOrders(): Collection;
    public function getRecentOrders(int $limit = 10): Collection;
    public function searchByOrderNumber(string $orderNumber): ?Order;
    public function updateStatus(Order $order, string $status): bool;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
```

### 2. Implementation

```php
// app/Repositories/EloquentOrderRepository.php
namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function findById(int $id): ?Order
    {
        return Order::find($id);
    }

    public function findWithItems(int $id): ?Order
    {
        return Order::with(['items.product', 'customer'])
            ->find($id);
    }

    public function getByCustomer(int $customerId): Collection
    {
        return Order::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingOrders(): Collection
    {
        return Order::where('status', 'pending')
            ->with('customer')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getRecentOrders(int $limit = 10): Collection
    {
        return Order::with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchByOrderNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)
            ->with(['items', 'customer'])
            ->first();
    }

    public function updateStatus(Order $order, string $status): bool
    {
        return $order->update(['status' => $status]);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Order::with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
```

### 3. Using in Service

```php
// app/Services/OrderService.php
namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}

    public function createOrder(array $data): Order
    {
        // Business logic
        $orderData = [
            'customer_id' => $data['customer_id'],
            'order_number' => $this->generateOrderNumber(),
            'status' => 'pending',
            'total' => $this->calculateTotal($data['items']),
        ];

        return $this->orderRepository->create($orderData);
    }

    public function getCustomerOrders(int $customerId): Collection
    {
        return $this->orderRepository->getByCustomer($customerId);
    }

    public function fulfillOrder(int $orderId): bool
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order || $order->status !== 'pending') {
            return false;
        }

        // Business logic for fulfillment
        $this->processPayment($order);
        $this->reserveInventory($order);

        return $this->orderRepository->updateStatus($order, 'fulfilled');
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    private function calculateTotal(array $items): float
    {
        // Calculation logic
    }
}
```

---

## Advanced Repository Patterns

### Pattern 1: Base Repository

Create a base repository with common CRUD operations.

```php
// app/Repositories/BaseRepository.php
namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(Model $model, array $data): bool
    {
        return $model->update($data);
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }
}
```

```php
// app/Repositories/EloquentUserRepository.php
namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class EloquentUserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    // Only implement specific methods
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getActiveUsers(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }
}
```

### Pattern 2: Criteria Pattern

Apply reusable query filters.

```php
// app/Repositories/Criteria/CriteriaInterface.php
namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

interface CriteriaInterface
{
    public function apply(Builder $query): Builder;
}
```

```php
// app/Repositories/Criteria/ActiveUsersCriteria.php
namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('email_verified_at');
    }
}
```

```php
// app/Repositories/Criteria/WithOrdersCriteria.php
namespace App\Repositories\Criteria;

use Illuminate\Database\Eloquent\Builder;

class WithOrdersCriteria implements CriteriaInterface
{
    public function __construct(
        private int $minOrders = 1
    ) {}

    public function apply(Builder $query): Builder
    {
        return $query->has('orders', '>=', $this->minOrders);
    }
}
```

```php
// app/Repositories/EloquentUserRepository.php
class EloquentUserRepository implements UserRepositoryInterface
{
    private array $criteria = [];

    public function pushCriteria(CriteriaInterface $criteria): self
    {
        $this->criteria[] = $criteria;
        return $this;
    }

    public function applyCriteria(): Builder
    {
        $query = User::query();

        foreach ($this->criteria as $criteria) {
            $query = $criteria->apply($query);
        }

        $this->criteria = []; // Reset after applying
        return $query;
    }

    public function get(): Collection
    {
        return $this->applyCriteria()->get();
    }
}
```

```php
// Usage in service
$users = $this->userRepository
    ->pushCriteria(new ActiveUsersCriteria())
    ->pushCriteria(new WithOrdersCriteria(minOrders: 5))
    ->get();
```

### Pattern 3: Specification Pattern

Define business rules as objects.

```php
// app/Specifications/UserSpecification.php
namespace App\Specifications;

use Illuminate\Database\Eloquent\Builder;

abstract class Specification
{
    abstract public function toQuery(Builder $query): Builder;

    public function and(Specification $spec): AndSpecification
    {
        return new AndSpecification($this, $spec);
    }

    public function or(Specification $spec): OrSpecification
    {
        return new OrSpecification($this, $spec);
    }
}

class ActiveUserSpecification extends Specification
{
    public function toQuery(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}

class VerifiedEmailSpecification extends Specification
{
    public function toQuery(Builder $query): Builder
    {
        return $query->whereNotNull('email_verified_at');
    }
}

class AndSpecification extends Specification
{
    public function __construct(
        private Specification $left,
        private Specification $right
    ) {}

    public function toQuery(Builder $query): Builder
    {
        return $this->right->toQuery(
            $this->left->toQuery($query)
        );
    }
}
```

```php
// Usage
$spec = (new ActiveUserSpecification())
    ->and(new VerifiedEmailSpecification());

$users = User::query();
$spec->toQuery($users)->get();
```

---

## Common Pitfalls

### Pitfall 1: Over-Abstracting Simple Queries

```php
// Overkill: Creating repository method for one-time query
public function getUsersCreatedOnDate(string $date): Collection
{
    return User::whereDate('created_at', $date)->get();
}

// Better: Use Eloquent directly for unique queries
$users = User::whereDate('created_at', $date)->get();
```

**Rule**: Only add to repository if query is reused 2+ times.

### Pitfall 2: Business Logic in Repository

```php
// Bad: Business logic in repository
class OrderRepository
{
    public function createOrder(array $data): Order
    {
        // This is business logic - belongs in service!
        if ($this->isCustomerEligibleForDiscount($data['customer_id'])) {
            $data['discount'] = $this->calculateDiscount($data);
        }

        return Order::create($data);
    }
}

// Good: Repository only handles data
class OrderRepository
{
    public function create(array $data): Order
    {
        return Order::create($data);
    }
}

// Service contains business logic
class OrderService
{
    public function createOrder(array $data): Order
    {
        if ($this->isCustomerEligibleForDiscount($data['customer_id'])) {
            $data['discount'] = $this->calculateDiscount($data);
        }

        return $this->orderRepository->create($data);
    }
}
```

### Pitfall 3: Returning Query Builders

```php
// Bad: Returning Builder (breaks abstraction)
public function getActiveUsers(): Builder
{
    return User::where('status', 'active'); // Caller can modify query
}

// Good: Return Collection or specific result
public function getActiveUsers(): Collection
{
    return User::where('status', 'active')->get();
}

// Or use criteria/specification pattern for flexible queries
```

---

## Testing Repositories

### Unit Testing Repository (With Database)

```php
// tests/Unit/Repositories/UserRepositoryTest.php
namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\EloquentUserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUserRepository();
    }

    public function test_creates_user()
    {
        $user = $this->repository->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'hashed_password',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    public function test_finds_user_by_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $user = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($user);
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_returns_null_when_user_not_found()
    {
        $user = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($user);
    }

    public function test_gets_only_active_users()
    {
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);
        User::factory()->create(['status' => 'active']);

        $activeUsers = $this->repository->getActiveUsers();

        $this->assertCount(2, $activeUsers);
        $this->assertTrue($activeUsers->every(fn($u) => $u->status === 'active'));
    }
}
```

### Integration Testing Service with Mocked Repository

```php
// tests/Unit/Services/UserServiceTest.php
namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_creates_user_with_hashed_password()
    {
        $mockRepo = Mockery::mock(UserRepositoryInterface::class);

        $mockRepo->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return isset($data['password'])
                    && strlen($data['password']) > 20; // Hashed
            }))
            ->andReturn(new User(['id' => 1]));

        $service = new UserService($mockRepo);

        $user = $service->createUser([
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'plain_password',
        ]);

        $this->assertEquals(1, $user->id);
    }
}
```

---

## Best Practices Summary

1. **Keep repositories simple** - Only data access, no business logic
2. **Use interfaces** - Always code against interfaces, not implementations
3. **Return concrete types** - Collection, Model, or null (not Builder)
4. **Name methods clearly** - `findByEmail()` not `getUser()`
5. **Don't over-abstract** - Create methods only for reused queries
6. **One repository per entity** - UserRepository, OrderRepository, etc.
7. **Include eager loading** - Load relationships in repository methods
8. **Test repositories with database** - They're data layer, need real DB tests

---

## Quick Quiz

1. **What is the main benefit of Repository Pattern?**
   - A) Makes queries faster
   - B) Abstracts data access from business logic
   - C) Reduces database calls
   - D) Makes code shorter

2. **Should repositories contain business logic?**
   - A) Yes, all logic should be in repositories
   - B) No, only data access code
   - C) Yes, but only simple logic
   - D) It depends on the project

3. **What should a repository method return?**
   - A) Query Builder
   - B) Collection, Model, or null
   - C) Array
   - D) JSON

**Answers**: 1-B, 2-B, 3-B

---

## Summary

**Repository Pattern** abstracts data access:

**Key concepts:**
- Interface defines contract
- Implementation handles database operations
- Services depend on repository interface
- Bind interface to implementation in service provider

**Benefits:**
- Abstraction from database implementation
- Easy to test with mocks
- Centralized query logic
- Flexible - swap implementations

**Structure:**
```
Repositories/
├── Contracts/              # Interfaces
│   └── UserRepositoryInterface.php
└── EloquentUserRepository.php  # Eloquent implementation
```

**Remember:**
- Repositories = Data access only
- Services = Business logic
- Keep them separate!

In the next lesson, we'll explore **SOLID Principles** - the foundation of clean, maintainable code!
