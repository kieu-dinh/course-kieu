# Lesson 3 - Code Generation Best Practices

**Duration**: 3-4 hours
**Prerequisites**: Lessons 1-2

---

## Introduction

AI can generate code in seconds that would take you minutes or hours to write. But there's a critical difference between "code that runs" and "code that's production-ready."

In this lesson, you'll learn:
- When to generate code with AI vs. write it yourself
- How to ensure generated code is clean and maintainable
- How to verify code quality and correctness
- How to adapt generated code to your project standards
- How to avoid common pitfalls of AI-generated code

This is where your fundamental knowledge (Modules 01-20) becomes crucial. You need to understand what good code looks like to judge AI output.

---

## The Code Generation Spectrum

### When to Generate vs. Write

**Generate with AI (Good Use Cases):**

1. **Boilerplate Code**
   - Models, migrations, controllers
   - CRUD operations
   - API resources
   - Form requests
   - Tests structure

2. **Repetitive Patterns**
   - Similar methods with slight variations
   - Multiple API endpoints following same pattern
   - Data transformations
   - Validation rules

3. **Standard Implementations**
   - Authentication flows
   - File upload handling
   - Pagination
   - Search functionality
   - Email templates

4. **Time-Consuming Tedium**
   - Type hints for existing code
   - PHPDoc blocks
   - Factory definitions
   - Seeder data

**Write Yourself (Best Not Generated):**

1. **Business Logic**
   - Domain-specific rules
   - Complex calculations
   - Workflow orchestration
   - Custom algorithms

2. **Critical Security**
   - Payment processing
   - Encryption implementation
   - Access control logic
   - API authentication

3. **Architecture Decisions**
   - System design
   - Database schema design
   - Service boundaries
   - Integration patterns

4. **Novel Problems**
   - Unique to your domain
   - No established patterns
   - Requires deep understanding
   - Innovation needed

---

## The Generation Workflow

### Step 1: Plan Before Prompting

Before asking AI to generate code, plan:

**Checklist:**
- [ ] What exact functionality do I need?
- [ ] What are the inputs and outputs?
- [ ] What edge cases must be handled?
- [ ] What are the security requirements?
- [ ] How does this fit with existing code?
- [ ] What's the expected performance?

**Example:**

Before generating a "user registration" feature:
```
Planning:
✓ Inputs: name, email, password, password_confirmation
✓ Validations: all required, email unique, password min 8 chars
✓ Process: validate → hash password → create user → send verification email
✓ Edge cases: duplicate email, weak password, email service down
✓ Security: CSRF protection, password hashing, rate limiting
✓ Integration: uses existing User model, EmailService
✓ Performance: queue verification email
```

Now you can write an effective prompt.

---

### Step 2: Generate with Context

Provide all the context you planned:

```
Generate a user registration feature for Laravel 11.

Context:
- Using User model (standard Laravel auth)
- EmailService handles sending (already implemented)
- Queue is configured (database driver)

Requirements:
1. Store new user with: name, email, password
2. Validate: all required, email unique, password min 8 chars
3. Hash password with bcrypt
4. Send verification email (queued)
5. Rate limit: 3 registrations per hour per IP
6. Redirect to email-verification-sent page

Error Handling:
- Show validation errors inline
- Handle email service failures gracefully
- Log failed email attempts

Provide:
1. RegisterController method
2. RegisterRequest validation class
3. Registration route
4. Basic test example

Follow Laravel 11 conventions and PSR-12.
```

---

### Step 3: Review Generated Code

**NEVER copy-paste without reviewing.** Check every aspect:

#### Code Quality Checklist

**✅ Correctness:**
- [ ] Does it do what you asked?
- [ ] Are all requirements met?
- [ ] Does logic make sense?
- [ ] Would it handle edge cases?

**✅ Security:**
- [ ] No SQL injection vulnerabilities?
- [ ] User input properly validated?
- [ ] Sensitive data handled correctly?
- [ ] No XSS vulnerabilities?
- [ ] Authentication/authorization correct?

