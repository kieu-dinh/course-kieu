# Lesson 2 - Effective Prompting for Code

**Duration**: 3-4 hours
**Prerequisites**: Lesson 1 (AI Tools Overview)

---

## Introduction

Imagine hiring a talented junior developer. If you say "build a website," they'll be confused and produce poor results. But if you provide clear requirements, context, and examples, they'll create exactly what you need.

AI works the same way. The quality of output depends entirely on the quality of your prompts. This is called **prompt engineering** - the skill of communicating effectively with AI.

In this lesson, you'll learn to write prompts that produce clean, correct, and contextual code. This skill will multiply your productivity by 10x.

---

## The Anatomy of a Great Prompt

### The Five Components

Every effective prompt should include:

1. **Context**: What's the situation?
2. **Task**: What needs to be done?
3. **Constraints**: What are the limitations?
4. **Format**: How should the output look?
5. **Examples**: What's a reference point?

Let's see the difference:

**Bad Prompt:**
```
Make a login system
```

**Good Prompt:**
```
I'm building a Laravel 11 application with Livewire 3.

Create a login Livewire component that:
- Uses email and password fields
- Validates input (required, email format)
- Throttles attempts (5 max per minute)
- Shows error messages inline
- Redirects to dashboard on success
- Uses Tailwind CSS for styling

Follow Laravel best practices for authentication.
Return only the component class and blade template.
```

See the difference? The good prompt provides all five components, leaving no room for ambiguity.

---

## Principle 1: Be Specific

### Vague vs. Specific

❌ **Vague**: "Create a user model"

✅ **Specific**:
```
Create a Laravel User model with these requirements:
- Fields: name (string, 100 chars), email (unique), password (hashed), avatar (nullable), bio (text, nullable), email_verified_at (timestamp, nullable)
- Relationships: hasMany posts, hasMany comments
- Fillable: name, email, avatar, bio
- Hidden: password, remember_token
- Casts: email_verified_at to datetime
- Add a method isVerified() that returns boolean
```

### Why Specificity Matters

AI models are trained on millions of code examples. When you're vague, they'll choose the most common pattern, which might not fit your needs.

**Example:**

Prompt: "Create a function to calculate price"

AI might generate:
```php
function calculatePrice($quantity, $price) {
    return $quantity * $price;
}
```

But you might need:
```php
function calculatePrice($quantity, $unitPrice, $tax = 0.20, $discount = 0) {
    $subtotal = $quantity * $unitPrice;
    $afterDiscount = $subtotal * (1 - $discount);
    $total = $afterDiscount * (1 + $tax);
    return round($total, 2);
}
```

The more specific your prompt, the closer AI gets to what you actually need.

---

## Principle 2: Provide Context

### The Context Pyramid

**Level 1: Technology Stack**
Tell AI what you're using:
- "In Laravel 11 with PHP 8.2..."
- "Using Alpine.js 3 and Tailwind CSS..."
- "For a React component with TypeScript..."

**Level 2: Project Domain**
Explain the business context:
- "For an e-commerce platform..."
- "In a hospital management system..."
- "Building a blog with user comments..."

**Level 3: Existing Code**
Share relevant snippets:
- "Here's my current User model..."
- "This is how we handle errors..."
- "We follow this naming convention..."

### Example: With vs. Without Context

**Without Context:**
```
Create a function to send notifications
```

Result: Generic, may not fit your system

**With Context:**
```
I'm building a Laravel task management app. We use Laravel's notification system with database and email channels.

Create a notification class that:
- Sends when a task is assigned to a user
- Includes task title, description, and due date
- Links to the task detail page
- Uses our email template (resources/views/emails/layout.blade.php)
- Stores in database for in-app notifications

Our Task model has: title, description, due_date, assigned_to (user_id)
```

Result: Tailored to your specific system

---

## Principle 3: Define Constraints

Constraints are boundaries that guide AI toward solutions that fit your requirements.

