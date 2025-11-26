# Lesson 03 - GitHub

## What is GitHub?

GitHub is a website that hosts Git repositories in the cloud.

- Backup your code
- Share with others
- Collaborate on projects
- Show your work (portfolio)

---

## Local vs Remote

```
Your Computer              GitHub
┌──────────────┐         ┌──────────────┐
│ Local Repo   │ ──────> │ Remote Repo  │
│              │  push   │              │
│              │ <────── │              │
│              │  pull   │              │
└──────────────┘         └──────────────┘
```

---

## Create a GitHub Repository

1. Go to github.com
2. Click "+" → "New repository"
3. Name it (e.g., `my-project`)
4. Keep it public or private
5. **Don't** add README (we'll push existing code)
6. Click "Create repository"

---

## Connect Local to GitHub

### First time setup (SSH key)

Generate SSH key:
```bash
ssh-keygen -t ed25519 -C "your@email.com"
```

Press Enter for all prompts.

Copy the public key:
```bash
cat ~/.ssh/id_ed25519.pub
```

Add to GitHub:
1. GitHub → Settings → SSH and GPG keys
2. Click "New SSH key"
3. Paste the key
4. Save

Test connection:
```bash
ssh -T git@github.com
```

You should see "Hi username! You've successfully authenticated..."

---

## Push Existing Repo to GitHub

After creating repo on GitHub:

```bash
# Add GitHub as remote
git remote add origin git@github.com:username/repo-name.git

# Push to GitHub
git push -u origin main
```

The `-u` sets up tracking. After this, just use `git push`.

---

## Clone a Repository

Download an existing repo:

```bash
git clone git@github.com:username/repo-name.git
cd repo-name
```

---

## Push and Pull

### Push (send to GitHub)

```bash
git push
```

### Pull (get from GitHub)

```bash
git pull
```

Always pull before starting work!

---

## GitHub Workflow

```bash
# 1. Pull latest changes
git pull

# 2. Create feature branch
git checkout -b new-feature

# 3. Make changes, commit
git add .
git commit -m "Add feature"

# 4. Push branch to GitHub
git push -u origin new-feature

# 5. Create Pull Request on GitHub

# 6. After review, merge on GitHub

# 7. Locally, update main
git checkout main
git pull
```

---

## Pull Requests (PR)

A Pull Request asks to merge your branch into main.

On GitHub:
1. Push your branch
2. Click "Compare & pull request"
3. Add description
4. Request review
5. After approval, click "Merge"

PRs allow:
- Code review before merging
- Discussion
- Automated checks

---

## Typical Daily Workflow

```bash
# Morning: get latest
git checkout main
git pull

# Start feature
git checkout -b feature/my-task

# Work...
git add .
git commit -m "Progress on task"

# End of day: push
git push -u origin feature/my-task
```

---

## Quick Reference

| Command | What it does |
|---------|--------------|
| `git remote add origin url` | Connect to GitHub |
| `git push` | Upload to GitHub |
| `git pull` | Download from GitHub |
| `git clone url` | Copy repo from GitHub |
| `git push -u origin branch` | Push new branch |

---

## Exercises

Go to: [Exercises](./exercises/)
