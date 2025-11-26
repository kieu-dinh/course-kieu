# Lesson 4 - AI-Assisted Debugging

**Duration**: 3-4 hours
**Prerequisites**: Lessons 1-3

---

## Introduction

Debugging is where developers spend 30-50% of their time. A bug that takes 2 hours to find manually might take 5 minutes with AI assistance. But there's an art to it - AI can help you debug faster, but only if you know how to ask the right questions and interpret its suggestions.

In this lesson, you'll learn:
- How to use AI to identify bugs quickly
- Effective debugging prompts and techniques
- How to interpret error messages with AI
- When AI helps vs. when it misleads
- Building a debugging workflow with AI

Remember: AI accelerates debugging, but your fundamental understanding (from Modules 01-20) helps you verify the fix and understand why it works.

---

## The Traditional Debugging Process

Before AI, debugging looked like this:

```
1. Notice bug (5 minutes)
   ↓
2. Reproduce bug (10 minutes)
   ↓
3. Read error message (5 minutes)
   ↓
4. Search Stack Overflow (15 minutes)
   ↓
5. Read 10 similar issues (20 minutes)
   ↓
6. Try solution 1 (fails) (10 minutes)
   ↓
7. Try solution 2 (fails) (10 minutes)
   ↓
8. Try solution 3 (works!) (10 minutes)
   ↓
9. Understand why (15 minutes)
   ↓
Total: ~100 minutes
```

With AI:

```
1. Notice bug (5 minutes)
   ↓
2. Reproduce bug (10 minutes)
   ↓
3. Ask AI with error + context (2 minutes)
   ↓
4. Get explanation + solution (1 minute)
   ↓
5. Apply fix (5 minutes)
   ↓
6. Understand why (10 minutes)
   ↓
Total: ~33 minutes
```

That's a 3x speedup! But only if you do it right.

---

## Types of Bugs and AI Effectiveness

### High AI Effectiveness (90%+ success rate)

**1. Syntax Errors**
```php
// Error: syntax error, unexpected '=>'
public function index()
{
    $posts = Post::where('status' => 'published')->get();
}
```

AI immediately spots: Should be comma, not `=>`

**2. Common Framework Errors**
```php
// Error: Call to undefined method illuminate\database\eloquent\collection::paginate()
$posts = Post::where('published', true)->get()->paginate(10);
```

AI knows: Call `paginate()` on query builder, not collection

**3. Type Errors**
```php
// Error: argument 1 must be string, array given
echo strlen(['foo', 'bar']);
```

AI spots: Wrong data type passed

**4. Missing Dependencies**
```
Error: Class 'Intervention\Image\Facades\Image' not found
```

AI suggests: Install package, check config, check import

---

### Medium AI Effectiveness (60-80% success rate)

**1. Logic Errors**
```php
// Bug: Always returns 0
public function calculateDiscount($price, $percent) {
    return $price * $percent / 100;
}

calculateDiscount(100, 20); // Should be 20, returns 0.2
```

AI can spot the logic issue with context

**2. Query Issues**
```php
// Bug: N+1 query problem
foreach ($posts as $post) {
    echo $post->author->name; // Queries author each iteration
}
```

AI suggests eager loading

**3. Race Conditions**
```php
// Bug: Concurrent requests cause duplicate entries
if (!Post::where('slug', $slug)->exists()) {
    Post::create(['slug' => $slug, ...]);
}
```

AI suggests unique database constraint or locking

---

### Low AI Effectiveness (20-40% success rate)

**1. Business Logic Bugs**
```php
// Bug: Incorrect commission calculation for specific cases
// (Your domain-specific rules)
```

AI doesn't understand your business rules

**2. Complex Race Conditions**
```php
// Bug: Only occurs under high load with specific timing
```

AI can suggest approaches but can't reproduce

**3. Environment-Specific Issues**
```
// Bug: Works locally, fails in production
```

AI needs detailed environment info

**4. Integration Bugs**
```php
// Bug: Third-party API returns unexpected data format
```