**✅ Performance:**
- [ ] No N+1 queries?
- [ ] Appropriate use of caching?
- [ ] Database indexes needed?
- [ ] Efficient algorithms?

**✅ Code Style:**
- [ ] Follows PSR-12 standards?
- [ ] Type hints present?
- [ ] Return types specified?
- [ ] Proper naming conventions?
- [ ] Consistent with your codebase?

**✅ Maintainability:**
- [ ] Easy to understand?
- [ ] Well-structured?
- [ ] Properly commented?
- [ ] Testable?
- [ ] Follows SOLID principles?

---

### Step 4: Test Generated Code

Never trust, always verify:

**Manual Testing:**
```
1. Happy path (normal usage)
2. Invalid inputs
3. Edge cases
4. Error conditions
5. Performance with realistic data
```

**Automated Testing:**
```php
// Always write tests for generated code
public function test_user_registration_with_valid_data()
{
    $response = $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/email-verification-sent');
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
}
```

---

### Step 5: Adapt to Your Project

Generated code is generic. Adapt it to your standards:

**Example Adaptations:**

**1. Naming Conventions:**
```php
// Generated (generic)
function getUserData($id) { }

// Your style
function retrieveUserById(int $id): User { }
```

**2. Error Handling:**
```php
// Generated (basic)
catch (Exception $e) {
    return back()->with('error', 'Something went wrong');
}

// Your style (specific)
catch (ValidationException $e) {
    return back()->withErrors($e->errors());
}
catch (QueryException $e) {
    Log::error('Database error in registration', ['error' => $e->getMessage()]);
    return back()->with('error', 'Unable to create account. Please try again.');
}
```

**3. Architecture Patterns:**
```php
// Generated (controller logic)
public function store(Request $request) {
    $validated = $request->validate([...]);
    $user = User::create($validated);
    Mail::to($user)->send(new WelcomeEmail());
    return redirect()->route('dashboard');
}

// Your pattern (service layer)
public function store(StoreUserRequest $request) {
    $user = $this->userService->register($request->validated());
    return redirect()->route('dashboard');
}
```

---

## Generating Different Types of Code

### 1. Models and Migrations

**Effective Prompt:**
```
Create a Post model and migration for a blog.

Fields:
- title: string(200), required
- slug: string(200), unique, indexed
- excerpt: text, nullable
- body: longtext, required
- featured_image: string, nullable
- published_at: timestamp, nullable, indexed
- user_id: foreign key to users, indexed
- category_id: foreign key to categories, indexed

Relationships:
- belongsTo User
- belongsTo Category
- hasMany Comment
- belongsToMany Tag

Features:
- Soft deletes
- Fillable: title, excerpt, body, featured_image, published_at, category_id
- Casts: published_at to datetime
- Accessor: is_published (checks published_at)
- Scope: published (where published_at is not null and <= now)

Include migration with all indexes and foreign keys.
```

**Review Checklist:**
- [ ] All fields have correct types
- [ ] Indexes on foreign keys and commonly queried fields
- [ ] Foreign key constraints configured
- [ ] Soft deletes enabled if needed
- [ ] Timestamps enabled
- [ ] Fillable/guarded set correctly
- [ ] Casts defined
- [ ] Relationships properly configured

---

### 2. Controllers

**Effective Prompt:**
```
Create a RESTful PostController for blog posts.

Context:
- Using Post model with published() scope
- PostService handles business logic
- Users must be authenticated and own posts to edit/delete
- Admin can manage all posts

Methods needed:
- index: show published posts, paginated (15 per page)
- show: single post (published only for guests, any for owner/admin)
- create: form to create (auth required)
- store: save new post (auth required, validate, redirect to show)
- edit: form to edit (owner or admin only)
- update: save changes (owner or admin only)
- destroy: soft delete (owner or admin only)

Validation:
- title: required, max 200
- slug: required, unique (except current post), alpha_dash
- excerpt: nullable, max 500
- body: required
- category_id: required, exists in categories
- featured_image: nullable, image, max 2MB
- published_at: nullable, date

Authorization:
- Use Laravel policies
- PostPolicy methods: view, create, update, delete

Error Handling:
- Show validation errors
- Handle 404 gracefully
- Handle authorization failures

Follow Laravel resource controller conventions.
Include type hints and return types.
```

