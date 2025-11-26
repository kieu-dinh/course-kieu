# Lesson 5 - Code Review with AI

**Duration**: 3-4 hours
**Prerequisites**: Lessons 1-4

---

## Introduction

Code review is one of the most valuable practices in software development. It catches bugs, improves quality, spreads knowledge, and ensures consistency. But thorough code review is time-consuming, and as a solo developer or small team, you might not always have someone to review your code.

This is where AI shines. AI can provide instant, detailed code reviews covering:
- Security vulnerabilities
- Performance issues
- Best practice violations
- Potential bugs
- Code style inconsistencies
- Architecture improvements

However, AI code review has limitations. It doesn't understand your business context, team standards, or project goals. The key is using AI as a first pass, then applying your own judgment.

In this lesson, you'll learn to use AI for effective code reviews that catch issues early and improve your code quality.

---

## Why AI Code Review?

### Traditional Code Review Challenges

**Without Code Review:**
- Bugs reach production
- Inconsistent code style
- Knowledge silos
- Technical debt accumulates

**With Human Review (ideal but expensive):**
- Time-intensive (30-60 min per review)
- Requires another developer
- Availability issues
- Can be superficial when rushed

**With AI Review:**
- Instant feedback (seconds)
- Available 24/7
- Consistent standards
- Catches common issues
- Free up humans for high-level review

**Best Practice:** Use AI for first pass, human for final review.

---

## What AI is Good at Reviewing

### High Effectiveness Areas

**1. Security Vulnerabilities**
- SQL injection points
- XSS vulnerabilities
- CSRF missing protection
- Unvalidated user input
- Exposed sensitive data
- Authentication/authorization issues

**2. Performance Issues**
- N+1 queries
- Missing eager loading
- Inefficient loops
- Missing database indexes
- Unnecessary queries
- Memory leaks

**3. Code Style**
- PSR-12 compliance
- Naming conventions
- Missing type hints
- Missing return types
- Inconsistent formatting

**4. Common Bugs**
- Null pointer exceptions
- Type mismatches
- Logic errors
- Missing error handling
- Off-by-one errors

**5. Laravel Best Practices**
- Not using facades correctly
- Not following conventions
- Missing validation
- Improper relationship definitions
- Not using built-in features

---

### Low Effectiveness Areas

**1. Business Logic**
AI doesn't understand your domain rules

**2. Architecture Decisions**
AI can suggest patterns but can't judge appropriateness for your context

**3. User Experience**
AI can't evaluate if the functionality makes sense to users

**4. Team Standards**
AI doesn't know your specific conventions unless you tell it

**5. Project Context**
AI doesn't know why certain decisions were made

---

## AI Code Review Workflow

### Step 1: Prepare the Code

Before requesting review:

```
1. Complete your implementation
2. Run tests locally
3. Check obvious issues yourself
4. Format code properly
5. Add basic comments for complex logic
```

**Don't submit:**
- Incomplete code
- Code that doesn't run
- Code without context

---

### Step 2: Provide Context

AI needs context to give relevant feedback:

**Minimum Context:**
```
Framework: Laravel 11, PHP 8.2
Purpose: [What this code does]
Focus: [What to review for]
```

**Ideal Context:**
```
Framework: Laravel 11, PHP 8.2
Purpose: [Detailed description]
Focus: Security, performance, best practices
Related: [Other relevant files/code]
Constraints: [Any specific requirements]
Standards: [Your team's standards]
```

---

### Step 3: Request Specific Review

Be specific about what you want reviewed:

**❌ Vague Request:**
```
Review this code
[paste code]
```

**✅ Specific Request:**
```
Review this Laravel controller for:
1. Security vulnerabilities (SQL injection, XSS, CSRF)
2. N+1 query issues
3. Laravel best practices
4. Error handling
5. Code style (PSR-12)

[paste code]

Context:
- Public-facing blog system
- Handles user-generated content
- High traffic expected
```

---

### Step 4: Analyze AI Feedback

AI will typically categorize issues:

**Critical (Fix Immediately):**
- Security vulnerabilities
- Data corruption risks
- System crashes

**Important (Fix Before Merge):**
- Performance issues
- Logic bugs
- Best practice violations