AI needs API documentation context

---

## Effective Debugging Prompts

### The Essential Information Framework

Every debugging prompt should include:

1. **The Error**: Exact error message
2. **The Code**: Relevant code causing error
3. **The Context**: What you're trying to do
4. **The Environment**: Framework version, PHP version, etc.
5. **What You Tried**: Previous attempts (if any)

---

### Template 1: Syntax/Runtime Error

```
Error Message:
[paste exact error with stack trace]

Code:
[paste relevant code, 10-20 lines with context]

Context:
- Laravel 11, PHP 8.2
- Trying to [what you're doing]
- This code is in [controller/model/service]

What I tried:
- [attempt 1] - didn't work because...
- [attempt 2] - didn't work because...

What's causing this error and how do I fix it?
```

**Example:**

```
Error Message:
Illuminate\Database\QueryException
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'published_at' in 'where clause'
SQL: select * from `posts` where `published_at` is not null

Code:
public function index() {
    $posts = Post::published()->paginate(15);
    return view('posts.index', compact('posts'));
}

// Post model scope:
public function scopePublished($query) {
    return $query->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
}

Context:
- Laravel 11, PHP 8.2
- Trying to show only published posts
- This is in PostController
- Migration was run successfully
- Table exists in database

What I tried:
- Ran php artisan migrate:fresh - still same error
- Checked table structure - column exists
- Cleared cache - didn't help

What's causing this error and how do I fix it?
```

---

### Template 2: Logic Error

```
Expected Behavior:
[what should happen]

Actual Behavior:
[what actually happens]

Code:
[paste relevant code]

Context:
- [framework, version]
- [relevant models/relationships]
- [sample data that causes issue]

Test Case:
Input: [sample input]
Expected: [expected output]
Actual: [actual output]

Why is this happening and how do I fix it?
```

**Example:**

```
Expected Behavior:
User's cart total should be $85 (100 - 15% discount)

Actual Behavior:
Cart total shows $100 (no discount applied)

Code:
public function calculateTotal() {
    $subtotal = $this->items->sum(fn($item) => $item->price * $item->quantity);
    $discount = $this->getDiscount();
    $total = $subtotal - ($subtotal * $discount);
    return $total;
}

public function getDiscount() {
    if ($this->user->is_premium) {
        return 0.15; // 15%
    }
    return 0;
}

Context:
- Laravel 11
- User is premium (verified in database)
- Cart has one item: price 100, quantity 1
- getDiscount() returns 0.15 (correct)

Test Case:
$cart = Cart::where('user_id', 1)->first();
$cart->user->is_premium; // true
$cart->getDiscount(); // 0.15
$cart->calculateTotal(); // 100 (should be 85)

Why is discount not being applied?
```

---

### Template 3: Performance Issue

```
Problem:
[page is slow/query timeout/high memory]

Measurements:
- Current performance: [X seconds/Y MB]
- Expected performance: [X seconds/Y MB]
- Occurs when: [conditions]

Code:
[paste slow code]

Query Log:
[paste relevant queries from debugbar/log]

Context:
- Laravel 11, PHP 8.2
- Database: MySQL 8.0
- Data size: [number of records]

Profile Results:
[paste profiler output if available]

How can I optimize this?
```

---

### Template 4: Unexpected Behavior

```
What I Expected:
[clear description]

What Actually Happens:
[clear description]

Steps to Reproduce:
1. [step 1]
2. [step 2]
3. [step 3]

Code:
[paste relevant code]

Related Code:
[paste other relevant code]

Context:
- [framework, versions]
- [relevant models/data]

Sample Data:
[provide test data that reproduces issue]

Why is this happening?
```

---

## Debugging Workflow with AI

### Step 1: Reproduce the Bug

Before asking AI anything, ensure you can consistently reproduce the bug:

```
1. Identify exact steps to trigger bug
2. Document conditions (user role, data state, etc.)
3. Verify it happens consistently
4. Get exact error message (if any)
5. Note the expected vs. actual behavior
```

