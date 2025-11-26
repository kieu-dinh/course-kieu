# Lesson 05 - Loading States and User Experience

**Duration**: 50-60 minutes
**Prerequisites**: Lesson 04 - Handling JSON Responses and Errors

---

## Introduction

Great applications don't just work - they **feel** great to use. When data takes time to load, users need to know what's happening. This lesson teaches you to create professional loading states and smooth user experiences.

**What you'll learn:**
- Show loading indicators while fetching data
- Disable buttons during operations
- Implement skeleton screens
- Add progress feedback
- Create smooth transitions
- Build professional UX patterns

---

## Why Loading States Matter

### The User's Perspective

**Without loading states:**
```
User clicks "Load Posts"
... nothing happens ...
... user clicks again ...
... still nothing ...
... user thinks it's broken ...
... finally posts appear 3 seconds later ...
User is confused and frustrated
```

**With loading states:**
```
User clicks "Load Posts"
Button shows "Loading..." and is disabled
Skeleton cards appear where posts will be
After 500ms, posts fade in smoothly
User feels in control and informed
```

### Key Principles

1. **Immediate Feedback** - Show something happened instantly
2. **Progress Indication** - Let users know how long to wait
3. **No Double Actions** - Disable buttons during operations
4. **Graceful Transitions** - Smooth, not jarring
5. **Context Preservation** - Keep UI stable (no layout shifts)

---

## Basic Loading State Pattern

### Simple Text Indicator

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loading Demo</title>
    <style>
        .loading { color: gray; font-style: italic; }
        .error { color: red; }
    </style>
</head>
<body>
    <button id="loadBtn">Load Data</button>
    <div id="status"></div>
    <div id="content"></div>

    <script>
        const button = document.getElementById('loadBtn');
        const status = document.getElementById('status');
        const content = document.getElementById('content');

        button.addEventListener('click', loadData);

        async function loadData() {
            // Show loading
            status.className = 'loading';
            status.textContent = 'Loading...';
            button.disabled = true;
            content.innerHTML = '';

            try {
                const response = await fetch('/api/posts');
                const result = await response.json();

                // Clear loading
                status.textContent = '';

                // Show content
                content.innerHTML = result.data.map(item =>
                    `<p>${item.title}</p>`
                ).join('');

            } catch (error) {
                // Show error
                status.className = 'error';
                status.textContent = 'Failed to load data';

            } finally {
                // Always re-enable button
                button.disabled = false;
            }
        }
    </script>
</body>
</html>
```

**Key points:**
- Disable button immediately
- Show loading message
- Clear previous content
- Always re-enable in `finally` block

---

## Loading Spinner

### CSS-Only Spinner

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spinner Demo</title>
    <style>
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .hidden { display: none; }
    </style>
</head>
<body>
    <button id="loadBtn">Load Posts</button>

    <div id="spinner" class="spinner hidden"></div>

    <div id="posts"></div>

    <script>
        const button = document.getElementById('loadBtn');
        const spinner = document.getElementById('spinner');
        const postsDiv = document.getElementById('posts');

        button.addEventListener('click', loadPosts);

        async function loadPosts() {
            // Show spinner
            showSpinner();
            button.disabled = true;
            postsDiv.innerHTML = '';

            try {
                const response = await fetch('/api/posts');
                const result = await response.json();

                // Display posts
                postsDiv.innerHTML = result.data.map(post => `
                    <article>
                        <h3>${post.title}</h3>
                        <p>${post.excerpt}</p>
                    </article>
                `).join('');

            } catch (error) {
                postsDiv.innerHTML = '<p class="error">Failed to load posts</p>';

            } finally {
                hideSpinner();
                button.disabled = false;
            }
        }

        function showSpinner() {
            spinner.classList.remove('hidden');
        }

        function hideSpinner() {
            spinner.classList.add('hidden');
        }
    </script>
</body>
</html>
```

---

## Skeleton Screens

Skeleton screens show content placeholders that look like the final content. Much better UX than spinners!

