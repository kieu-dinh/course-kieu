# Exercise 03 - GitHub Flow

## Objective

Push your project to GitHub and create a Pull Request.

---

## Part 1: Create GitHub Repository

1. Go to github.com
2. Click "+" → "New repository"
3. Name: `git-practice`
4. Keep it **public**
5. **Don't** check "Add README"
6. Click "Create repository"

---

## Part 2: Push to GitHub

If you haven't set up SSH yet, do it now (see lesson 03).

```bash
# In your git-practice folder
git remote add origin git@github.com:YOUR-USERNAME/git-practice.git

# Push main branch
git push -u origin main
```

Refresh GitHub page - your code is there!

---

## Part 3: Create a Feature with PR

### Step 1: Create branch

```bash
git checkout -b feature/improve-styles
```

### Step 2: Make changes

Update `style.css`:
```css
body {
  font-family: system-ui, sans-serif;
  margin: 40px;
  background: #f5f5f5;
  color: #333;
}

h1 {
  color: #0066cc;
}

a {
  color: #0066cc;
  text-decoration: none;
}

a:hover {
  text-decoration: underline;
}
```

### Step 3: Commit and push

```bash
git add .
git commit -m "Improve styling"
git push -u origin feature/improve-styles
```

### Step 4: Create Pull Request

1. Go to GitHub
2. You'll see "Compare & pull request" button - click it
3. Add a title: "Improve styling"
4. Add description: "Added better colors and typography"
5. Click "Create pull request"

### Step 5: Review and Merge

1. Look at the "Files changed" tab
2. Click "Merge pull request"
3. Click "Confirm merge"
4. Click "Delete branch" (on GitHub)

### Step 6: Update local

```bash
git checkout main
git pull
git branch -d feature/improve-styles
```

---

## The Complete GitHub Flow

```
1. Pull latest main
   git checkout main
   git pull

2. Create feature branch
   git checkout -b feature/name

3. Work and commit
   git add .
   git commit -m "message"

4. Push to GitHub
   git push -u origin feature/name

5. Create PR on GitHub

6. Review and merge on GitHub

7. Update local
   git checkout main
   git pull
   git branch -d feature/name
```

---

## Checklist

- [ ] Repository created on GitHub
- [ ] Code pushed to GitHub
- [ ] Feature branch created
- [ ] Changes pushed
- [ ] Pull Request created
- [ ] PR merged
- [ ] Local updated with `git pull`

---

## Congratulations!

You now understand Git and GitHub! This is how professional teams work.

**Next Module:** [04 - PHP Algorithms](../../04-algorithmique-php/)