**Minor (Fix When Convenient):**
- Style issues
- Minor optimizations
- Suggestions for improvement

**Evaluate each suggestion:**
- [ ] Is this actually an issue?
- [ ] Does it apply to my context?
- [ ] What's the priority?
- [ ] How complex is the fix?

---

### Step 5: Apply Improvements

**Don't blindly apply all suggestions**

For each suggestion:
```
1. Understand why it's suggested
2. Evaluate if it fits your context
3. Consider side effects
4. Test the change
5. Verify it actually improves things
```

---

## Code Review Prompt Templates

### Template 1: Security Review

```
Perform a security review of this Laravel code:

[paste code]

Check for:
- SQL injection vulnerabilities
- XSS attack vectors
- CSRF protection
- Authentication bypass
- Authorization issues
- Exposed sensitive data
- Unvalidated user input
- Mass assignment vulnerabilities
- Insecure file uploads
- Session security

Context:
- [Public/internal/admin] facing
- Handles [type of data]
- User roles: [roles]

For each issue found:
1. Describe the vulnerability
2. Explain the exploit scenario
3. Provide secure fix
4. Explain why fix works
```

---

### Template 2: Performance Review

```
Review this Laravel code for performance:

[paste code]

Check for:
- N+1 query problems
- Missing eager loading
- Inefficient database queries
- Missing indexes
- Unnecessary loops
- Memory inefficiency
- Missing caching opportunities
- Redundant computations

Context:
- Expected traffic: [volume]
- Database size: [records]
- Performance target: [time]

For each issue:
1. Identify bottleneck
2. Measure impact
3. Suggest optimization
4. Show expected improvement
```

---

### Template 3: Best Practices Review

```
Review this Laravel 11 code for best practices:

[paste code]

Check for:
- Laravel conventions adherence
- SOLID principles
- DRY principle violations
- Code organization
- Proper use of Laravel features
- Service/repository pattern usage
- Dependency injection
- Type hints and return types
- Error handling
- Testability

Standards:
- Follow PSR-12
- Type hint everything
- Use form requests for validation
- Service layer for business logic
- Policies for authorization

Suggest improvements with explanations.
```

---

### Template 4: Complete Code Review

```
Comprehensive code review of this Laravel controller:

[paste code]

Review for:
1. **Security** - vulnerabilities, exploits
2. **Performance** - queries, memory, speed
3. **Correctness** - logic, edge cases, bugs
4. **Best Practices** - Laravel conventions, SOLID
5. **Maintainability** - readability, organization
6. **Testability** - easy to test, dependencies

Context:
- Framework: Laravel 11, PHP 8.2
- Purpose: [description]
- User roles: [roles]
- Expected usage: [volume/frequency]

Provide:
- Issue severity (Critical/Important/Minor)
- Specific line numbers
- Clear explanations
- Code examples for fixes
- Prioritized action items
```

---

## Real-World Review Examples

### Example 1: Controller Review

**Code to Review:**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();

        foreach ($posts as $post) {
            $post->author_name = User::find($post->user_id)->name;
        }

        return view('posts.index', compact('posts'));
    }

    public function show($id)
    {
        $post = Post::where('id', $id)->first();
        return view('posts.show', compact('post'));
    }

    public function store(Request $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->body = $request->body;
        $post->user_id = $request->user()->id;
        $post->save();

        return redirect('/posts');
    }

    public function destroy($id)
    {
        Post::where('id', $id)->delete();
        return back();
    }
}
```

**Review Prompt:**
```
Review this PostController for:
- Security issues
- Performance problems
- Laravel best practices
- Potential bugs

Context: Laravel 11, public blog, ~1000 posts
```

**Expected AI Response (Example):**
```markdown
## Critical Issues

### 1. No Authentication/Authorization (Line 8, 15, 22, 31)
**Severity:** Critical

All methods lack authentication and authorization checks.

**Issues:**
- Anyone can create posts (line 22)
- Anyone can delete any post (line 31)
- No ownership verification

**Fix:**
```php
// Add middleware
public function __construct()
{
    $this->middleware('auth')->except(['index', 'show']);
}

// Add authorization check in destroy
public function destroy(Post $post)
{
    $this->authorize('delete', $post);
    $post->delete();
    return back();
}
```

