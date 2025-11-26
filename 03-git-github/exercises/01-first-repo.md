# Exercise 01 - First Repository

## Objective

Create your first Git repository and make commits.

---

## Task

Create a project with multiple commits.

### Steps

1. Create a new folder:
```bash
mkdir git-practice
cd git-practice
```

2. Initialize Git:
```bash
git init
```

3. Check status:
```bash
git status
```

4. Create `index.html`:
```html
<!DOCTYPE html>
<html>
<head>
  <title>Git Practice</title>
</head>
<body>
  <h1>Hello Git!</h1>
</body>
</html>
```

5. Stage and commit:
```bash
git add index.html
git commit -m "Initial commit: add index.html"
```

6. Add more content to `index.html`:
```html
<p>This is my first Git project.</p>
```

7. Commit the change:
```bash
git add .
git commit -m "Add paragraph to index.html"
```

8. Create `style.css`:
```css
body {
  font-family: sans-serif;
  margin: 40px;
}
```

9. Link CSS in HTML and commit:
```bash
git add .
git commit -m "Add CSS styling"
```

10. View history:
```bash
git log --oneline
```

---

## Expected Output

Your `git log --oneline` should show something like:

```
abc1234 Add CSS styling
def5678 Add paragraph to index.html
ghi9012 Initial commit: add index.html
```

---

## Checklist

- [ ] Repo initialized with `git init`
- [ ] At least 3 commits made
- [ ] Each commit has a clear message
- [ ] `git log` shows all commits
- [ ] `git status` shows "nothing to commit"

---

## Bonus

1. Create a `.gitignore` file that ignores `.DS_Store`
2. Make more changes and commit them
3. Use `git diff` before staging to see changes

---

## Next

[Exercise 02: Branch Practice](./02-branch-practice.md)