**Review Checklist:**
- [ ] All methods follow RESTful conventions
- [ ] Authorization checks present
- [ ] Validation comprehensive
- [ ] Error handling robust
- [ ] Type hints and return types
- [ ] Follows your architecture (service layer, etc.)
- [ ] No business logic in controller
- [ ] Consistent response format

---

### 3. Service Classes

**Effective Prompt:**
```
Create a PostService for blog post operations.

Responsibilities:
- Create new post (with slug generation)
- Update existing post
- Publish/unpublish post
- Delete post (soft delete)
- Restore deleted post
- Duplicate post

Each method should:
- Accept typed parameters
- Return Post model or bool
- Handle database transactions where needed
- Fire appropriate events
- Log important actions

Slug generation:
- Convert title to slug
- Ensure uniqueness by appending number if needed
- Update slug on title change (optional)

Events to fire:
- PostCreated
- PostPublished
- PostUnpublished
- PostDeleted

Dependencies:
- PostRepository (for data access)
- Illuminate\Support\Str (for slug generation)
- Illuminate\Support\Facades\Log

Include PHPDoc blocks.
Follow SOLID principles.
Type hint everything.
```

**Review Checklist:**
- [ ] Single Responsibility Principle followed
- [ ] Dependencies injected (not instantiated)
- [ ] All methods type-hinted
- [ ] Transactions used where needed
- [ ] Events fired appropriately
- [ ] Logging implemented
- [ ] Error handling present
- [ ] Testable design

---

### 4. API Resources

**Effective Prompt:**
```
Create API resources for a Post model REST API.

PostResource (single post):
- id
- title
- slug
- excerpt
- body (only if ?include=body)
- featured_image (full URL)
- published_at (ISO 8601 format)
- author: { id, name, avatar_url }
- category: { id, name, slug }
- tags: [{ id, name, slug }]
- comments_count
- created_at (ISO 8601)
- updated_at (ISO 8601)

PostCollection (multiple posts):
- Wrap posts in "data" key
- Include pagination metadata
- Add links (first, last, prev, next)

Conditional fields:
- Show 'body' only if ?include=body
- Show author email only if authenticated user is author
- Show draft posts only if user can view unpublished

Use Laravel 11 API resources.
Handle relationships efficiently.
Include examples of usage.
```

**Review Checklist:**
- [ ] All fields properly mapped
- [ ] Relationships efficiently loaded
- [ ] Conditional fields work correctly
- [ ] Dates formatted consistently
- [ ] URLs are absolute
- [ ] Pagination metadata included
- [ ] No N+1 query issues
- [ ] Follows API design standards

---

### 5. Form Requests

**Effective Prompt:**
```
Create form request validation for storing a blog post.

Request: StorePostRequest

Rules:
- title: required, string, max 200, unique in posts
- slug: sometimes, string, alpha_dash, max 200, unique in posts
- excerpt: nullable, string, max 500
- body: required, string
- featured_image: nullable, image, mimes:jpg,png,webp, max:2048
- published_at: nullable, date, after_or_equal:now
- category_id: required, exists:categories,id
- tag_ids: nullable, array
- tag_ids.*: exists:tags,id

Authorization:
- User must be authenticated
- User must have 'create-posts' ability

Custom validation:
- If published_at is set, featured_image is required
- Slug auto-generated from title if not provided

Error messages:
- Custom messages for all validation rules
- Clear, user-friendly language

Include:
- prepareForValidation() to generate slug
- withValidator() for custom after-validation rules
- authorize() method
```

