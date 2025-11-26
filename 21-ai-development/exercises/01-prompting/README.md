# Exercise 21.1 - AI Prompting Practice

## Objective

Learn effective prompting techniques to get better results from AI models like ChatGPT, Claude, etc.

---

## Task

Practice writing different types of prompts and learn prompt engineering principles.

---

## Prompt Engineering Principles

### 1. Be Specific and Clear

**Bad Prompt:**
```
Write code for a web app
```

**Good Prompt:**
```
Write a Laravel controller with CRUD endpoints for managing users.
Include validation, proper HTTP status codes, and error handling.
Return JSON responses and use dependency injection.
```

### 2. Provide Context

**Bad Prompt:**
```
What's wrong with this code?
```

**Good Prompt:**
```
I'm building a Laravel API for managing products.
Here's my controller for getting all products.
It's running slow for large datasets.
What's wrong and how can I optimize it?

[include code]
```

### 3. Define Output Format

**Bad Prompt:**
```
Explain database indexing
```

**Good Prompt:**
```
Explain database indexing in 3 paragraphs.
For each paragraph:
1. Start with a clear statement
2. Provide one practical example
3. End with a key takeaway

Target audience: junior developers
```

### 4. Use Role Playing

**Bad Prompt:**
```
Explain REST APIs
```

**Good Prompt:**
```
You are an expert Laravel developer with 10 years of experience.
Explain how to design a REST API for a blog application.
Include best practices, common pitfalls, and code examples.
Assume the reader knows PHP but is new to API design.
```

### 5. Few-Shot Examples

**Bad Prompt:**
```
Generate function names
```

**Good Prompt:**
```
Generate function names following these patterns:

Examples:
- getActiveUsers() - retrieves active users
- calculateTotalPrice() - computes total price
- sendWelcomeEmail() - sends welcome email

Now generate 3 more function names for:
1. A function that validates an email address
2. A function that formats a date as ISO-8601
3. A function that deletes expired sessions
```

### 6. Give Constraints

**Bad Prompt:**
```
Write code to process orders
```

**Good Prompt:**
```
Write a PHP function to process orders with these constraints:
- Must be less than 50 lines of code
- Must handle payment processing, email notification, and inventory update
- Must include error handling for each step
- Must use transactions to ensure data consistency
- Performance requirement: process 1000+ orders per minute
```

### 7. Use Temperature (if available)

For creative tasks (lower = more focused):
```
Generate creative blog post titles about Laravel.
Temperature: 0.7 (balanced between creativity and consistency)
```

For factual tasks (lower = more deterministic):
```
Write documentation for an API endpoint.
Temperature: 0.2 (highly consistent and factual)
```

---

## Prompt Templates

### Template 1: Code Debugging

```
I'm debugging a [LANGUAGE] [APPLICATION_TYPE].

Problem:
[DESCRIBE THE PROBLEM]

Expected behavior:
[WHAT SHOULD HAPPEN]

Actual behavior:
[WHAT IS HAPPENING]

Code:
[INCLUDE RELEVANT CODE]

Questions:
1. What's the root cause?
2. How do I fix it?
3. How can I prevent this in the future?
```

### Template 2: Code Improvement

```
I have [LANGUAGE] code for [TASK].
Can you improve it for [SPECIFIC_GOAL]?

Current approach:
[INCLUDE CODE]

Constraints:
- [CONSTRAINT 1]
- [CONSTRAINT 2]

I want:
[SPECIFIC_REQUIREMENT]

Explain your changes and why they're better.
```

### Template 3: Learning/Explanation

```
Explain [TOPIC] to me.

Context:
- My experience level: [LEVEL]
- I'm working on: [PROJECT]
- I already understand: [WHAT_THEY_KNOW]

Please:
1. Give a brief overview (2-3 sentences)
2. Provide a practical example in [LANGUAGE]
3. Share 3 best practices
4. Mention common mistakes

Format: Keep it concise but detailed
```

### Template 4: Architecture Design

```
I'm designing a [SYSTEM] for [USE_CASE].

Requirements:
- [REQUIREMENT 1]
- [REQUIREMENT 2]

Constraints:
- [CONSTRAINT 1]
- [CONSTRAINT 2]

Current plan:
[DESCRIBE APPROACH]

Questions:
1. Is my approach sound?
2. What am I missing?
3. How should I structure this?

Technology stack: [STACK]
```

---

## Prompt Chains

Break complex tasks into smaller prompts:

### Multi-Step Process

**Prompt 1:** Analyze the problem
```
Analyze this database schema for performance issues:
[SCHEMA]

List:
1. Potential bottlenecks
2. Missing indexes
3. N+1 query opportunities
```

**Prompt 2:** Generate solution
```
Based on these issues:
[ISSUES FROM PREVIOUS PROMPT]

Generate:
1. Index creation statements
2. Query optimization examples
3. Caching strategies

Use Laravel syntax.
```

**Prompt 3:** Implement and test
```
Write test cases for these optimizations:
[OPTIMIZATIONS FROM PREVIOUS PROMPT]

Include:
1. Performance baseline test
2. Test with optimized queries
3. Assertions comparing query counts
```

---

## Advanced Techniques

### 1. Chain-of-Thought Prompting