**Why?** AI needs clear, reproducible information. "It sometimes doesn't work" is too vague.

---

### Step 2: Gather Context

Collect all relevant information:

**Code Context:**
```php
// The problematic code
// Related models
// Relevant configuration
// Database schema (if relevant)
```

**Error Context:**
```
// Full error message
// Stack trace
// Error log entries
```

**Environment Context:**
```
- Laravel version
- PHP version
- Package versions
- Database type
- Server environment (local/production)
```

---

### Step 3: Ask AI for Analysis

Submit your well-formatted prompt:

```
[Use one of the templates above]
[Include all gathered context]
[Be specific about what you tried]
```

---

### Step 4: Evaluate AI Response

**Critical Analysis Checklist:**

- [ ] Does the explanation make sense?
- [ ] Does it address the actual error?
- [ ] Is the solution appropriate for your framework version?
- [ ] Are there security implications?
- [ ] Will it cause other issues?
- [ ] Do you understand WHY it fixes the bug?

**Red Flags:**

❌ "Try this, it might work" (vague)
❌ Suggests outdated syntax
❌ Doesn't explain why error occurs
❌ Solution seems too complex for the problem
❌ Suggests dangerous operations (disabling security, etc.)

**Green Flags:**

✅ Explains root cause clearly
✅ Solution is simple and targeted
✅ Uses current framework best practices
✅ Explains why the fix works
✅ Warns about edge cases

---

### Step 5: Apply Fix Carefully

```
1. Understand the fix completely
2. Apply in a test environment first
3. Test the specific bug
4. Test related functionality
5. Check for side effects
6. Run test suite
7. Deploy to production
```

Never blindly copy-paste a fix to production!

---

### Step 6: Learn from the Bug

After fixing, reflect:

```
Questions to ask yourself:
- Why did this bug occur?
- How could I have prevented it?
- What did I learn?
- Should I add a test to prevent regression?
- Are there similar bugs elsewhere?
```

---

## Real-World Debugging Examples

### Example 1: CSRF Token Mismatch

**Error:**
```
419 | Page Expired
CSRF token mismatch
```

**Bad Prompt:**
```
I get 419 error, help
```

**Good Prompt:**
```
Error:
419 Page Expired - CSRF token mismatch
Occurs when submitting login form

Code:
// routes/web.php
Route::post('/login', [AuthController::class, 'login']);

// resources/views/auth/login.blade.php
<form method="POST" action="/login">
    @csrf
    <input type="email" name="email">
    <input type="password" name="password">
    <button type="submit">Login</button>
</form>

// app/Http/Controllers/AuthController.php
public function login(Request $request) {
    $credentials = $request->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return redirect('/dashboard');
    }
    return back()->withErrors(['email' => 'Invalid credentials']);
}

Context:
- Laravel 11, PHP 8.2
- Session driver: file (in .env)
- Happens consistently after leaving form open for >30 minutes
- Happens in production, not locally
- Behind CloudFlare proxy

Config:
// config/session.php
'lifetime' => 120,
'expire_on_close' => false,

Environment:
- Production: Ubuntu 20.04, Nginx
- Local: macOS, Laravel Herd

What's causing this and how do I fix it for production?
```

