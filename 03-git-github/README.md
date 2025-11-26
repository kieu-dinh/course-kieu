# Module 03 - Git & GitHub

Every professional developer uses Git. It tracks changes to your code and lets you collaborate with others.

## Learning Objectives

By the end of this module, you will:
- [ ] Understand what version control is
- [ ] Create repositories
- [ ] Commit changes
- [ ] Work with branches
- [ ] Push to GitHub
- [ ] Collaborate using pull requests

## Duration

3-4 days

## Lessons

| # | Lesson | Description |
|---|--------|-------------|
| 01 | [Git Basics](./01-git-basics.md) | Init, add, commit, status |
| 02 | [Branches](./02-branches.md) | Create, switch, merge |
| 03 | [GitHub](./03-github.md) | Remote repos, push, pull |

## Exercises

| # | Exercise | What you'll do |
|---|----------|----------------|
| 01 | [First Repo](./exercises/01-first-repo.md) | Create and commit |
| 02 | [Branch Practice](./exercises/02-branch-practice.md) | Branch workflow |
| 03 | [GitHub Flow](./exercises/03-github-flow.md) | Push and PR |

---

## Why Git?

Without Git:
```
project/
├── index.html
├── index_v2.html
├── index_v2_final.html
├── index_v2_final_REAL.html
└── index_v2_final_REAL_fixed.html   😱
```

With Git:
```
project/
└── index.html   ✅

Git tracks all versions internally!
```

---

## Key Concepts Preview

```
Your Computer                         GitHub (cloud)
┌─────────────────┐                  ┌─────────────────┐
│  Working Dir    │                  │  Remote Repo    │
│  (your files)   │                  │  (backup/share) │
│        ↓        │                  │                 │
│  git add        │                  │                 │
│        ↓        │     git push     │                 │
│  Staging Area   │ ───────────────> │                 │
│        ↓        │                  │                 │
│  git commit     │ <─────────────── │                 │
│        ↓        │     git pull     │                 │
│  Local Repo     │                  │                 │
└─────────────────┘                  └─────────────────┘
```

---

Start with: [Lesson 01: Git Basics](./01-git-basics.md)