### Simple Skeleton

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Skeleton Demo</title>
    <style>
        .skeleton {
            background: linear-gradient(
                90deg,
                #f0f0f0 25%,
                #e0e0e0 50%,
                #f0f0f0 75%
            );
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .skeleton-title {
            height: 24px;
            width: 60%;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .skeleton-text {
            height: 16px;
            width: 100%;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .skeleton-text:last-child {
            width: 80%;
        }

        .post-card {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .hidden { display: none; }

        /* Fade in animation */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
    </style>
</head>
<body>
    <button id="loadBtn">Load Posts</button>

    <div id="skeleton-container"></div>
    <div id="posts-container"></div>

    <script>
        const loadBtn = document.getElementById('loadBtn');
        const skeletonContainer = document.getElementById('skeleton-container');
        const postsContainer = document.getElementById('posts-container');

        loadBtn.addEventListener('click', loadPosts);

        async function loadPosts() {
            // Show skeleton
            showSkeletons(3); // Show 3 skeleton cards
            loadBtn.disabled = true;
            postsContainer.innerHTML = '';

            try {
                const response = await fetch('/api/posts');
                const result = await response.json();

                // Small delay to show skeleton (remove in production)
                await new Promise(resolve => setTimeout(resolve, 500));

                // Hide skeleton
                hideSkeletons();

                // Show posts with fade-in
                displayPosts(result.data);

            } catch (error) {
                hideSkeletons();
                postsContainer.innerHTML = '<p class="error">Failed to load posts</p>';

            } finally {
                loadBtn.disabled = false;
            }
        }

        function showSkeletons(count) {
            const skeletons = Array(count).fill(0).map(() => `
                <div class="post-card skeleton-card">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            `).join('');

            skeletonContainer.innerHTML = skeletons;
        }

        function hideSkeletons() {
            skeletonContainer.innerHTML = '';
        }

        function displayPosts(posts) {
            postsContainer.className = 'fade-in';
            postsContainer.innerHTML = posts.map(post => `
                <article class="post-card">
                    <h2>${post.title}</h2>
                    <p class="author">By ${post.author.name}</p>
                    <p>${post.excerpt}</p>
                    <a href="#">Read more</a>
                </article>
            `).join('');
        }
    </script>
</body>
</html>
```

---

## Button Loading States

### Loading Button with Spinner

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Button Loading</title>
    <style>
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            background: #3498db;
            color: white;
            cursor: pointer;
            position: relative;
        }

        .btn:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }

        .btn.loading {
            padding-left: 40px;
        }

        .btn-spinner {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        .hidden { display: none; }
    </style>
</head>
<body>
    <button id="submitBtn" class="btn">
        <span class="btn-spinner hidden"></span>
        <span class="btn-text">Submit</span>
    </button>

    <script>
        const button = document.getElementById('submitBtn');
        const spinner = button.querySelector('.btn-spinner');
        const text = button.querySelector('.btn-text');

        button.addEventListener('click', handleSubmit);

        async function handleSubmit() {
            setButtonLoading(true);

            try {
                // Simulate API call
                await fetch('/api/submit', {
                    method: 'POST',
                    body: JSON.stringify({ data: 'test' })
                });

                text.textContent = 'Success!';
                setTimeout(() => {
                    text.textContent = 'Submit';
                }, 2000);

            } catch (error) {
                text.textContent = 'Failed';
                setTimeout(() => {
                    text.textContent = 'Submit';
                }, 2000);

            } finally {
                setButtonLoading(false);
            }
        }

        function setButtonLoading(loading) {
            if (loading) {
                button.disabled = true;
                button.classList.add('loading');
                spinner.classList.remove('hidden');
                text.textContent = 'Loading...';
            } else {
                button.disabled = false;
                button.classList.remove('loading');
                spinner.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
```

---

## Progress Indicators

### Determinate Progress Bar

When you know how much progress has been made:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Progress Bar</title>
    <style>
        .progress-container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
        }

        .progress-bar-bg {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2ecc71);
            border-radius: 15px;
            transition: width 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .progress-text {
            text-align: center;
            margin-top: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="progress-container">
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressBar" style="width: 0%;">
                <span id="progressPercent">0%</span>
            </div>
        </div>
        <div class="progress-text" id="progressText">Ready to upload</div>
    </div>

    <button id="uploadBtn">Upload Files</button>

    <script>
        const uploadBtn = document.getElementById('uploadBtn');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');
        const progressText = document.getElementById('progressText');

        uploadBtn.addEventListener('click', simulateUpload);

        async function simulateUpload() {
            uploadBtn.disabled = true;
            progressText.textContent = 'Uploading...';

            // Simulate upload progress
            for (let i = 0; i <= 100; i += 10) {
                await new Promise(resolve => setTimeout(resolve, 200));
                updateProgress(i);
            }

            progressText.textContent = 'Upload complete!';
            uploadBtn.disabled = false;

            // Reset after 2 seconds
            setTimeout(() => {
                updateProgress(0);
                progressText.textContent = 'Ready to upload';
            }, 2000);
        }

        function updateProgress(percent) {
            progressBar.style.width = percent + '%';
            progressPercent.textContent = percent + '%';
        }

        // Real file upload with progress
        async function uploadFileWithProgress(file) {
            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    updateProgress(percent);
                }
            });

            // Handle completion
            xhr.addEventListener('load', () => {
                if (xhr.status === 200) {
                    progressText.textContent = 'Upload complete!';
                } else {
                    progressText.textContent = 'Upload failed';
                }
            });

            // Send request
            xhr.open('POST', '/api/upload');
            xhr.send(formData);
        }
    </script>
</body>
</html>
```

### Indeterminate Progress Bar

When you don't know how long it will take:

```html
<style>
    .progress-indeterminate {
        width: 100%;
        height: 4px;
        background: #f0f0f0;
        position: relative;
        overflow: hidden;
    }

    .progress-indeterminate::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        width: 30%;
        background: #3498db;
        animation: indeterminate 1.5s infinite;
    }

    @keyframes indeterminate {
        0% { left: -30%; }
        100% { left: 100%; }
    }