### Types of Constraints

**Technical Constraints:**
- "Without using external packages"
- "Must work with PHP 8.0+"
- "Cannot use JavaScript"
- "No raw SQL queries, use Eloquent"

**Performance Constraints:**
- "Should handle 10,000 records efficiently"
- "Must cache results for 5 minutes"
- "Optimize for memory usage"

**Security Constraints:**
- "Prevent SQL injection"
- "Sanitize all user input"
- "Use CSRF protection"

**Style Constraints:**
- "Follow PSR-12 coding standards"
- "Use type hints for all parameters"
- "Maximum cyclomatic complexity of 10"
- "Match our existing code style"

### Example

```
Create a search function for products that:

Constraints:
- Must use Eloquent (no raw SQL)
- Should work with 100,000+ products
- Must prevent SQL injection
- Cache results for 5 minutes
- Use eager loading to avoid N+1 queries
- Follow PSR-12 standards
- Type hint all parameters and return types
```

---

## Principle 4: Request Specific Formats

Tell AI exactly how you want the output structured.

### Format Options

**Code only:**
```
Return only the PHP code, no explanations
```

**Code with comments:**
```
Include inline comments explaining complex logic
```

**Code with explanation:**
```
First provide the code, then explain:
1. How it works
2. Why you chose this approach
3. Potential edge cases
```

**Step by step:**
```
Break this into steps:
1. Database migration
2. Model with relationships
3. Controller methods
4. Routes
5. Blade views
```

**Multiple approaches:**
```
Show two solutions:
1. Simple approach (easy to understand)
2. Optimized approach (better performance)

Explain trade-offs of each
```

### Example

**Prompt:**
```
Create a Laravel service to process payments.

Format your response as:
1. Interface definition
2. Implementation class
3. Service provider registration
4. Usage example in controller
5. PHPUnit test

For each section, explain the purpose in a comment block.
```

This ensures you get organized, documented code you can immediately use.

---

## Principle 5: Use Examples

Examples are powerful because they show exactly what you want.

### One-Shot Prompting

Provide one example:

```
I want a validation rule similar to this:

'email' => ['required', 'email', 'unique:users,email,' . $userId]

Create a similar rule for:
- username (alphanumeric, 3-20 chars, unique in users table)
```

### Few-Shot Prompting

Provide multiple examples to establish a pattern:

```
Here are existing methods in my controller:

public function store(StorePostRequest $request): RedirectResponse
{
    $post = Post::create($request->validated());
    return redirect()->route('posts.show', $post);
}

public function update(UpdatePostRequest $request, Post $post): RedirectResponse
{
    $post->update($request->validated());
    return redirect()->route('posts.show', $post);
}

Following the same pattern, create a destroy method.
```

### Reference Existing Code

```
Here's how we currently handle file uploads:

[paste your existing code]

Create a similar method for handling avatar uploads, but:
- Resize to 200x200
- Store in 'avatars' directory
- Generate thumbnail at 50x50
```

---

## Principle 6: Iterate and Refine

Your first prompt rarely produces perfect results. That's normal! Treat it as a conversation.

### The Refinement Process

**First Attempt:**
```
Create a user registration form
```

**Review Response** → Too basic

**Second Attempt:**
```
The form is too simple. Add:
- Password confirmation field
- Terms acceptance checkbox
- Client-side validation
- Show password strength indicator
```

**Review Response** → Better, but missing backend

**Third Attempt:**
```
Now add the backend:
- Controller method with validation
- Store user in database
- Hash password with bcrypt
- Send verification email
- Redirect with success message
```

**Review Response** → Perfect!

### Improvement Prompts

When output isn't quite right, use specific improvement requests:

**For Better Quality:**
- "Add error handling for edge cases"
- "Include type hints and return types"
- "Add PHPDoc blocks"
- "Follow SOLID principles"

**For Different Approach:**
- "Refactor using the repository pattern"
- "Convert to use dependency injection"
- "Make it more testable"