**Review Checklist:**
- [ ] All fields validated
- [ ] Validation rules appropriate
- [ ] Custom rules work correctly
- [ ] Authorization checked
- [ ] Error messages clear
- [ ] Edge cases handled
- [ ] Follows Laravel conventions

---

### 6. Tests

**Effective Prompt:**
```
Create PHPUnit tests for PostService.

Test class: PostServiceTest

Methods to test:
- createPost()
- updatePost()
- publishPost()
- unpublishPost()
- deletePost()

For each method, test:
- Happy path (normal success)
- Validation failures
- Authorization failures
- Edge cases
- Database integrity

Example structure wanted:
public function test_create_post_with_valid_data()
public function test_create_post_without_required_title()
public function test_cannot_create_post_as_guest()

Use:
- RefreshDatabase trait
- Factories for models
- Act, Arrange, Assert pattern
- Descriptive test names
- One assertion focus per test

Include setup() method for common dependencies.
```

**Review Checklist:**
- [ ] All methods covered
- [ ] All paths tested (success, failure, edge cases)
- [ ] Tests are isolated (no dependencies)
- [ ] Descriptive test names
- [ ] Clear assertions
- [ ] Proper setup/teardown
- [ ] Fast execution
- [ ] Follows AAA pattern

---

## Handling Generated Code Issues

### Common Issues and Fixes

#### Issue 1: Outdated Syntax

**Generated:**
```php
// Laravel 8 syntax
Route::get('/posts', 'PostController@index');
```

**Fix to Laravel 11:**
```php
Route::get('/posts', [PostController::class, 'index']);
```

**Solution**: Always specify framework version in prompt.

---

#### Issue 2: Missing Type Hints

**Generated:**
```php
public function store($request) {
    return view('posts.show');
}
```

**Fix:**
```php
public function store(StorePostRequest $request): RedirectResponse {
    return redirect()->route('posts.show');
}
```

**Solution**: Request "full type hints and return types" in prompt.

---

#### Issue 3: No Error Handling

**Generated:**
```php
public function createPost(array $data): Post {
    return Post::create($data);
}
```

**Fix:**
```php
public function createPost(array $data): Post {
    try {
        DB::beginTransaction();

        $post = Post::create($data);

        event(new PostCreated($post));

        DB::commit();

        return $post;
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Failed to create post', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);

        throw new PostCreationException('Unable to create post', 0, $e);
    }
}
```

**Solution**: Request "comprehensive error handling" in prompt.

---

#### Issue 4: Security Vulnerabilities

**Generated:**
```php
public function search(Request $request) {
    $query = $request->get('q');
    $results = DB::select("SELECT * FROM posts WHERE title LIKE '%{$query}%'");
    return view('search', compact('results'));
}
```

**Problems:**
- SQL injection vulnerability
- No input validation
- No XSS protection
- No pagination

**Fix:**
```php
public function search(SearchRequest $request): View {
    $query = $request->validated('q');

    $results = Post::query()
        ->where('title', 'LIKE', "%{$query}%")
        ->orWhere('body', 'LIKE', "%{$query}%")
        ->published()
        ->paginate(15);

    return view('search', compact('results', 'query'));
}
```

**Solution**: Always review for security, request "prevent SQL injection and XSS."

---

#### Issue 5: Poor Performance

**Generated:**
```php
public function index() {
    $posts = Post::all();

    foreach ($posts as $post) {
        echo $post->author->name; // N+1 query!
        foreach ($post->comments as $comment) { // N+1 query!
            echo $comment->user->name; // N+1 query!
        }
    }
}
```

**Problems:**
- Loads all posts (no pagination)
- N+1 queries on relationships
- No caching

**Fix:**
```php
public function index(): View {
    $posts = Post::query()
        ->with(['author', 'comments.user'])
        ->published()
        ->latest()
        ->paginate(15);

    return view('posts.index', compact('posts'));
}
```

