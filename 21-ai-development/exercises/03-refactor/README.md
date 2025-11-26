# Exercise 21.3 - Refactoring with AI

## Objective

Learn to use AI as a refactoring partner to improve existing code systematically.

---

## Task

Use AI to guide you through refactoring a larger piece of code.

---

## Refactoring Process

### Phase 1: Analysis

Get AI to analyze your code:

```
Analyze this code for refactoring opportunities:

[YOUR CODE]

For each piece, tell me:
1. Current code quality (1-10)
2. Main issues preventing higher quality
3. Refactoring effort estimate
4. Potential improvements

Prioritize by impact and effort.
```

### Phase 2: Planning

Create a refactoring plan:

```
Based on this analysis:
[AI'S ANALYSIS]

Create a step-by-step refactoring plan:
1. What to refactor first (and why)
2. Dependency order
3. Estimated time per step
4. Testing strategy for each step
5. How to maintain backward compatibility
6. How to verify each change works

Break each step into small, manageable tasks.
```

### Phase 3: Implementation Guidance

Get help implementing each step:

```
Step [N]: [DESCRIPTION]

Current code:
[CURRENT CODE]

Help me refactor this to:
[GOAL]

Please:
1. Show the refactored code
2. Explain each change
3. Show test cases to verify it works
4. Highlight what improved

Keep it focused on this one step only.
```

### Phase 4: Verification

Ensure the refactoring is successful:

```
After refactoring from:
[OLD CODE]

To:
[NEW CODE]

Verify:
1. Functionality is identical
2. Performance improved (how to test?)
3. Code quality improved (specific metrics)
4. Tests pass
5. No new technical debt introduced

What else should I check?
```

---

## Example: Refactoring an Order Service

### Initial Code (Problematic)

```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\OrderConfirmation;

class OrderService
{
    public function createOrder($userId, $items)
    {
        // Validate user
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        // Calculate total
        $total = 0;
        $orderItems = [];
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return ['success' => false, 'error' => 'Product not found'];
            }

            $quantity = $item['quantity'];
            $price = $product->price * $quantity;
            $total += $price;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price
            ];
        }

        // Check stock
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product->stock < $item['quantity']) {
                return ['success' => false, 'error' => 'Not enough stock for ' . $product->name];
            }
        }

        // Create order
        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending'
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create($item);
                $product = Product::find($item['product_id']);
                $product->update(['stock' => $product->stock - $item['quantity']]);
            }

            // Process payment
            $paymentResult = $this->processPayment($user, $total);
            if (!$paymentResult['success']) {
                DB::rollBack();
                return ['success' => false, 'error' => 'Payment failed'];
            }

            // Send email
            Mail::to($user->email)->send(new OrderConfirmation($order));

            DB::commit();

            return ['success' => true, 'order' => $order];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'error' => 'Order creation failed: ' . $e->getMessage()];
        }
    }

    private function processPayment($user, $amount)
    {
        // Simulate payment processing
        if ($amount < 0) {
            return ['success' => false];
        }

        // Call payment API
        $response = \Http::post('https://payment-api.com/charge', [
            'amount' => $amount,
            'user_id' => $user->id,
            'user_email' => $user->email
        ]);

        if ($response->status() === 200) {
            return ['success' => true];
        }

        return ['success' => false];
    }

    public function getOrderHistory($userId)
    {
        $orders = Order::where('user_id', $userId)->get();

        $result = [];
        foreach ($orders as $order) {
            $items = [];
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                $items[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price
                ];
            }

            $result[] = [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'items' => $items,
                'date' => $order->created_at
            ];
        }

        return $result;
    }
}
```

### Refactoring Prompt

```
I need to refactor this Order service systematically.

Analysis Request:

For this service:
[PASTE SERVICE CODE]

Analyze:
1. Code quality issues
2. Performance problems
3. Testability issues
4. SOLID principle violations
5. Security concerns

For each issue:
- Explain the problem
- Show impact at scale (1M+ orders)
- Suggest how to fix
- Estimate refactoring effort

Prioritize by business impact first.
```

### Following the AI Guidance

**After getting analysis, ask for a plan:**

```
Based on your analysis of the issues:

Create a refactoring plan where I:
1. Start with high-impact fixes
2. Introduce proper patterns
3. Improve testability
4. Add proper error handling
5. Ensure backward compatibility

For each step:
- What to do
- Why it matters
- Estimated time
- Test cases needed
- How to verify

The result should be production-ready code.
```

### Implementing Step by Step

**For each step, ask for specific help:**

```
Step 1: Separate validation logic

Current code mixes:
- User validation
- Product validation
- Stock validation
- Price calculation

Create:
1. Validation classes for each concern
2. Clear error messages
3. Early validation before transaction
4. Tests for each validator

Show me the refactored code for this step only.
```