**Create Policy:**
```php
php artisan make:policy PostPolicy

// PostPolicy
public function delete(User $user, Post $post)
{
    return $user->id === $post->user_id;
}
```

---

### 2. SQL Injection Risk (Line 15)
**Severity:** Critical

Using unsanitized $id directly in query.

**Issue:**
```php
$post = Post::where('id', $id)->first();
```

While Eloquent prevents SQL injection, it's better practice to use route model binding.

**Fix:**
```php
// Route
Route::get('/posts/{post}', [PostController::class, 'show']);

// Controller
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
```

---

### 3. No Input Validation (Line 22)
**Severity:** Critical

No validation before creating post.

**Issues:**
- Missing CSRF protection
- No validation rules
- Could save invalid data
- XSS vulnerability (unescaped HTML in body)

**Fix:**
```php
// Create Form Request
php artisan make:request StorePostRequest

// StorePostRequest
public function authorize()
{
    return auth()->check();
}

public function rules()
{
    return [
        'title' => 'required|string|max:200',
        'body' => 'required|string|max:10000',
    ];
}

// Controller
public function store(StorePostRequest $request)
{
    $post = Post::create([
        'title' => $request->validated('title'),
        'body' => $request->validated('body'),
        'user_id' => auth()->id(),
    ]);

    return redirect()->route('posts.show', $post);
}
```

---

## Important Issues

### 4. N+1 Query Problem (Line 10-12)
**Severity:** Important

Loading authors in loop causes N+1 queries.

**Issue:**
```php
foreach ($posts as $post) {
    $post->author_name = User::find($post->user_id)->name;
}
```

With 1000 posts: 1 query for posts + 1000 queries for users = 1001 queries!

**Fix:**
```php
public function index()
{
    $posts = Post::with('user')
        ->latest()
        ->paginate(15);

    return view('posts.index', compact('posts'));
}

// In view, use: $post->user->name
```

**Performance Impact:**
- Before: 1001 queries, ~5-10 seconds
- After: 2 queries, <100ms

---

### 5. Loading All Records (Line 8)
**Severity:** Important

Using `Post::all()` loads all posts into memory.

**Issues:**
- Memory issues with large datasets
- Slow page load
- No pagination

**Fix:**
```php
public function index()
{
    $posts = Post::with('user')
        ->latest()
        ->paginate(15);

    return view('posts.index', compact('posts'));
}
```

---

### 6. Missing Error Handling (Line 15, 31)
**Severity:** Important

No checks if post exists.

**Issue:**
```php
$post = Post::where('id', $id)->first();
// Could be null!
return view('posts.show', compact('post'));
```

**Fix:**
```php
public function show(Post $post)
{
    return view('posts.show', compact('post'));
}
// Laravel automatically returns 404 if not found
```

---

## Minor Issues

### 7. Not Following Conventions (Multiple)
**Severity:** Minor

- Not using route model binding
- Not using form requests
- Missing type hints
- Missing return types
- Not using named routes

**Fix:**
```php
public function index(): View
{
    $posts = Post::with('user')
        ->latest()
        ->paginate(15);

    return view('posts.index', compact('posts'));
}

public function show(Post $post): View
{
    return view('posts.show', compact('post'));
}

public function store(StorePostRequest $request): RedirectResponse
{
    $post = Post::create([
        'title' => $request->validated('title'),
        'body' => $request->validated('body'),
        'user_id' => auth()->id(),
    ]);

    return redirect()->route('posts.show', $post)
        ->with('success', 'Post created successfully!');
}

public function destroy(Post $post): RedirectResponse
{
    $this->authorize('delete', $post);

    $post->delete();

    return redirect()->route('posts.index')
        ->with('success', 'Post deleted successfully!');
}
```

---

## Summary of Action Items

**Must Fix Before Deployment:**
1. ✅ Add authentication and authorization
2. ✅ Add input validation
3. ✅ Fix N+1 query
4. ✅ Add pagination

**Should Fix:**
5. ✅ Use route model binding
6. ✅ Add type hints and return types
7. ✅ Follow Laravel conventions
8. ✅ Add flash messages