**Solution**: Request "optimize for N+1 queries, use pagination."

---

## Advanced Generation Techniques

### Technique 1: Incremental Generation

Instead of generating everything at once, build incrementally:

**Step 1:** Basic structure
```
Create a basic Post model with just title and body fields.
```

**Step 2:** Add complexity
```
Add these fields to the Post model:
- excerpt (text, nullable)
- published_at (timestamp, nullable)

Add published() scope.
```

**Step 3:** Add relationships
```
Add relationships to Post model:
- belongsTo User
- hasMany Comment
```

**Step 4:** Add advanced features
```
Add these methods to Post:
- isPublished(): bool
- publish(): self
- unpublish(): self
```

This approach:
- ✅ Easier to review each step
- ✅ Better understanding of each addition
- ✅ Catch issues early
- ✅ Learn progressively

---

### Technique 2: Generate from Tests

Write tests first, then generate implementation:

**Step 1:** Write test
```php
public function test_user_can_publish_post() {
    $user = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post(
        route('posts.publish', $post)
    );

    $response->assertRedirect(route('posts.show', $post));
    $this->assertNotNull($post->fresh()->published_at);
}
```

**Step 2:** Ask AI to generate implementation
```
Here's my test for publishing a post:
[paste test]

Generate the implementation that makes this test pass:
1. Route definition
2. Controller method
3. Any necessary validation

The test should pass without modification.
```

This approach:
- ✅ Test-driven development
- ✅ Clear requirements from test
- ✅ Guaranteed testability
- ✅ Better design

---

### Technique 3: Generate Variations

Ask for multiple approaches and choose best:

```
Show me 3 ways to implement post scheduling:

1. Simple approach: Cron job checking published_at
2. Queue approach: Dispatch scheduled jobs
3. Event-driven approach: Using Laravel's task scheduling

For each, provide:
- Implementation code
- Pros and cons
- Scalability considerations
- When to use

Then recommend which is best for:
- Small blog (< 100 posts/day)
- Medium site (< 1000 posts/day)
- Large platform (> 10000 posts/day)
```

---

## Code Generation Workflow Summary

### The Complete Process

```
1. PLAN
   ↓ What exactly do I need?
   ↓ What are the requirements?

2. PROMPT
   ↓ Provide full context
   ↓ Be specific and detailed

3. GENERATE
   ↓ Let AI create code

4. REVIEW
   ↓ Check correctness
   ↓ Check security
   ↓ Check performance
   ↓ Check style

5. TEST
   ↓ Manual testing
   ↓ Automated tests
   ↓ Edge cases

6. ADAPT
   ↓ Match your style
   ↓ Follow your patterns
   ↓ Improve if needed

7. INTEGRATE
   ↓ Add to codebase
   ↓ Commit with clear message
   ↓ Document if needed
```

---

## Real-World Example: Complete Feature

Let's generate a complete "post commenting" feature step by step.

### Step 1: Plan

```
Feature: Post Comments
- Users can comment on published posts
- Nested comments (one level)
- Markdown support
- Edit own comments (10 min window)
- Delete own comments (soft delete)
- Report inappropriate comments
```

### Step 2: Generate Migration

**Prompt:**
```
Create migration for comments table:
- id
- post_id (foreign key to posts)
- user_id (foreign key to users)
- parent_id (foreign key to comments, nullable, for nesting)
- body (text, required)
- edited_at (timestamp, nullable)
- deleted_at (timestamp, nullable for soft deletes)
- timestamps

Indexes on: post_id, user_id, parent_id
Foreign keys with cascade on delete for post and user
```

**Review:** Check foreign keys, indexes, data types

### Step 3: Generate Model

**Prompt:**
```
Create Comment model with:
- Soft deletes
- Fillable: body
- Relationships: belongsTo Post, belongsTo User, belongsTo Comment (parent), hasMany Comment (replies)
- Casts: edited_at and deleted_at to datetime
- Methods:
  - isEditable(): bool (true if created < 10 minutes ago)
  - edit(string $body): self
  - markAsEdited(): void
```