**For Better Explanation:**
- "Explain why you chose this approach"
- "What are the security implications?"
- "What happens if X is null?"

---

## Principle 7: Specify Your Knowledge Level

AI can adjust explanations based on your experience.

### Beginner Level

```
I'm new to Laravel migrations. Create a migration for a blog posts table and explain each part line by line, including why we use certain data types.
```

Result: Detailed explanations, simpler code

### Intermediate Level

```
Create a Laravel migration for posts with standard blog fields. Include foreign keys and indexes.
```

Result: Standard best practices, moderate detail

### Advanced Level

```
Design an optimized database schema for a high-traffic blog. Consider read performance, relationship queries, and future scaling. Include migrations and explain architectural decisions.
```

Result: Advanced patterns, performance focus

---

## Common Prompting Patterns

### Pattern 1: The Explanation Request

```
Explain [concept] in the context of [your situation]:
- Why it's used
- How it works
- When to use it
- Common pitfalls
- Simple example

I already understand [related concepts].
```

**Example:**
```
Explain Laravel middleware in the context of building an API.
I already understand routes and controllers.
Show how to create custom middleware for API authentication.
```

---

### Pattern 2: The Code Review

```
Review this code for:
- Security vulnerabilities
- Performance issues
- Best practice violations
- Potential bugs
- Readability improvements

[paste your code]

Be specific about what to change and why.
```

---

### Pattern 3: The Debugging Assistant

```
I'm getting this error:
[paste error message]

Here's the relevant code:
[paste code]

What's causing this and how do I fix it?
Explain why the error occurs.
```

---

### Pattern 4: The Refactoring Request

```
Refactor this code to:
- [specific improvement 1]
- [specific improvement 2]
- [specific improvement 3]

[paste code]

Show before/after comparison and explain what changed.
```

---

### Pattern 5: The Learning Path

```
I want to learn [topic] in the context of [your project].
- Start with fundamentals
- Show practical examples
- Progress to advanced usage
- Include common mistakes to avoid

My current level: [beginner/intermediate/advanced]
```

---

## Domain-Specific Prompting

### For Laravel Development

**Template:**
```
In Laravel 11 with [packages], create [component] that:

Requirements:
- [specific requirement 1]
- [specific requirement 2]

Follow Laravel conventions:
- Use service container for dependencies
- Type hint interfaces
- Follow PSR standards
- Include test example

Database context:
- [relevant models and relationships]
```

**Example:**
```
In Laravel 11 with Livewire 3, create a product filter component that:

Requirements:
- Filter by category, price range, brand
- Show result count
- Update URL parameters
- Maintain filters on page refresh
- Use Alpine.js for smooth interactions

Follow Laravel conventions:
- Use query scopes on Product model
- Type hint dependencies
- Cache filtered results for 5 minutes

Database context:
Product belongsTo Category, belongsToMany Brand
```

---

### For Database Design

**Template:**
```
Design a database schema for [domain] with:

Tables needed:
- [table 1]: [description]
- [table 2]: [description]

Relationships:
- [relationship description]

Constraints:
- [business rules]

Provide:
1. ER diagram description
2. Laravel migrations
3. Model relationships
4. Index recommendations
```

---

### For API Development

**Template:**
```
Create a REST API endpoint for [resource] that:

HTTP Method: [GET/POST/PUT/DELETE]
Route: [/api/resource]
Authentication: [type]

Request:
- [parameter 1]: [type, validation]
- [parameter 2]: [type, validation]

Response:
- Success: [status code, format]
- Error: [status codes, messages]

Include:
- Controller method
- Form request validation
- API resource transformer
- Example response
```

---

## Advanced Prompting Techniques

### Technique 1: Chain of Thought

Ask AI to think step-by-step:

