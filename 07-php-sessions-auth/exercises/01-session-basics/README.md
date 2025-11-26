# Exercise 7.1 - Session Basics

## Objective

Learn how to use PHP sessions to store data across page requests.

## Duration

1-2 hours

## Task

Create a simple counter application that remembers how many times a user has visited the page using sessions.

## Requirements

- [ ] Start a session using `session_start()`
- [ ] Create a visit counter that increments on each page load
- [ ] Display the number of visits
- [ ] Add a "Reset Counter" button that clears the session
- [ ] Display when the session started (store timestamp in session)
- [ ] Show the session ID

## Starter Files

Work in `counter.php` and `reset.php` - see starter code there.

## Expected Output

```
Welcome!
This is visit #5
Session started: 2024-01-15 14:30:22
Session ID: abc123def456

[Reset Counter]
```

## Checklist

- [ ] Session starts correctly
- [ ] Counter increments on refresh
- [ ] Reset button clears the counter
- [ ] Session data persists across page loads
- [ ] Code is clean and readable

## Tips

- Use `$_SESSION` superglobal to store data
- `session_start()` must be called before any output
- Use `session_destroy()` to clear all session data
- `$_SESSION['key']` to access session variables