</style>

<div class="progress-indeterminate"></div>
```

---

## Complete Example: Post Manager with Professional UX

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            margin-bottom: 30px;
            color: #333;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 500;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Button styles */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background: #2980b9;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn.loading {
            padding-left: 45px;
        }

        .btn-spinner {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Toast notification */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: #2ecc71;
            color: white;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 1000;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.error {
            background: #e74c3c;
        }

        /* Posts list */
        .posts-section {
            margin-top: 40px;
        }

        .posts-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Skeleton */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .post-skeleton {
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .skeleton-title {
            height: 20px;
            width: 70%;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .skeleton-text {
            height: 14px;
            width: 100%;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        .skeleton-text:last-child {
            width: 85%;
        }

        /* Post card */
        .post-card {
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .post-card:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .post-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }

        .post-content {
            color: #666;
            line-height: 1.6;
        }

        .post-meta {
            color: #999;
            font-size: 12px;
            margin-top: 10px;
        }

        /* Fade in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Post Manager</h1>

        <!-- Create Form -->
        <form id="postForm">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" required>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="btn-spinner hidden"></span>
                <span class="btn-text">Create Post</span>
            </button>
        </form>

        <!-- Posts Section -->
        <div class="posts-section">
            <div class="posts-header">
                <h2>Recent Posts</h2>
                <button class="btn btn-primary" id="refreshBtn">Refresh</button>
            </div>

            <div id="postsSkeletonContainer"></div>
            <div id="postsContainer"></div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Elements
        const postForm = document.getElementById('postForm');
        const submitBtn = document.getElementById('submitBtn');
        const refreshBtn = document.getElementById('refreshBtn');
        const skeletonContainer = document.getElementById('postsSkeletonContainer');
        const postsContainer = document.getElementById('postsContainer');
        const toast = document.getElementById('toast');

        // Load posts on page load
        document.addEventListener('DOMContentLoaded', loadPosts);

        // Form submission
        postForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                title: document.getElementById('title').value,
                content: document.getElementById('content').value
            };

            setButtonLoading(submitBtn, true);

            try {
                const response = await fetch('/api/posts.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Failed to create post');
                }

                showToast('Post created successfully!', 'success');
                postForm.reset();
                loadPosts(); // Reload posts

            } catch (error) {
                showToast(error.message, 'error');
            } finally {
                setButtonLoading(submitBtn, false);
            }
        });

        // Refresh button
        refreshBtn.addEventListener('click', loadPosts);

        // Load posts
        async function loadPosts() {
            showSkeletons(3);
            postsContainer.innerHTML = '';
            refreshBtn.disabled = true;

            try {
                const response = await fetch('/api/posts.php');
                const result = await response.json();

                if (!response.ok) {
                    throw new Error('Failed to load posts');
                }

                // Small delay to show skeleton
                await new Promise(resolve => setTimeout(resolve, 300));

                hideSkeletons();
                displayPosts(result.data);

            } catch (error) {
                hideSkeletons();
                postsContainer.innerHTML = `
                    <p style="color: #e74c3c;">Failed to load posts. Please try again.</p>
                `;
            } finally {
                refreshBtn.disabled = false;
            }
        }

        // Display posts
        function displayPosts(posts) {
            if (posts.length === 0) {
                postsContainer.innerHTML = '<p style="color: #999;">No posts yet</p>';
                return;
            }

            postsContainer.className = 'fade-in';
            postsContainer.innerHTML = posts.map(post => `
                <article class="post-card">
                    <h3 class="post-title">${escapeHtml(post.title)}</h3>
                    <p class="post-content">${escapeHtml(post.content)}</p>
                    <div class="post-meta">
                        Posted ${formatDate(post.created_at)}
                    </div>
                </article>
            `).join('');
        }

        // Show skeleton
        function showSkeletons(count) {
            const skeletons = Array(count).fill(0).map(() => `
                <div class="post-skeleton">
                    <div class="skeleton skeleton-title"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                    <div class="skeleton skeleton-text"></div>
                </div>
            `).join('');

            skeletonContainer.innerHTML = skeletons;
        }

        function hideSkeletons() {
            skeletonContainer.innerHTML = '';
        }

        // Button loading state
        function setButtonLoading(button, loading) {
            const spinner = button.querySelector('.btn-spinner');
            const text = button.querySelector('.btn-text');
            const originalText = text.textContent;

            if (loading) {
                button.disabled = true;
                button.classList.add('loading');
                spinner.classList.remove('hidden');
                text.textContent = 'Loading...';
                button.dataset.originalText = originalText;
            } else {
                button.disabled = false;
                button.classList.remove('loading');
                spinner.classList.add('hidden');
                text.textContent = button.dataset.originalText || originalText;
            }
        }

        // Toast notification
        function showToast(message, type = 'success') {
            toast.textContent = message;
            toast.className = `toast ${type}`;

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Hide after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Utilities
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);

            if (diffMins < 1) return 'just now';
            if (diffMins < 60) return `${diffMins} minutes ago`;

            const diffHours = Math.floor(diffMins / 60);
            if (diffHours < 24) return `${diffHours} hours ago`;

            const diffDays = Math.floor(diffHours / 24);
            if (diffDays < 7) return `${diffDays} days ago`;

            return date.toLocaleDateString();
        }
    </script>
</body>
</html>
```