```
Before writing code, think through:
1. What problem are we solving?
2. What are the edge cases?
3. What's the best approach?
4. What are the trade-offs?

Then provide the solution with your reasoning.

Task: Create a cart system that handles product variants
```

---

### Technique 2: Constraint-Based Generation

Provide a list of must-haves and must-not-haves:

```
Create a user authentication system.

MUST have:
- Email/password login
- Password reset
- Email verification
- Rate limiting
- Session management

MUST NOT:
- Use any external packages beyond Laravel core
- Store passwords in plain text
- Allow concurrent sessions
- Use cookies for authentication

Explain how you meet each constraint.
```

---

### Technique 3: Role-Based Prompting

Ask AI to assume a specific role:

```
Act as a senior Laravel developer reviewing my code.

Focus on:
- Architecture and design patterns
- Performance optimization
- Security vulnerabilities
- SOLID principles
- Testability

Here's my code:
[paste code]

Provide specific, actionable feedback.
```

---

### Technique 4: Comparative Analysis

Ask for multiple solutions:

```
Show me 3 ways to implement [feature]:
1. Simplest approach (beginner-friendly)
2. Standard Laravel approach (best practices)
3. Advanced approach (enterprise-grade)

For each, explain:
- Implementation code
- Pros and cons
- When to use it
- Complexity level
```

---

## Real-World Examples

### Example 1: Building a Feature

**Task**: Add comment functionality to blog

**Effective Prompt:**
```
I'm building a Laravel 11 blog. Add a comment system for posts.

Current structure:
- Post model has: id, user_id, title, content, published_at
- User model is standard Laravel auth

Requirements:
- Nested comments (one level only)
- Users must be logged in to comment
- Show author name and avatar
- Sort by newest first
- Soft delete comments
- Markdown support in comment body
- Email notification to post author

Provide:
1. Comment migration
2. Comment model with relationships
3. CommentController (store, destroy methods)
4. Comment Livewire component for display
5. Blade partial for comment form
6. Comment notification class

Follow Laravel 11 best practices.
```

---

### Example 2: Debugging Complex Issue

**Effective Prompt:**
```
I'm getting random "419 Page Expired" errors in my Laravel app.

Context:
- Laravel 11 with Livewire 3
- Behind CloudFlare proxy
- Session driver: redis
- Forms have @csrf token
- Happens after ~30 minutes of inactivity

Error: "CSRF token mismatch"

Recent changes:
- Switched from file to redis sessions
- Added CloudFlare
- Updated to Livewire 3

What's the likely cause and how do I fix it?
Include configuration changes needed.
```

---

### Example 3: Refactoring

**Effective Prompt:**
```
Refactor this controller to follow Laravel best practices:

[paste 200-line controller]

Issues I see:
- Too much logic in controller
- No service layer
- Direct DB queries
- No type hints
- Poor error handling

Refactor to:
1. Service class for business logic
2. Repository for data access
3. Form requests for validation
4. Proper type hints and return types
5. Exception handling

Show new structure with explanation of each layer.
```

---

## Prompting Anti-Patterns

### Anti-Pattern 1: The Lazy Prompt

❌ **Bad:**
```
Fix my code [paste 500 lines]
```

Why it fails:
- No context
- No specific issue identified
- AI doesn't know what "fix" means

✅ **Good:**
```
This Laravel controller is violating Single Responsibility Principle.
Refactor to extract:
- Email sending → SendEmailService
- Data processing → OrderProcessor
- Validation → Form Request

[paste relevant sections only]
```

---

### Anti-Pattern 2: The Unrealistic Request

❌ **Bad:**
```
Create a complete e-commerce platform like Amazon
```

Why it fails:
- Too broad
- AI can't generate enterprise systems
- You won't understand the output

✅ **Good:**
```
Create a basic product catalog with:
- Products table (name, price, description)
- Categories (one level)
- Simple search by name
- Basic cart (session-based)

Focus on getting the structure right first.
```

---

### Anti-Pattern 3: The Context-Free Question