**Refactored Controller:**
```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index(): View
    {
        $posts = Post::with('user')
            ->latest()
            ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post): View
    {
        return view('posts.show', compact('post'));
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'Post created successfully!');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', 'Post deleted successfully!');
    }
}
```
```

---

### Example 2: Service Class Review

**Code to Review:**
```php
<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

class OrderService
{
    public function createOrder($userId, $items)
    {
        $total = 0;
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => $userId,
            'total' => $total,
            'status' => 'pending'
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => Product::find($item['product_id'])->price
            ]);
        }

        return $order;
    }
}
```

**Review Prompt:**
```
Review this OrderService for:
- Race conditions
- Performance issues
- Error handling
- Business logic bugs
- Best practices

Context: E-commerce, high traffic, inventory tracking
```

**Expected Issues AI Would Find:**
1. No database transaction (partial orders possible)
2. No stock checking
3. Race condition on inventory
4. N+1 queries loading products
5. No validation
6. Price could change between calculation and saving
7. No error handling
8. Missing return type hints

---

## Reviewing Different Code Types

### 1. Models

**Focus Areas:**
- Relationship definitions
- Fillable/guarded properties
- Casts configuration
- Scope definitions
- Accessor/mutator patterns
- Mass assignment protection

**Review Prompt:**
```
Review this Laravel model:

[paste model]

Check for:
- Correct relationship definitions
- Mass assignment protection
- Proper casts
- Scope implementations
- Accessor/mutator best practices
- Missing indexes (if relationships used)
```

---

### 2. Migrations

**Focus Areas:**
- Correct column types
- Foreign key constraints
- Indexes on foreign keys
- Unique constraints
- Default values
- Nullable fields

**Review Prompt:**
```
Review this migration:

[paste migration]

Check for:
- Appropriate column types
- Missing indexes
- Foreign key constraints
- Data integrity constraints
- Rollback functionality
```

---

### 3. API Controllers

**Focus Areas:**
- Input validation
- Authentication/authorization
- API resource usage
- Proper HTTP status codes
- Error responses
- Rate limiting

**Review Prompt:**
```
Review this API controller:

[paste controller]

Check for:
- Authentication/authorization
- Input validation
- Proper HTTP status codes
- Error handling
- API resource transformation
- Rate limiting
- CORS configuration
```

---

### 4. Blade Templates

**Focus Areas:**
- XSS protection
- Proper escaping
- CSRF tokens
- Accessibility
- Conditional logic complexity
- Component usage

**Review Prompt:**
```
Review this Blade template:

[paste blade]

Check for:
- XSS vulnerabilities
- Proper escaping ({{ }} vs {!! !!})
- CSRF token on forms
- Accessibility (ARIA labels, semantic HTML)
- Overly complex logic (should be in controller)
```

---

## Specialized Reviews

### Security-Focused Review

**Comprehensive Security Checklist:**

```
Review this code for security vulnerabilities:

[paste code]

Check for:
1. **Injection Attacks**
   - SQL injection
   - Command injection
   - LDAP injection

2. **Cross-Site Scripting (XSS)**
   - Stored XSS
   - Reflected XSS
   - DOM-based XSS

3. **Cross-Site Request Forgery (CSRF)**
   - Missing CSRF tokens
   - State-changing GET requests

4. **Authentication Issues**
   - Weak password requirements
   - Missing rate limiting
   - Insecure session management
   - Missing authentication checks

5. **Authorization Issues**
   - Missing authorization checks
   - Insecure direct object references
   - Privilege escalation

6. **Data Exposure**
   - Sensitive data in logs
   - Exposed API keys
   - Debug info in production

7. **File Upload Issues**
   - Missing file type validation
   - No file size limits
   - Executable file uploads

For each issue:
- Severity rating
- Exploit scenario
- Secure fix with code
```

---

### Performance-Focused Review

```
Performance audit of this Laravel code:

[paste code]

Analyze:
1. **Database Queries**
   - Count queries
   - Identify N+1 issues
   - Missing eager loading
   - Inefficient queries
   - Missing indexes

2. **Memory Usage**
   - Loading too much data
   - Memory leaks
   - Inefficient data structures

3. **Algorithmic Complexity**
   - Nested loops
   - Inefficient algorithms
   - Redundant computations

