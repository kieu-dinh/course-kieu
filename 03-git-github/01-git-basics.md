# Lesson 01 - Git Basics

## What is Git?

Git is a **version control system**. It:
- Tracks every change you make
- Lets you go back in time
- Allows multiple people to work together

---

## Setup (One Time)

Configure your identity:

```bash
git config --global user.name "Your Name"
git config --global user.email "your@email.com"
```

Verify:
```bash
git config --list
```

---

## Core Concepts

### Repository (Repo)

A project folder tracked by Git.

### Commit

A snapshot of your project at a point in time. Like a save point in a video game.

### The Three Areas

```
┌─────────────────┐
│  Working Dir    │  ← Your actual files
│        ↓        │
│    git add      │
│        ↓        │
│  Staging Area   │  ← Files ready to commit
│        ↓        │
│   git commit    │
│        ↓        │
│  Repository     │  ← Saved history
└─────────────────┘
```

---

## Essential Commands

### Create a New Repo

```bash
cd my-project
git init
```

This creates a hidden `.git` folder.

### Check Status

```bash
git status
```

Shows:
- Files changed but not staged
- Files staged but not committed
- Current branch

**Run this often!**

### Stage Files

```bash
git add filename.html      # Add one file
git add .                  # Add all changed files
```

### Commit

```bash
git commit -m "Your message describing the change"
```

Good commit messages:
- ✅ "Add contact form to homepage"
- ✅ "Fix navigation on mobile"
- ❌ "Update"
- ❌ "asdfasdf"

### View History

```bash
git log              # Full log
git log --oneline    # Compact view
```

---

## Complete Workflow

```bash
# 1. Check what changed
git status

# 2. Stage changes
git add .

# 3. Commit with message
git commit -m "Add new feature"

# 4. Check history
git log --oneline
```

---

## Undo Changes

### Unstage a file

```bash
git restore --staged filename.html
```

### Discard changes (before staging)

```bash
git restore filename.html
```

⚠️ This loses your changes permanently!

### Go back to previous commit

```bash
git log --oneline         # Find commit hash
git checkout abc123       # Go to that commit (read-only)
git checkout main         # Go back to latest
```

---

## .gitignore

Files you DON'T want tracked:

Create `.gitignore`:
```
node_modules/
.env
.DS_Store
*.log
```

Git will ignore these files.

---

## Quick Reference

| Command | What it does |
|---------|--------------|
| `git init` | Create new repo |
| `git status` | Check status |
| `git add .` | Stage all changes |
| `git commit -m "msg"` | Save snapshot |
| `git log --oneline` | View history |
| `git restore file` | Discard changes |

---

## Practice

1. Create a new folder
2. `git init`
3. Create `index.html`
4. `git status` (see untracked file)
5. `git add index.html`
6. `git status` (see staged file)
7. `git commit -m "Initial commit"`
8. `git log`

---

## Next

[Lesson 02: Branches](./02-branches.md)
