# Lesson 02 - Branches

## What are Branches?

Branches let you work on different features without affecting the main code.

```
         feature-login
              ↓
        ○───○───○
       /
main ○───○───○───○───○
              \
               ○───○
                  ↑
            feature-cart
```

Each branch is independent until you merge it.

---

## Why Use Branches?

- Work on a feature without breaking main code
- Experiment safely
- Multiple people can work on different features
- Easy to discard if something doesn't work

---

## Main Branch

The default branch is called `main` (or `master` in older repos).

This is your "production-ready" code.

**Rule:** Never commit directly to `main`. Use branches!

---

## Branch Commands

### See all branches

```bash
git branch
```

The `*` shows your current branch.

### Create a new branch

```bash
git branch feature-name
```

### Switch to a branch

```bash
git checkout feature-name
# or (newer)
git switch feature-name
```

### Create and switch in one command

```bash
git checkout -b feature-name
# or
git switch -c feature-name
```

### Delete a branch

```bash
git branch -d feature-name
```

---

## Branch Workflow

```bash
# 1. Start from main
git checkout main

# 2. Create feature branch
git checkout -b add-contact-page

# 3. Make changes, commit
git add .
git commit -m "Add contact page"

# 4. More changes, more commits
git add .
git commit -m "Style contact form"

# 5. Switch back to main
git checkout main

# 6. Merge feature into main
git merge add-contact-page

# 7. Delete the feature branch
git branch -d add-contact-page
```

---

## Merging

Merge brings changes from one branch into another.

```bash
git checkout main          # Go to target branch
git merge feature-branch   # Merge feature into main
```

Visual:
```
Before:
main:    ○───○───○
                  \
feature:           ○───○

After merge:
main:    ○───○───○───────○ (merge commit)
                  \     /
feature:           ○───○
```

---

## Merge Conflicts

If same lines changed in both branches, Git can't auto-merge.

```
<<<<<<< HEAD
Your changes in main
=======
Changes from feature branch
>>>>>>> feature-branch
```

To resolve:
1. Open the file
2. Choose which code to keep (or combine)
3. Remove the `<<<<`, `====`, `>>>>` markers
4. Save and commit

---

## Branch Naming

Good names:
- `feature/login-form`
- `fix/header-bug`
- `update/readme`

Bad names:
- `test`
- `new`
- `asdf`

---

## Quick Reference

| Command | What it does |
|---------|--------------|
| `git branch` | List branches |
| `git branch name` | Create branch |
| `git checkout name` | Switch branch |
| `git checkout -b name` | Create + switch |
| `git merge name` | Merge into current |
| `git branch -d name` | Delete branch |

---

## Practice

1. Create a new branch `test-feature`
2. Create a new file in that branch
3. Commit it
4. Switch back to `main`
5. Notice the file is gone (it's in the other branch!)
6. Merge `test-feature` into `main`
7. File appears!

---

## Next

[Lesson 03: GitHub](./03-github.md)