**AI Response (Example):**
```
The issue is likely related to session timeout combined with CloudFlare.

Root Cause:
1. User opens form
2. Waits >120 minutes (your session lifetime)
3. Session expires, CSRF token becomes invalid
4. User submits form → 419 error

Additional factors with CloudFlare:
- CloudFlare might cache pages with old CSRF tokens
- Trusted proxies not configured correctly

Solutions:

1. Increase session lifetime in production .env:
SESSION_LIFETIME=720  # 12 hours

2. Configure trusted proxies for CloudFlare:
// app/Http/Middleware/TrustProxies.php
protected $proxies = '*';
protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO;

3. Add CSRF token refresh for long forms:
<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
// Refresh token every 60 minutes
setInterval(() => {
    fetch('/refresh-csrf')
        .then(response => response.json())
        .then(data => {
            document.querySelector('meta[name="csrf-token"]').content = data.token;
            document.querySelector('input[name="_token"]').value = data.token;
        });
}, 3600000);
</script>

// Add route in web.php
Route::get('/refresh-csrf', fn() => response()->json(['token' => csrf_token()]));

4. Set proper cache headers to prevent caching login page:
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('cache.headers:no_cache');

Recommended Approach:
- Increase session lifetime to 12 hours
- Configure trusted proxies
- Add token refresh for long forms
- This prevents the issue without breaking CloudFlare compatibility
```

---

### Example 2: N+1 Query Problem

**Symptom:**
```
Posts page loads in 15 seconds with 100 posts
```

**Good Prompt:**
```
Performance Issue:
Posts index page is very slow

Measurements:
- Load time: 15 seconds
- Database queries: 301 queries (from Laravel Debugbar)
- Data: 100 posts, each has author and category

Code:
// PostController.php
public function index() {
    $posts = Post::latest()->paginate(20);
    return view('posts.index', compact('posts'));
}

// posts/index.blade.php
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>By {{ $post->author->name }}</p>
        <p>In {{ $post->category->name }}</p>
        <p>{{ $post->comments->count() }} comments</p>
    </article>
@endforeach

Query Log (first few):
SELECT * FROM posts ORDER BY created_at DESC LIMIT 20
SELECT * FROM users WHERE id = 1
SELECT * FROM categories WHERE id = 5
SELECT * FROM users WHERE id = 2
SELECT * FROM categories WHERE id = 3
[... 296 more queries]

Models:
// Post.php
class Post extends Model {
    public function author() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }
}

Context:
- Laravel 11, PHP 8.2, MySQL 8.0
- 100 posts in database
- Slow on both local and production

How do I optimize this?
```

**AI Response (Example):**
```
You have a classic N+1 query problem. For 20 posts, you're running:
- 1 query for posts (N = 20)
- 20 queries for authors (N)
- 20 queries for categories (N)
- 20 queries for comment counts (N)
Total: 1 + 20 + 20 + 20 = 61 queries minimum

Fix with Eager Loading:

// PostController.php
public function index() {
    $posts = Post::query()
        ->with(['author', 'category'])
        ->withCount('comments')
        ->latest()
        ->paginate(20);

    return view('posts.index', compact('posts'));
}

// posts/index.blade.php (updated)
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>By {{ $post->author->name }}</p>
        <p>In {{ $post->category->name }}</p>
        <p>{{ $post->comments_count }} comments</p>  {{-- Note: _count suffix --}}
    </article>
@endforeach

This reduces queries from 61 to 3:
1. SELECT * FROM posts ... (with pagination)
2. SELECT * FROM users WHERE id IN (1,2,3...)
3. SELECT * FROM categories WHERE id IN (5,3,8...)

Expected improvement:
- Queries: 301 → 3 (99% reduction)
- Load time: 15s → <1s

Additional optimization (if needed):
// Cache for 5 minutes
public function index() {
    $posts = Cache::remember('posts.index.page.' . request('page', 1), 300, function() {
        return Post::query()
            ->with(['author', 'category'])
            ->withCount('comments')
            ->latest()
            ->paginate(20);
    });

    return view('posts.index', compact('posts'));
}

Also consider:
- Add indexes on posts.user_id and posts.category_id
- Add index on comments.post_id
```

---

### Example 3: Unexpected Null Value

**Symptom:**
```
Call to a member function format() on null
```

