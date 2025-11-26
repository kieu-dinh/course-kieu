# Module 00 - Environment Preparation

Before you start coding, you need to set up your work environment. This module will guide you through installing all the necessary tools.

## Learning Objectives

By the end of this module, you will have:
- [ ] VS Code installed and configured
- [ ] A web browser with developer tools
- [ ] PHP installed via Laravel Herd
- [ ] Node.js installed
- [ ] A GitHub account
- [ ] A basic understanding of the terminal

## Duration

1-2 days

---

## 1. Install Visual Studio Code

VS Code is the code editor we'll use throughout this training.

### Steps

1. Go to [https://code.visualstudio.com/](https://code.visualstudio.com/)
2. Download the version for your operating system (Mac/Windows)
3. Install it
4. Open VS Code

### Essential Extensions to Install

In VS Code, click on the Extensions icon (square icon on the left sidebar) and search for:

| Extension | Why |
|-----------|-----|
| **PHP Intelephense** | PHP code intelligence |
| **Tailwind CSS IntelliSense** | Tailwind autocompletion |
| **Alpine.js IntelliSense** | Alpine autocompletion |
| **Laravel Blade Snippets** | Laravel template help |
| **GitLens** | Better Git integration |
| **Auto Rename Tag** | Auto rename HTML tags |
| **Prettier** | Code formatting |

### Verify

Open VS Code. You should see a welcome screen.

---

## 2. Set Up Your Browser

We recommend **Google Chrome** or **Firefox** for development because of their excellent developer tools.

### Steps

1. If you don't have Chrome, download it from [https://www.google.com/chrome/](https://www.google.com/chrome/)
2. Open Chrome
3. Right-click anywhere on a webpage and select "Inspect" (or press F12)
4. You should see the Developer Tools panel

### What You'll See

The Developer Tools have several tabs:
- **Elements**: See and modify the HTML/CSS of a page
- **Console**: See JavaScript errors and messages
- **Network**: See all HTTP requests (we'll use this a lot!)
- **Sources**: Debug JavaScript code

Don't worry if this seems complex now. We'll learn to use these tools in Module 01.

---

## 3. Install Laravel Herd (Mac) or Laragon (Windows)

### For Mac Users: Laravel Herd

Laravel Herd is the easiest way to get PHP running on Mac.

1. Go to [https://herd.laravel.com/](https://herd.laravel.com/)
2. Download Herd
3. Install it (drag to Applications)
4. Open Herd
5. Follow the setup wizard

Herd automatically installs:
- PHP (multiple versions)
- Composer (PHP package manager)
- Node.js & npm

### For Windows Users: Laragon

1. Go to [https://laragon.org/download/](https://laragon.org/download/)
2. Download Laragon Full
3. Install it
4. Open Laragon

### Verify PHP Installation

Open your terminal:
- **Mac**: Press Cmd + Space, type "Terminal", press Enter
- **Windows**: Open Laragon, click "Terminal"

Type this command and press Enter:

```bash
php -v
```

You should see something like:
```
PHP 8.3.x (cli) ...
```

If you see a version number, PHP is installed correctly!

---

## 4. Install Node.js (if not included)

Node.js is needed for frontend tools like Tailwind and Vite.

If Herd or Laragon didn't install Node.js:

1. Go to [https://nodejs.org/](https://nodejs.org/)
2. Download the LTS version
3. Install it

### Verify

In your terminal:

```bash
node -v
npm -v
```

You should see version numbers for both.

---

## 5. Create a GitHub Account

GitHub is where developers store and share code. You'll use it to save your progress.

### Steps

1. Go to [https://github.com/](https://github.com/)
2. Click "Sign up"
3. Choose a username (this will be public, choose wisely!)
4. Use your email
5. Create a password
6. Complete the verification

### Verify

You should be able to log in to GitHub.

---

## 6. Learn Basic Terminal Commands

The terminal might look scary, but you only need a few commands to start.

### Open your terminal

- **Mac**: Cmd + Space, type "Terminal"
- **Windows**: Use Laragon's terminal or search for "Command Prompt"

### Essential Commands

| Command | What it does | Example |
|---------|-------------|---------|
| `pwd` | Shows where you are (current folder) | `pwd` |
| `ls` | Lists files in current folder | `ls` |
| `cd` | Changes directory (folder) | `cd Documents` |
| `cd ..` | Goes up one folder | `cd ..` |
| `mkdir` | Creates a new folder | `mkdir my-project` |
| `clear` | Clears the terminal screen | `clear` |

### Try It!

```bash
# See where you are
pwd

# List files
ls

# Navigate to the course directory
cd /Users/pouget/Projects/cours-kieu

# Check you're in the right place
pwd
# Should show: /Users/pouget/Projects/cours-kieu

# List files to see the modules
ls

# Create a test file in Module 00
cd 00-preparation
touch test.txt

# List to see your file
ls
```

---

## 7. Open the Course Folder

**IMPORTANT**: The course folder already exists! You don't need to create anything.

All your work will be done in: `/Users/pouget/Projects/cours-kieu`

### Steps

1. Open your terminal

2. Navigate to the course directory

```bash
cd /Users/pouget/Projects/cours-kieu
```

3. Verify you're in the right place

```bash
pwd
# Should show: /Users/pouget/Projects/cours-kieu

# List all modules
ls
# Should show: 00-preparation, 01-theorie-web, 02-html-css, etc.
```

4. Open this folder in VS Code

```bash
code .
```

(The `.` means "current folder")

You should now see VS Code open with the course folder showing all modules on the left sidebar!

**From now on, ALWAYS work from this directory. Never create folders outside of it.**

---

## Exercise: Verification Checklist

Complete this checklist to make sure everything is working:

### File: `exercises/00-verification.md`

Check off each item in the verification file located at `00-preparation/exercises/00-verification.md`:

```markdown
# Environment Verification Checklist

## VS Code
- [ ] VS Code opens correctly
- [ ] I can create a new file (File > New File)
- [ ] I installed PHP Intelephense extension
- [ ] I installed Tailwind CSS IntelliSense extension

## Browser
- [ ] I can open Developer Tools (F12 or right-click > Inspect)
- [ ] I can see the Elements tab
- [ ] I can see the Network tab

## Terminal
- [ ] I can open the terminal
- [ ] `php -v` shows a version number
- [ ] `node -v` shows a version number
- [ ] `npm -v` shows a version number
- [ ] `composer -v` shows Composer information

## GitHub
- [ ] I have a GitHub account
- [ ] I can log in to github.com

## Course Folder
- [ ] I navigated to /Users/pouget/Projects/cours-kieu
- [ ] I can open it in VS Code with `code .`
- [ ] I can see all modules (00-preparation, 01-theorie-web, etc.) in VS Code sidebar
```

---

## What's Next?

Once everything is checked off, you're ready to start learning about how the web works!

Go to: [Module 01 - Web Theory](../01-theorie-web/)

---

## Troubleshooting

### "php is not recognized" or "command not found: php"

- **Mac**: Make sure Herd is running (check the menu bar icon)
- **Windows**: Make sure Laragon is started, use Laragon's terminal

### "code is not recognized" (the `code .` command)

In VS Code:
1. Press Cmd+Shift+P (Mac) or Ctrl+Shift+P (Windows)
2. Type "shell command"
3. Select "Shell Command: Install 'code' command in PATH"

### I'm stuck!

Ask Claude! Describe your problem clearly:
- What you tried to do
- What command you ran
- What error message you see
- Your operating system (Mac/Windows)