---

## Refactoring Patterns

### Pattern 1: Extract Classes

**Before:**
```php
class UserService {
    public function createUser($data) {
        // Validation (20 lines)
        // Create user (5 lines)
        // Send email (10 lines)
        // Log (5 lines)
        // Update cache (5 lines)
    }
}
```

**After:**
```php
class UserValidator { }
class UserRepository { }
class UserNotifier { }
class UserLogger { }
class UserCache { }

class UserService {
    public function __construct(
        UserValidator $validator,
        UserRepository $repository,
        UserNotifier $notifier,
        UserLogger $logger,
        UserCache $cache
    ) {}

    public function createUser($data) {
        // Each line delegates to appropriate class
    }
}
```

### Pattern 2: Extract Methods

**Before:**
```php
public function process($data) {
    // Validation - 10 lines
    // Calculation - 15 lines
    // Storage - 10 lines
    // Notification - 10 lines
    // Logging - 5 lines
}
```

**After:**
```php
public function process($data) {
    $validated = $this->validate($data);
    $result = $this->calculate($validated);
    $this->store($result);
    $this->notify($result);
    $this->log($result);
}

private function validate($data) { }
private function calculate($data) { }
private function store($data) { }
private function notify($data) { }
private function log($data) { }
```

### Pattern 3: Use Repositories

```php
// Before: Direct DB queries in service
class OrderService {
    public function getOrders($userId) {
        return Order::where('user_id', $userId)->with('items')->get();
    }
}

// After: Delegate to repository
class OrderService {
    public function __construct(private OrderRepository $repository) {}

    public function getOrders($userId) {
        return $this->repository->getUserOrders($userId);
    }
}
```

---

## Testing During Refactoring

Create tests as you refactor:

```
Step [N] Refactoring: [DESCRIPTION]

Write tests that:
1. Verify the current behavior (before refactoring)
2. Allow safe refactoring
3. Verify new behavior matches old behavior
4. Test new functionality separately

Show me:
1. The test class
2. Tests for happy path
3. Tests for edge cases
4. Tests for errors
```

---

## Refactoring Checklist

- [ ] Analyze code for issues
- [ ] Create refactoring plan
- [ ] Write tests for current behavior
- [ ] Refactor one step at a time
- [ ] Run tests after each step
- [ ] Verify functionality unchanged
- [ ] Review code quality improvements
- [ ] Identify new patterns used
- [ ] Document lessons learned
- [ ] Compare before/after metrics

---

## Metrics to Track

**Code Quality:**
- Lines per method (target: < 20)
- Methods per class (target: < 10)
- Cyclomatic complexity (target: < 5)
- Code duplication (target: < 5%)

**Performance:**
- Database queries (fewer)
- Memory usage (lower)
- Response time (faster)
- Caching effectiveness

**Maintainability:**
- Test coverage (target: > 80%)
- Documentation quality
- Code clarity (Readability score)
- Architecture adherence

---

## Real-World Refactoring

### Scenario 1: Legacy Controller
```
I have a 300-line controller that:
- Handles 15 different actions
- Has repeated validation
- Mixes concerns
- Hard to test

Show me how to:
1. Extract validation
2. Create service layer
3. Improve structure
4. Add tests

[PASTE CONTROLLER]
```

### Scenario 2: Slow Database Queries
```
User list endpoint is slow:
- 100 queries for 100 users (N+1)
- Returns too much data
- No pagination

Refactor for:
1. Minimal queries
2. Selective columns
3. Pagination
4. Caching

Current code:
[PASTE CODE]
```

### Scenario 3: Mixed Responsibilities
```
This class does too much:
[PASTE CODE]

Help me:
1. Identify responsibilities
2. Extract into separate classes
3. Use dependency injection
4. Maintain backward compatibility

This is used by 10+ controllers.
```

---

## Best Practices

1. **Test First**: Write tests before refactoring
2. **Small Steps**: Refactor one thing at a time
3. **Verify Often**: Run tests after each change
4. **Document**: Explain why, not just what
5. **Measure**: Track quality improvements
6. **Get Review**: Have AI or peers review changes
7. **Learn**: Understand the patterns you're using

---

## Checklist

- [ ] Choose code to refactor
- [ ] Get AI analysis
- [ ] Create refactoring plan
- [ ] Write baseline tests
- [ ] Implement step 1, run tests
- [ ] Implement step 2, run tests
- [ ] Continue for all steps
- [ ] Verify quality improvements
- [ ] Document what you learned
- [ ] Commit changes with clear messages

---

## Bonus Challenges

1. Refactor your largest class
2. Extract a service from a controller
3. Apply SOLID principles systematically
4. Reduce code complexity by 50%
5. Improve test coverage to 80%+
6. Reduce database queries by 80%