**Good Prompt:**
```
Error:
Call to a member function format() on null

Stack Trace:
at app/Models/Post.php:45
at app/Http/Controllers/PostController.php:22

Code:
// Post.php (line 45)
public function getPublishedDateAttribute() {
    return $this->published_at->format('M d, Y');
}

// PostController.php (line 22)
public function index() {
    $posts = Post::all();
    return view('posts.index', compact('posts'));
}

// posts/index.blade.php
@foreach($posts as $post)
    <p>Published: {{ $post->published_date }}</p>
@endforeach

Context:
- Laravel 11, PHP 8.2
- published_at is nullable timestamp
- Some posts are drafts (published_at is null)
- Error occurs when viewing posts index

Sample Data:
Post 1: published_at = "2024-01-15 10:00:00" ✓ Works
Post 2: published_at = NULL ✗ Causes error

Why is this happening and how do I fix it safely?
```

**AI Response (Example):**
```
The error occurs because you're calling format() on null when published_at is null (draft posts).

Root Cause:
- published_at can be null for draft posts
- Accessor doesn't check for null before calling format()
- When blade accesses $post->published_date, it triggers the error

Solution Options:

1. Add Null Check in Accessor (Recommended):
public function getPublishedDateAttribute(): ?string {
    return $this->published_at?->format('M d, Y');
}
// Returns null for unpublished posts

2. Return Default Text:
public function getPublishedDateAttribute(): string {
    return $this->published_at
        ? $this->published_at->format('M d, Y')
        : 'Draft';
}

3. Handle in Blade:
@foreach($posts as $post)
    <p>Published: {{ $post->published_at?->format('M d, Y') ?? 'Draft' }}</p>
@endforeach

4. Filter Published Posts Only:
public function index() {
    $posts = Post::whereNotNull('published_at')->get();
    return view('posts.index', compact('posts'));
}

Recommended Approach:
Combine solutions 1 and 4:
- Add null check in accessor (defensive programming)
- Filter to published posts in controller (business logic)

public function getPublishedDateAttribute(): ?string {
    return $this->published_at?->format('M d, Y');
}

public function index() {
    $posts = Post::published()->get(); // Use scope
    return view('posts.index', compact('posts'));
}

@foreach($posts as $post)
    <p>Published: {{ $post->published_date }}</p>
@endforeach

This prevents:
- Runtime errors
- Showing drafts to public
- Future similar bugs

Add this scope to Post model:
public function scopePublished($query) {
    return $query->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
}
```

---

## Advanced Debugging with AI

### Debugging Strategy: Rubber Duck with AI

Traditional "rubber duck debugging" involves explaining your code to a duck. With AI, you can have a conversation:

```
You: Here's my code that's not working: [paste code]

AI: What's the expected behavior?

You: It should [explanation]. But it's [actual behavior].

AI: Let's walk through it line by line...
```

This conversation helps you think through the logic.

---

### Debugging Strategy: Binary Search

When you have a large codebase and don't know where the bug is:

```
Prompt to AI:
"I have a bug where [description]. The code path involves:
1. [step 1]
2. [step 2]
3. [step 3]
4. [step 4]
5. [step 5]

Help me binary search to find where the issue is.
What should I check at step 3 to determine if the bug is in steps 1-3 or 4-5?"
```

AI helps you systematically narrow down the problem.

---

### Debugging Strategy: Hypothesis Testing

```
Prompt to AI:
"I have a bug: [description]

My hypothesis: [what you think is wrong]

Evidence for:
- [observation 1]
- [observation 2]

Evidence against:
- [observation 3]

Is my hypothesis correct? What test can I run to confirm or refute it?"
```

---

## When AI Gets It Wrong

AI is not perfect. Here's how to tell when AI is misleading you:

### Red Flag 1: Overly Complex Solution

```
Problem: Simple form validation error

AI suggests:
- Creating custom validation rule class
- Overriding framework method
- Using reflection
- 50+ lines of code

Reality: Probably just needs:
'email' => 'required|email|unique:users'
```

**Action:** Ask for simpler solution

---

### Red Flag 2: Outdated Syntax

```
AI suggests Laravel 8 syntax:
Route::get('/posts', 'PostController@index');

But you're on Laravel 11:
Route::get('/posts', [PostController::class, 'index']);
```