❌ **Bad:**
```
Why doesn't this work?
$user->posts
```

Why it fails:
- No context about models
- No error message
- No explanation of expected behavior

✅ **Good:**
```
In my Laravel app, this line returns null:
$user->posts

My User model:
class User extends Model {
    // [relevant code]
}

My Post model:
class Post extends Model {
    // [relevant code]
}

I expect it to return a collection of user's posts.
What's missing?
```

---

### Anti-Pattern 4: The Assumption Prompt

❌ **Bad:**
```
Update this to handle the edge cases
```

Why it fails:
- AI doesn't know which edge cases
- Assumes AI understands your domain

✅ **Good:**
```
This payment function doesn't handle:
1. Zero amount orders
2. Invalid payment method
3. Declined transactions
4. Network timeouts

Add error handling for each case and explain your approach.

[paste code]
```

---

## Prompt Templates Library

### Template 1: New Feature

```
Feature: [name]
Purpose: [why we need it]
Context: [Laravel version, packages, relevant models]

Requirements:
- [ ] Requirement 1
- [ ] Requirement 2
- [ ] Requirement 3

Technical Constraints:
- [constraint 1]
- [constraint 2]

Acceptance Criteria:
- [ ] Criteria 1
- [ ] Criteria 2

Provide:
1. [what you need]
2. [what you need]

Follow [standards/patterns].
```

---

### Template 2: Code Review

```
Review this [type] for:
- ✅ Correctness
- ⚡ Performance
- 🔒 Security
- 📖 Readability
- 🧪 Testability

[paste code]

Current issues I'm aware of:
- [known issue 1]

Questions:
1. [specific question]
2. [specific question]
```

---

### Template 3: Learning

```
Teach me [concept] in Laravel:

My current understanding:
- [what you already know]

What I want to learn:
- [specific goal 1]
- [specific goal 2]

Show:
1. Simple explanation with analogy
2. Practical example in my project context
3. Common mistakes to avoid
4. When to use vs when not to use

Project context: [brief description]
```

---

## Exercises

### Exercise 1: Prompt Comparison (30 minutes)

1. Start with a vague prompt: "Create a contact form"
2. Refine it using all five components (context, task, constraints, format, examples)
3. Send both to ChatGPT or Claude
4. Compare results
5. Note the differences

**Goal**: See how specificity affects output quality

---

### Exercise 2: Iterative Refinement (45 minutes)

1. Choose a feature from your past projects
2. Write a prompt for it
3. Get AI response
4. Identify what's wrong or missing
5. Refine prompt
6. Repeat until you get excellent output

**Goal**: Practice the refinement process

---

### Exercise 3: Prompt Template Creation (60 minutes)

1. Think of 3 tasks you do regularly
2. Create prompt templates for each
3. Test them with AI
4. Refine templates
5. Save them for reuse

**Goal**: Build your personal prompt library

---

## Summary

Effective prompting is a skill that multiplies your AI productivity. Remember:

**The Five Components:**
1. Context (where/what)
2. Task (do what)
3. Constraints (limits)
4. Format (how to present)
5. Examples (show, don't tell)

**The Key Principles:**
- Be specific, not vague
- Provide relevant context
- Define clear constraints
- Request specific formats
- Use examples liberally
- Iterate and refine
- Match your knowledge level

**The Golden Rule:**
The better your prompt, the better the output. Spend time crafting prompts, not fixing bad code.

In the next lesson, we'll explore **code generation best practices** - how to generate production-ready code with AI assistance.

---

## Additional Resources

- [Prompt Engineering Guide](https://www.promptingguide.ai/)
- [OpenAI Prompt Engineering](https://platform.openai.com/docs/guides/prompt-engineering)
- [Anthropic Prompt Library](https://docs.anthropic.com/claude/prompt-library)

**Next Lesson**: [03-code-generation.md](./03-code-generation.md) - Generate clean, production-ready code with AI.
