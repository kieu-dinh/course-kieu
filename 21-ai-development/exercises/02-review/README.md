# Exercise 21.2 - Code Review with AI

## Objective

Learn how to use AI for effective code reviews to improve code quality.

---

## Task

Use AI to review your code for quality, performance, security, and best practices.

---

## What AI Can Review

### 1. Code Quality
- Readability and clarity
- Naming conventions
- Code duplication
- Complexity (cyclomatic complexity)
- Type hinting and documentation
- Function/method size

### 2. Performance
- N+1 query problems
- Inefficient algorithms
- Memory usage
- Caching opportunities
- Database query optimization

### 3. Security
- SQL injection vulnerabilities
- XSS vulnerabilities
- CSRF protection
- Authentication/authorization issues
- Sensitive data exposure
- Input validation

### 4. Architecture
- Design patterns
- Separation of concerns
- SOLID principles
- Coupling and cohesion
- Code organization

### 5. Best Practices
- Framework conventions
- Laravel conventions
- Error handling
- Testing approach
- Documentation

---

## Code Review Prompt Template

```
You are an expert code reviewer.
Review this [LANGUAGE] [CONTEXT] code.

Focus on:
1. Code quality and readability
2. Performance implications
3. Security vulnerabilities
4. Best practices

Provide:
1. Overall assessment
2. Critical issues (must fix)
3. Important improvements
4. Nice-to-have refactorings
5. Positive aspects

Code:
[INCLUDE CODE]

Context:
- This is used for: [USE]
- Expected scale: [SCALE]
- Current issues: [KNOWN ISSUES]

Be constructive and explain why each suggestion matters.
```

---

## Reviewing a Controller