```
Solve this step-by-step:

Problem: Optimize an API endpoint that handles 10,000 concurrent requests.

Think through:
1. What could cause bottlenecks?
2. How would you identify them?
3. What solutions would you implement?
4. How would you verify improvements?

Show your reasoning for each step.
```

### 2. Reverse Role-Play

```
You are a user trying to use my API.
Here's my documentation.

Try to:
1. Use the API
2. Point out confusing parts
3. Suggest improvements
4. Test edge cases

[API DOCUMENTATION]
```

### 3. System Prompts

Set context that applies to all future prompts in conversation:

```
System: You are an expert Laravel architect.
You prioritize:
1. Performance
2. Security
3. Maintainability
4. Developer experience

For each code review, explain trade-offs and best practices.

Now, review this service class:
[CLASS CODE]
```

---

## Exercises

### Exercise 1: Debug a Problem

```
I have a Laravel application where users report slow load times.
The homepage shows 150 database queries.

Models:
- Product (many categories, many tags)
- Category
- Tag

Controller:
[INCLUDE YOUR CONTROLLER CODE]

Query:
[INCLUDE SLOW QUERY IF YOU HAVE ONE]

What's causing the N+1 issue and how do I fix it?
```

### Exercise 2: Design a Feature

```
I need to build a recommendation engine for an e-commerce site.

Requirements:
- Recommend products based on user purchase history
- Show top 5 recommendations per user
- Must work with 1M+ products and 100K+ users
- Update recommendations daily

Design the system including:
1. Database schema
2. Processing strategy
3. Caching approach
4. API endpoint design

Technology: Laravel + Redis
```

### Exercise 3: Improve Code Quality

```
Here's my order processing service.
It's becoming hard to maintain.

Can you help refactor it using:
1. Service layer pattern
2. Repository pattern
3. SOLID principles

[INCLUDE SERVICE CODE]

Explain why each change improves the code.
```

### Exercise 4: Learn a Concept

```
I need to understand Laravel Queues for my e-commerce API.

Context:
- I know basic Laravel
- I've used controllers and models
- I haven't done async tasks before

Teach me:
1. Why use queues? (real example)
2. How do they work? (step-by-step)
3. Common patterns (with code)
4. How to debug issues

Make it practical and actionable.
```

---

## Tips for Better Prompts

1. **Be Conversational**: AI responds better to friendly tone
2. **Iterate**: Refine prompts based on responses
3. **Give Examples**: Show what you want, not just tell
4. **Separate Concerns**: One task per prompt when learning
5. **Include Constraints**: Tell AI what's important
6. **Ask for Alternatives**: "Give me 3 different approaches"
7. **Request Explanations**: "Explain why" helps you learn
8. **Use Formatting**: Code blocks, lists make it clearer
9. **Share Context**: Your experience level, tech stack, goals
10. **Be Patient**: Good results take good prompts

---

## Checklist

- [ ] Write 3 debugging prompts
- [ ] Write 2 feature design prompts
- [ ] Write 2 learning/explanation prompts
- [ ] Practice prompt chaining (multi-step)
- [ ] Try role-playing prompts
- [ ] Test specificity vs vagueness
- [ ] Document your best prompts
- [ ] Share prompts that worked well

---

## Exercise Submission

Document 5 of your best prompts:

```markdown
# Prompt 1: [Title]
**Purpose**: [What you wanted to achieve]
**Type**: [Debug/Design/Learning/Code Review]
**Result**: [Was it effective?]

**Prompt**:
[INCLUDE FULL PROMPT]

**AI Response Summary**:
[HOW HELPFUL WAS IT?]

**What Worked**:
- [SPECIFIC ELEMENTS THAT WERE EFFECTIVE]

**Next Time**:
- [HOW YOU'D IMPROVE IT]

---
```

---

## Bonus Challenges

1. Create a prompt library for your team
2. Document prompt patterns that work
3. Share prompts with others and compare results
4. Measure AI response quality
5. Build a personal prompt template collection
6. Document lessons learned about AI interaction

---

## Resources

- OpenAI Prompt Engineering Guide
- Anthropic Claude Best Practices
- Prompt Engineering Institute
- Platform-specific documentation

---

## Real-World Scenarios

### Scenario 1: API Design
```
I'm designing a real-time messaging API for a chat application.
Expected scale: 1M daily active users.

Key features:
- Send/receive messages
- Typing indicators
- Read receipts
- User presence (online/offline)

Stack: Laravel + WebSockets

Help me design the API including:
1. Endpoint structure
2. Real-time architecture
3. Data model
4. Performance considerations
```

### Scenario 2: Bug Hunt
```
Our Laravel API is returning 500 errors randomly.
Error: "Allowed memory exceeded"
Happens during peak hours
Database has 50M+ records

Stack trace doesn't show our code.
Looks like it's happening in pagination.

What could cause memory exhaustion in pagination?
How do I debug this?
What solutions would you try?
```

### Scenario 3: Refactoring
```
I've inherited a 2000-line controller.
It needs refactoring badly.

What's your refactoring strategy for:
1. Breaking it into smaller pieces
2. Choosing appropriate patterns
3. Maintaining backward compatibility
4. Writing tests during refactoring

[INCLUDE CONTROLLER CODE]
```

Make these prompts as specific as possible!