---

## Best Practices for Loading States

### 1. Immediate Feedback

```javascript
button.addEventListener('click', async () => {
    // Show loading IMMEDIATELY - before async operation
    showLoading();

    try {
        await fetch('/api/data');
    } finally {
        hideLoading();
    }
});
```

### 2. Disable During Operations

```javascript
async function submitForm() {
    const submitBtn = document.getElementById('submit');

    // Prevent double submission
    submitBtn.disabled = true;

    try {
        await fetch('/api/submit', { method: 'POST' });
    } finally {
        // Always re-enable
        submitBtn.disabled = false;
    }
}
```

### 3. Use Skeletons for Content

```javascript
// Bad - empty space while loading
showSpinner();
await loadPosts();
hideSpinner();

// Good - skeleton that matches content
showPostsSkeleton();
await loadPosts();
hideSkeleton();
```

### 4. Smooth Transitions

```css
/* Add fade-in for content */
.content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
```

### 5. Timeout Feedback

```javascript
async function loadData() {
    const timeoutId = setTimeout(() => {
        showMessage('This is taking longer than usual...');
    }, 5000); // Show after 5 seconds

    try {
        await fetch('/api/data');
    } finally {
        clearTimeout(timeoutId);
    }
}
```

---

## Summary

**Key UX Patterns:**

1. **Loading Indicators** - Show spinners or text
2. **Skeleton Screens** - Better than spinners for content
3. **Button States** - Disable and show loading
4. **Progress Bars** - For known progress
5. **Toast Notifications** - Quick feedback
6. **Smooth Transitions** - Fade in content
7. **Timeout Warnings** - For slow operations

**Implementation Checklist:**
- [ ] Show loading immediately on user action
- [ ] Disable buttons during operations
- [ ] Use skeletons for content loads
- [ ] Add fade-in animations
- [ ] Always re-enable in `finally` blocks
- [ ] Provide meaningful progress feedback
- [ ] Handle edge cases (slow network, errors)

---

## Practice Exercises

**Exercise 1:** Create a search interface with skeleton results while loading

**Exercise 2:** Build a file uploader with progress bar showing percentage

**Exercise 3:** Implement a form with button loading states and success/error toasts

---

**Next up:** Lesson 06 - Combining Alpine.js with Fetch - The perfect duo!