### Code to Review

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = DB::table('posts')
            ->get();

        if ($request->has('search')) {
            $filtered = [];
            foreach ($posts as $post) {
                if (strpos(strtolower($post->title), strtolower($request->search)) !== false) {
                    $filtered[] = $post;
                }
            }
            $posts = $filtered;
        }

        if ($request->has('sort_by')) {
            if ($request->sort_by === 'date') {
                usort($posts, function($a, $b) {
                    return strtotime($a->created_at) - strtotime($b->created_at);
                });
            } elseif ($request->sort_by === 'title') {
                usort($posts, function($a, $b) {
                    return strcmp($a->title, $b->title);
                });
            }
        }

        $result = [];
        foreach ($posts as $post) {
            $user = User::find($post->user_id);
            $result[] = [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'author' => $user->name,
                'date' => $post->created_at
            ];
        }

        return response()->json([
            'data' => $result,
            'count' => count($result)
        ]);
    }

    public function store(Request $request)
    {
        $title = $request->get('title', '');
        $content = $request->get('content', '');

        if (strlen($title) < 3) {
            return response()->json(['error' => 'Title too short'], 400);
        }

        if (empty($content)) {
            return response()->json(['error' => 'Content is required'], 400);
        }

        if (strlen($content) < 10) {
            return response()->json(['error' => 'Content too short'], 400);
        }

        $post = new Post();
        $post->title = $title;
        $post->content = $content;
        $post->user_id = Auth::id();
        $post->save();

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'author_id' => $post->user_id
        ], 201);
    }

    public function show($id)
    {
        $post = DB::table('posts')->where('id', $id)->first();

        if (!$post) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $user = User::find($post->user_id);

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'author' => $user->name,
            'date' => $post->created_at
        ]);
    }

    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if (Auth::id() !== $post->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->has('title')) {
            $post->title = $request->get('title');
        }

        if ($request->has('content')) {
            $post->content = $request->get('content');
        }

        $post->save();

        return response()->json([
            'id' => $post->id,
            'title' => $post->title,
            'content' => $post->content
        ]);
    }

    public function delete($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['error' => 'Not found'], 404);
        }

        if (Auth::id() !== $post->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
```

### Detailed Review Prompt

```
You are a senior Laravel developer and architect.

Please review this Post controller thoroughly.

Assessment criteria:
1. Code quality and readability
2. Laravel best practices
3. Performance for 100K+ posts
4. Security vulnerabilities
5. API design

Current issues I know about:
- N+1 problem in index()
- Manual filtering instead of database queries
- Inconsistent response format

Code:
[PASTE CODE ABOVE]

Questions:
1. What are the critical issues I must fix immediately?
2. What security vulnerabilities exist?
3. How should I refactor this controller?
4. What patterns should I use?
5. How would you structure this for production?

Please be specific and provide code examples for your suggestions.
```

---

## Review Checklist

Use this checklist when reviewing code with AI:

```
## Performance Checks
- [ ] Are there N+1 queries?
- [ ] Is pagination implemented?
- [ ] Are relationships eager loaded?
- [ ] Are unnecessary columns selected?
- [ ] Is there database indexing?
- [ ] Could results be cached?

## Security Checks
- [ ] Is input validated?
- [ ] Are authorization checks present?
- [ ] Is mass assignment prevented?
- [ ] Are sensitive fields hidden?
- [ ] Is SQL injection possible?
- [ ] Is XSS possible?
- [ ] Is CSRF token checked?

## Code Quality Checks
- [ ] Is code readable?
- [ ] Are names clear?
- [ ] Is there code duplication?
- [ ] Are methods too long?
- [ ] Is type hinting used?
- [ ] Is documentation present?
- [ ] Are edge cases handled?

## Architecture Checks
- [ ] Does it follow Laravel conventions?
- [ ] Is separation of concerns respected?
- [ ] Are design patterns used appropriately?
- [ ] Is the controller too large?
- [ ] Could business logic be extracted?
- [ ] Are dependencies injected?

## Testing Checks
- [ ] Are there tests?
- [ ] Do tests cover happy path?
- [ ] Do tests cover edge cases?
- [ ] Do tests cover errors?
- [ ] Is test structure clear?
```

---

## Review Process

### Step 1: Ask for High-Level Assessment

```
Review this code and give me:
1. Overall quality rating (1-10)
2. Top 3 critical issues
3. Top 3 improvements
4. Top 3 good practices it follows

[CODE]
```

### Step 2: Dive Deep into Issues

```
For the N+1 query problem you mentioned:

1. Show me the problem in detail
2. Explain why it's a performance risk at scale
3. Provide the complete refactored solution
4. Explain how to test this is fixed
```

### Step 3: Request Best Practices

```
What are the best practices I should follow for:
1. API response formatting
2. Error handling
3. Authorization patterns
4. Query optimization

In a Laravel API context.
```

### Step 4: Plan Refactoring

```
Create a refactoring plan that:
1. Prioritizes critical issues
2. Estimates effort for each change
3. Shows dependencies between changes
4. Maintains backward compatibility

Include specific code examples.
```

---

## Real-World Review Examples

### Example 1: Service Class Review

```
Review this service for:
1. Does it follow SRP?
2. Should it be split into multiple services?
3. Are there hidden dependencies?
4. Could it be tested better?

[SERVICE CODE]

Also suggest a test suite for it.
```

### Example 2: Query Optimization

```
This query gets slow with large datasets:
[QUERY]

Diagnose:
1. Why is it slow?
2. What indexes would help?
3. Could pagination help?
4. Should I use caching?

Usage: Gets top products for 50K users daily
Scale: 500K products, 100K users
Acceptable response time: < 100ms
```

### Example 3: Security Audit

```
Security review of this authentication code:

[CODE]

Check for:
1. Token validation issues
2. Session handling
3. Password security
4. CSRF protection
5. Authorization flaws
6. Input validation
7. Sensitive data exposure

Assume this is production code handling user data.
```

---

## Iterative Review Process

### Round 1: Get Issues

```
Review this code for issues:
[CODE]

Just list the issues, don't fix them yet.
```

### Round 2: Understand Issues

```
For each issue:
1. Why is it a problem?
2. What's the impact?
3. How would I test for this?

Issues:
[ISSUES FROM ROUND 1]
```

### Round 3: Get Solutions

```
For each issue, provide:
1. Refactored code
2. Tests that verify the fix
3. Explanation of why this is better

Issues:
[ISSUES]
```

### Round 4: Implementation Plan

```
Create an implementation plan:
1. Which issues to fix first?
2. Estimated time for each?
3. Dependencies between changes?
4. How to test the full refactoring?
```

---

## Best Practices for AI Review

1. **Be Specific**: Show exact code, not just descriptions
2. **Give Context**: What does this code do and at what scale?
3. **Ask Why**: Don't just ask "is this good?" but "why is this better?"
4. **Request Examples**: Always ask for code examples
5. **Verify Understanding**: Have AI explain the issue before solving
6. **Compare Approaches**: Ask for multiple solutions and trade-offs
7. **Test Suggestions**: Always test AI's suggestions in your code
8. **Ask Questions**: "What else should I check?" or "What could I miss?"

---

## Checklist

- [ ] Review 3 pieces of your code with AI
- [ ] Document critical issues found
- [ ] Create refactoring plan
- [ ] Understand why each suggestion is better
- [ ] Implement fixes
- [ ] Test implementations
- [ ] Review again to verify improvements

---

## Bonus Challenges

1. Create a code review template for your team
2. Document common issues found in reviews
3. Create before/after comparisons
4. Build a code review checklist
5. Compare AI review vs peer review
6. Document lessons learned from reviews

---

## Code Review Principles

- **Constructive Feedback**: Focus on code, not person
- **Explain Trade-offs**: Show why one approach is better
- **Provide Examples**: Don't just criticize, show the fix
- **Consider Context**: Not all code needs enterprise patterns
- **Balance Speed and Quality**: Perfection shouldn't block progress
- **Learn from Reviews**: Make them teachable moments