4. **Caching Opportunities**
   - Repeated queries
   - Expensive computations
   - Static data not cached

5. **Optimization Suggestions**
   - Specific improvements
   - Expected performance gain
   - Implementation difficulty

Provide metrics:
- Current estimated performance
- Optimized estimated performance
- Priority of each fix
```

---

## Self-Review with AI

Before submitting code for review by others, use AI for self-review:

### Self-Review Workflow

```
1. Write your code
   ↓
2. Run tests locally
   ↓
3. AI security review
   ↓
4. Fix critical issues
   ↓
5. AI performance review
   ↓
6. Fix important issues
   ↓
7. AI best practices review
   ↓
8. Fix minor issues
   ↓
9. Final manual check
   ↓
10. Submit for human review (if available)
```

This catches 80% of issues before human review, making reviews faster and more focused on architecture and business logic.

---

## Building a Review Checklist

Create your personal code review checklist:

### Laravel Controller Checklist

```markdown
## Pre-Submission Checklist

### Security
- [ ] Authentication required where needed
- [ ] Authorization checks present
- [ ] Input validated (Form Request)
- [ ] CSRF protection on state-changing operations
- [ ] No SQL injection possibilities
- [ ] No XSS vulnerabilities

### Performance
- [ ] No N+1 queries
- [ ] Eager loading used
- [ ] Pagination for lists
- [ ] Appropriate caching

### Best Practices
- [ ] Type hints on all parameters
- [ ] Return types specified
- [ ] Route model binding used
- [ ] Named routes used
- [ ] Flash messages for feedback
- [ ] Follows Laravel conventions

### Error Handling
- [ ] 404 handling
- [ ] Validation error handling
- [ ] Database error handling
- [ ] Proper error responses

### Testing
- [ ] Unit tests written
- [ ] Feature tests written
- [ ] All tests passing
- [ ] Edge cases covered
```

---

## Limitations of AI Code Review

### What AI Misses

**1. Business Logic Correctness**
```php
// AI can't tell if this discount calculation is correct for YOUR business
public function calculateDiscount($total) {
    if ($total > 1000) {
        return $total * 0.15; // Is 15% correct? AI doesn't know
    }
    return 0;
}
```

**2. Architecture Appropriateness**
```php
// AI suggests service layer
// But maybe you deliberately kept it simple for this small feature
// Context matters!
```

**3. Team Standards**
```php
// Your team might have specific naming conventions
// AI doesn't know unless you tell it
```

**4. Business Context**
```php
// AI might suggest caching
// But maybe you need real-time data for regulatory reasons
```

---

## Exercises

### Exercise 1: Security Review (45 minutes)

1. Write a vulnerable user registration controller
2. Request AI security review
3. Compare what AI finds vs. what you intentionally added
4. Did AI catch everything?
5. What did it miss?

### Exercise 2: Performance Review (60 minutes)

1. Write code with multiple performance issues:
   - N+1 queries
   - Missing pagination
   - No caching
   - Inefficient loops
2. Request AI performance review
3. Apply suggested optimizations
4. Measure before/after performance

### Exercise 3: Complete Review (90 minutes)

1. Take a controller from your past project
2. Request comprehensive AI review
3. Fix all critical and important issues
4. Re-submit for review
5. Compare: before vs. after code quality

---

## Summary

**Key Takeaways:**

1. **AI Excels At:**
   - Security vulnerability detection
   - Performance issue identification
   - Best practice enforcement
   - Code style consistency

2. **AI Struggles With:**
   - Business logic correctness
   - Architecture appropriateness
   - Team-specific conventions
   - Project context

3. **Effective Review Process:**
   - Provide clear context
   - Request specific focus areas
   - Evaluate suggestions critically
   - Test all changes
   - Learn from feedback

4. **Best Practices:**
   - Use AI for first-pass review
   - Always understand suggestions
   - Never blindly apply fixes
   - Build your review checklist
   - Human review for final approval

In the next lesson, we'll explore **refactoring with AI** - how to systematically improve existing code with AI assistance.

---

**Next Lesson**: [06-refactoring-ai.md](./06-refactoring-ai.md) - Refactor code systematically with AI help.