**Review:** Check relationships, logic, type hints

### Step 4: Generate Controller

**Prompt:**
```
Create CommentController with:
- store(StoreCommentRequest, Post): RedirectResponse
  - Create comment
  - Flash success message
  - Redirect back to post
- update(UpdateCommentRequest, Comment): RedirectResponse
  - Authorize user owns comment
  - Check isEditable()
  - Update and mark as edited
- destroy(Comment): RedirectResponse
  - Authorize user owns comment
  - Soft delete
  - Flash success

Full type hints, error handling, authorization checks
```

**Review:** Authorization, error handling, redirects

### Step 5: Generate Validation

**Prompt:**
```
Create StoreCommentRequest with:
- Rules: body required, string, max 1000
- Authorization: user authenticated
- Custom: post must be published

Create UpdateCommentRequest with:
- Rules: body required, string, max 1000
- Authorization: user owns comment
- Custom: comment must be editable (< 10 min old)
```

**Review:** Rules, authorization, custom validation

### Step 6: Generate Views

**Prompt:**
```
Create Blade components for comments:

1. comments-section.blade.php
   - Shows all comments for post
   - Nested structure (one level)
   - Comment form at top

2. comment-card.blade.php
   - Display single comment
   - Show author, date, body (markdown)
   - Edit/delete buttons (if owner)
   - Reply button
   - Show "edited" tag if edited

Use Tailwind CSS, make responsive
```

**Review:** Accessibility, responsive design, XSS protection

### Step 7: Generate Tests

**Prompt:**
```
Create tests for Comment feature:

CommentTest:
- test_user_can_comment_on_published_post
- test_cannot_comment_on_unpublished_post
- test_guest_cannot_comment
- test_user_can_edit_own_comment_within_10_minutes
- test_cannot_edit_comment_after_10_minutes
- test_user_can_delete_own_comment
- test_cannot_delete_other_users_comment
- test_comment_is_soft_deleted

Use factories, RefreshDatabase
```

**Review:** Coverage, assertions, isolation

### Step 8: Integrate

```bash
php artisan migrate
php artisan test --filter=CommentTest
git add .
git commit -m "Add comment feature for posts"
```

---

## Exercises

### Exercise 1: Generate and Review (45 minutes)

1. Choose a simple feature (e.g., tags for posts)
2. Generate migration, model, controller
3. Review each piece with checklists
4. Identify and fix at least 3 issues
5. Write tests to verify

### Exercise 2: Refactor Generated Code (60 minutes)

1. Generate a controller with all logic in it
2. Refactor to use service layer
3. Refactor to use repository pattern
4. Compare: before vs. after
5. Note: what makes the refactored version better?

### Exercise 3: Security Audit (30 minutes)

1. Generate a search feature
2. Intentionally ask for "simple" version
3. Audit for security issues:
   - SQL injection points
   - XSS vulnerabilities
   - Missing authorization
   - No rate limiting
4. Fix all issues

---

## Summary

**Key Takeaways:**

1. **Generation is a tool, not a shortcut**
   - Plan before generating
   - Review thoroughly
   - Test extensively
   - Adapt to your project

2. **Your knowledge is essential**
   - You must understand generated code
   - You must catch mistakes
   - You must make architectural decisions
   - You are responsible for the code

3. **The Review Checklist**
   - ✅ Correctness
   - ✅ Security
   - ✅ Performance
   - ✅ Style
   - ✅ Maintainability

4. **The Golden Rule**
   - Never commit code you don't understand
   - Always test generated code
   - Always review for security
   - Always adapt to your standards

In the next lesson, we'll explore **AI-assisted debugging** - how to use AI to find and fix bugs faster.

---

**Next Lesson**: [04-ai-debugging.md](./04-ai-debugging.md) - Debug efficiently with AI assistance.
