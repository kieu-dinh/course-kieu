# Exercise 02 - Branch Practice

## Objective

Practice creating, switching, and merging branches.

---

## Task

Use the repo from Exercise 01 (or create a new one).

### Part 1: Create a Feature Branch

```bash
# Check current branch
git branch

# Create and switch to new branch
git checkout -b feature/add-about

# Verify you're on new branch
git branch
```

### Part 2: Work on the Branch

1. Create `about.html`:
```html
<!DOCTYPE html>
<html>
<head>
  <title>About</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h1>About Me</h1>
  <p>I'm learning Git!</p>
  <a href="index.html">Back to Home</a>
</body>
</html>
```

2. Commit:
```bash
git add .
git commit -m "Add about page"
```

3. Add link in `index.html`:
```html
<a href="about.html">About</a>
```

4. Commit:
```bash
git add .
git commit -m "Add link to about page"
```

### Part 3: Merge to Main

```bash
# Switch to main
git checkout main

# Check: about.html shouldn't exist here yet
ls

# Merge feature branch
git merge feature/add-about

# Check: about.html now exists!
ls

# View history
git log --oneline
```

### Part 4: Delete Feature Branch

```bash
git branch -d feature/add-about

# Verify it's gone
git branch
```

---

## Challenge: Create Another Feature

1. Create branch `feature/add-contact`
2. Add `contact.html`
3. Add navigation links
4. Commit changes
5. Merge to main
6. Delete the branch

---

## Checklist

- [ ] Created feature branch
- [ ] Made commits on feature branch
- [ ] Merged to main
- [ ] Deleted feature branch
- [ ] `git log` shows merge
- [ ] Only `main` branch remains

---

## Next

[Exercise 03: GitHub Flow](./03-github-flow.md)
