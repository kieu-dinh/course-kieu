# Exercise 18.5 - Test-Driven Development (TDD)

## Objective

Practice Test-Driven Development (TDD) by writing tests first, then implementing features to pass those tests.

---

## Task

Build a new feature using TDD approach: User favorites/likes system for items.

### Requirements

**Feature Requirements:**
- Users can mark items as favorites (like/unlike)
- Users can view their favorite items
- Show count of favorites on each item
- A user can only favorite an item once

**TDD Process:**
1. Write failing tests for the feature
2. Write minimal code to make tests pass
3. Refactor for cleaner code

---

## Step 1: Plan the Feature

Before writing tests, plan what you need:

```
Models:
- User (already exists in Laravel)
- Item (from previous exercises)

Relationships:
- Item hasMany favorites
- User hasMany favorites (through likes/favorites table)

API Endpoints:
- POST /api/items/{id}/favorite (add favorite)
- DELETE /api/items/{id}/favorite (remove favorite)
- GET /api/users/me/favorites (list user's favorites)

Database:
- Create pivot table: user_item (user_id, item_id)
```

---

## Step 2: Create Migration and Model Tests

```bash
php artisan make:migration create_favorites_table
php artisan make:test FavoriteFeatureTest
```

```php
// tests/Feature/FavoriteFeatureTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FavoriteFeatureTest extends TestCase
{
    use RefreshDatabase;

    // TEST 1: User can favorite an item
    public function test_user_can_favorite_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
                        ->postJson("/api/items/{$item->id}/favorite");

        $response->assertStatus(200);
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);
    }

    // TEST 2: User can unfavorite an item
    public function test_user_can_unfavorite_item()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // First favorite
        $user->favorites()->attach($item);

        // Then unfavorite
        $response = $this->actingAs($user)
                        ->deleteJson("/api/items/{$item->id}/favorite");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'item_id' => $item->id
        ]);
    }

    // TEST 3: User cannot favorite same item twice
    public function test_user_cannot_favorite_same_item_twice()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user)
             ->postJson("/api/items/{$item->id}/favorite");

        $response = $this->actingAs($user)
                        ->postJson("/api/items/{$item->id}/favorite");

        $response->assertStatus(422);
    }

    // TEST 4: Item shows count of favorites
    public function test_item_shows_favorite_count()
    {
        $item = Item::factory()->create();
        User::factory(3)->create()->each(function($user) use ($item) {
            $user->favorites()->attach($item);
        });

        $response = $this->getJson("/api/items/{$item->id}");

        $response->assertJsonPath('data.favorites_count', 3);
    }

    // TEST 5: User can list their favorites
    public function test_user_can_list_their_favorites()
    {
        $user = User::factory()->create();
        $items = Item::factory(3)->create();

        $items->each(function($item) use ($user) {
            $user->favorites()->attach($item);
        });

        $response = $this->actingAs($user)
                        ->getJson('/api/users/me/favorites');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }
}
```

---

## Step 3: Create Migration

```php
// database/migrations/YYYY_MM_DD_create_favorites_table.php
Schema::create('favorites', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('item_id')->constrained()->onDelete('cascade');
    $table->timestamps();
    $table->unique(['user_id', 'item_id']); // Prevent duplicates
});
```

---

## Step 4: Implement the Feature

Only write code needed to pass tests:

```php
// app/Models/User.php
public function favorites()
{
    return $this->belongsToMany(Item::class, 'favorites');
}

// app/Models/Item.php
public function favoritedBy()
{
    return $this->belongsToMany(User::class, 'favorites');
}

public function getFavoritesCountAttribute()
{
    return $this->favoritedBy()->count();
}

// app/Http/Controllers/FavoriteController.php
namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function store(Item $item)
    {
        $user = auth()->user();

        if ($user->favorites()->where('item_id', $item->id)->exists()) {
            return response()->json(['message' => 'Already favorited'], 422);
        }

        $user->favorites()->attach($item);

        return response()->json(['message' => 'Added to favorites']);
    }

    public function destroy(Item $item)
    {
        auth()->user()->favorites()->detach($item);

        return response()->json(['message' => 'Removed from favorites']);
    }
}

// routes/api.php
Route::post('/items/{item}/favorite', [FavoriteController::class, 'store']);
Route::delete('/items/{item}/favorite', [FavoriteController::class, 'destroy']);
Route::get('/users/me/favorites', [UserController::class, 'favorites'])->middleware('auth:sanctum');
```

---

## Step 5: Run Tests

```bash
# Run tests
php artisan test tests/Feature/FavoriteFeatureTest.php

# Watch tests (with --parallel if available)
php artisan test --watch
```

---

## TDD Cycle

**Red → Green → Refactor**

1. Write test (Red - test fails)
2. Write minimal code to pass test (Green - test passes)
3. Refactor code to be cleaner (Green - test still passes)

Repeat until feature is complete.

---

## Bonus Challenges

1. Add test for users can only access their own favorites
2. Add test for listing items with favorite status for current user
3. Add test for most favorited items
4. Add soft deletes to keep favorite history
5. Add event listener for when item is favorited
6. Write tests for permission checks (only auth users can favorite)

---

## Checklist

- [ ] Migration created
- [ ] All tests written before implementation
- [ ] Tests failing initially (Red)
- [ ] Implementation code written (Green)
- [ ] All tests passing
- [ ] Code refactored for clarity
- [ ] Feature works end-to-end
- [ ] Database tests verify changes

---

## Solution Check

Run the full test suite:
```bash
php artisan test tests/Feature/FavoriteFeatureTest.php
```

Expected:
```
✓ test_user_can_favorite_item
✓ test_user_can_unfavorite_item
✓ test_user_cannot_favorite_same_item_twice
✓ test_item_shows_favorite_count
✓ test_user_can_list_their_favorites

5 passed
```

All tests should pass and feature should work via API.