**Action:** Always specify framework version in prompt

---

### Red Flag 3: Security Anti-Patterns

```
AI suggests:
DB::raw("SELECT * FROM users WHERE email = '{$request->email}'")

This has SQL injection vulnerability!
```

**Action:** Always review for security, challenge suspicious code

---

### Red Flag 4: Doesn't Match Your Error

```
Your error: "Column 'email' cannot be null"

AI response focuses on: Password hashing

AI didn't actually address your error!
```

**Action:** Restate the problem more clearly

---

## Building Your Debugging Prompt Library

Create templates for common debugging scenarios:

### Template Collection

**1. Database Error Template:**
```
Database Error:
[SQL state and error]

Query:
[Failed query or code]

Schema:
[Relevant table structure]

Expected: [what should happen]
Actual: [what happens]
```

**2. Validation Error Template:**
```
Validation failing for: [field]

Rules:
[validation rules]

Input:
[sample input that fails]

Expected: [pass/fail with specific error]
Actual: [what actually happens]
```

**3. Relationship Error Template:**
```
Relationship Error:
[Error message]

Models:
[Show relationship definitions]

Usage:
[Code trying to use relationship]

Expected: [what should be returned]
Actual: [what is returned]
```

**4. Authentication Error Template:**
```
Auth Error:
[Error message]

Auth Config:
[Relevant config/session.php settings]

Code:
[Login/auth code]

Environment:
[Local vs production differences]
```

Save these templates for quick debugging!

---

## Debugging Checklist

Before asking AI:

- [ ] Can I reproduce the bug consistently?
- [ ] Do I have the exact error message?
- [ ] Do I have the relevant code?
- [ ] Do I know what the expected behavior is?
- [ ] Have I checked obvious issues (typos, missing imports)?
- [ ] Do I have environment details?

When using AI:

- [ ] Used a proper prompt template
- [ ] Included all context
- [ ] Specified framework versions
- [ ] Described what I've already tried
- [ ] Asked specific questions

After AI response:

- [ ] Do I understand the explanation?
- [ ] Does the fix address the root cause?
- [ ] Have I tested the fix?
- [ ] Did I check for side effects?
- [ ] Did I add a test to prevent regression?
- [ ] Did I learn from this bug?

---

## Exercises

### Exercise 1: Debug with AI (30 minutes)

Take a buggy code snippet (provided or from your own projects):
1. Reproduce the bug
2. Gather all context
3. Write a proper debugging prompt
4. Submit to Claude or ChatGPT
5. Evaluate the response
6. Apply and test the fix

### Exercise 2: Compare AI Debugging (45 minutes)

Same bug, different AIs:
1. Submit to Claude
2. Submit to ChatGPT
3. Submit to GitHub Copilot Chat
4. Compare responses:
   - Which was most accurate?
   - Which explained best?
   - Which gave best solution?

### Exercise 3: Red Flag Detection (30 minutes)

Review AI-suggested fixes (create buggy versions):
1. Identify potential issues
2. Test solutions
3. Note what's wrong
4. Learn to spot bad suggestions

---

## Summary

**Key Takeaways:**

1. **AI accelerates debugging 3-10x**
   - But only with good prompts
   - Still need to understand the fix

2. **Essential Prompt Components:**
   - Exact error message
   - Relevant code
   - Clear context
   - What you tried
   - Expected vs actual behavior

3. **Always Verify:**
   - Understand the explanation
   - Test the fix thoroughly
   - Check for side effects
   - Learn from the bug

4. **Know AI Limitations:**
   - Best for common errors
   - Weaker on business logic
   - Can suggest outdated solutions
   - Needs good context

5. **Build Your Library:**
   - Save effective prompts
   - Document common bugs
   - Refine templates
   - Learn patterns

In the next lesson, we'll explore **code review with AI** - how to use AI to improve code quality and catch issues before they become bugs.

---

**Next Lesson**: [05-code-review.md](./05-code-review.md) - Use AI for thorough code reviews.
