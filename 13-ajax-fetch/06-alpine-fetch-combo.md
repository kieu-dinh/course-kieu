# Lesson 06 - Combining Alpine.js with Fetch API

**Duration**: 60-70 minutes
**Prerequisites**: Module 12 - Alpine.js, Lesson 05 - Loading States

---

## Introduction

This is where everything comes together! Alpine.js gives you reactive data and easy DOM manipulation. Fetch API lets you talk to your server. Combined, they create powerful, dynamic interfaces with minimal code.

**What you'll learn:**
- Use Alpine.js with Fetch API
- Manage loading states reactively
- Build interactive components that fetch data
- Create real-world patterns (forms, lists, modals)
- Prepare for Laravel Livewire concepts

---

## Why Alpine.js + Fetch?

### Without Alpine (Vanilla JS)

```javascript
// Verbose, manual DOM manipulation
const button = document.getElementById('loadBtn');
const loading = document.getElementById('loading');
const content = document.getElementById('content');

button.addEventListener('click', async () => {
    loading.style.display = 'block';
    content.innerHTML = '';
    button.disabled = true;

    const response = await fetch('/api/posts');
    const data = await response.json();

    loading.style.display = 'none';
    button.disabled = false;

    data.forEach(post => {
        const div = document.createElement('div');
        div.textContent = post.title;
        content.appendChild(div);
    });
});
```

### With Alpine.js

```html
<div x-data="postsWidget()">
    <button @click="loadPosts" :disabled="loading">Load Posts</button>

    <div x-show="loading">Loading...</div>

    <template x-for="post in posts" :key="post.id">
        <div x-text="post.title"></div>
    </template>
</div>

<script>
function postsWidget() {
    return {
        posts: [],
        loading: false,

        async loadPosts() {
            this.loading = true;

            const response = await fetch('/api/posts');
            const data = await response.json();

            this.posts = data.data;
            this.loading = false;
        }
    }
}
</script>
```

**Much cleaner!** Alpine handles the DOM updates automatically.

---

## Basic Pattern: Loading Data

### Simple Data Fetcher

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alpine + Fetch</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .loading { color: gray; }
        .error { color: red; }
        .post { border: 1px solid #ddd; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div x-data="postsList()">
        <h1>Blog Posts</h1>

        <button
            @click="loadPosts"
            :disabled="loading"
            x-text="loading ? 'Loading...' : 'Load Posts'"
        ></button>

        <!-- Loading state -->
        <div x-show="loading" class="loading">
            Fetching posts...
        </div>

        <!-- Error state -->
        <div x-show="error" class="error" x-text="error"></div>

        <!-- Posts list -->
        <div x-show="posts.length > 0">
            <template x-for="post in posts" :key="post.id">
                <article class="post">
                    <h3 x-text="post.title"></h3>
                    <p x-text="post.excerpt"></p>
                    <small x-text="'By ' + post.author.name"></small>
                </article>
            </template>
        </div>

        <!-- Empty state -->
        <div x-show="!loading && posts.length === 0 && !error">
            No posts found.
        </div>
    </div>

    <script>
        function postsList() {
            return {
                posts: [],
                loading: false,
                error: null,

                async loadPosts() {
                    this.loading = true;
                    this.error = null;

                    try {
                        const response = await fetch('/api/posts.php');

                        if (!response.ok) {
                            throw new Error('Failed to load posts');
                        }

                        const result = await response.json();
                        this.posts = result.data;

                    } catch (err) {
                        this.error = err.message;
                        console.error('Error:', err);

                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
```

**Key concepts:**
- `x-data` creates component state
- `:disabled` binds button disabled state
- `x-show` conditionally shows elements
- `x-for` loops through posts
- State changes automatically update UI

---

## Pattern: Form Submission

### Create Post Form

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Post</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error { color: red; font-size: 14px; margin-top: 5px; }
        .success { color: green; padding: 15px; background: #d4edda; border-radius: 4px; }
        button { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
    </style>
</head>
<body>
    <div x-data="createPostForm()" class="container">
        <h1>Create New Post</h1>

        <form @submit.prevent="submitForm">
            <!-- Title -->
            <div class="form-group">
                <label for="title">Title</label>
                <input
                    type="text"
                    id="title"
                    x-model="form.title"
                    :disabled="submitting"
                    required
                >
                <div x-show="errors.title" class="error" x-text="errors.title"></div>
            </div>

            <!-- Content -->
            <div class="form-group">
                <label for="content">Content</label>
                <textarea
                    id="content"
                    x-model="form.content"
                    :disabled="submitting"
                    rows="5"
                    required
                ></textarea>
                <div x-show="errors.content" class="error" x-text="errors.content"></div>
            </div>

            <!-- Submit button -->
            <button
                type="submit"
                :disabled="submitting"
                x-text="submitting ? 'Creating...' : 'Create Post'"
            ></button>
        </form>

        <!-- Success message -->
        <div x-show="successMessage" class="success" x-text="successMessage"></div>

        <!-- General error -->
        <div x-show="generalError" class="error" x-text="generalError"></div>
    </div>

    <script>
        function createPostForm() {
            return {
                form: {
                    title: '',
                    content: ''
                },
                errors: {},
                submitting: false,
                successMessage: '',
                generalError: '',

                async submitForm() {
                    // Reset messages
                    this.errors = {};
                    this.successMessage = '';
                    this.generalError = '';
                    this.submitting = true;

                    try {
                        const response = await fetch('/api/posts.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        const result = await response.json();

                        // Handle validation errors
                        if (response.status === 422) {
                            this.errors = result.errors;
                            return;
                        }

                        // Handle other errors
                        if (!response.ok) {
                            throw new Error(result.error || 'Failed to create post');
                        }

                        // Success!
                        this.successMessage = 'Post created successfully!';
                        this.resetForm();

                        // Hide success message after 3 seconds
                        setTimeout(() => {
                            this.successMessage = '';
                        }, 3000);

                    } catch (error) {
                        this.generalError = error.message;
                        console.error('Error:', error);

                    } finally {
                        this.submitting = false;
                    }
                },

                resetForm() {
                    this.form = {
                        title: '',
                        content: ''
                    };
                }
            }
        }
    </script>
</body>
</html>
```

---

## Pattern: CRUD Operations

### Complete Post Manager

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Manager</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .post { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .post-actions button { margin-right: 10px; padding: 5px 10px; }
        .btn-delete { background: #e74c3c; color: white; border: none; cursor: pointer; }
        .btn-edit { background: #f39c12; color: white; border: none; cursor: pointer; }
        .loading { color: gray; font-style: italic; }
        input, textarea { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .edit-form { background: #f9f9f9; padding: 15px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container" x-data="postManager()">
        <h1>Post Manager</h1>

        <!-- Loading state -->
        <div x-show="loading" class="loading">Loading posts...</div>

        <!-- Posts list -->
        <div x-show="!loading">
            <template x-for="post in posts" :key="post.id">
                <div class="post">
                    <!-- View mode -->
                    <div x-show="editingId !== post.id">
                        <h3 x-text="post.title"></h3>
                        <p x-text="post.content"></p>

                        <div class="post-actions">
                            <button
                                class="btn-edit"
                                @click="startEdit(post)"
                            >Edit</button>

                            <button
                                class="btn-delete"
                                @click="deletePost(post.id)"
                                :disabled="deleting === post.id"
                                x-text="deleting === post.id ? 'Deleting...' : 'Delete'"
                            ></button>
                        </div>
                    </div>

                    <!-- Edit mode -->
                    <div x-show="editingId === post.id" class="edit-form">
                        <input
                            type="text"
                            x-model="editForm.title"
                            style="width: 100%; margin-bottom: 10px;"
                        >
                        <textarea
                            x-model="editForm.content"
                            style="width: 100%; margin-bottom: 10px;"
                            rows="3"
                        ></textarea>

                        <button
                            @click="saveEdit(post.id)"
                            :disabled="updating"
                            x-text="updating ? 'Saving...' : 'Save'"
                        ></button>

                        <button @click="cancelEdit">Cancel</button>
                    </div>
                </div>
            </template>

            <div x-show="posts.length === 0">
                No posts yet.
            </div>
        </div>
    </div>

    <script>
        function postManager() {
            return {
                posts: [],
                loading: false,
                deleting: null,
                updating: false,
                editingId: null,
                editForm: {},

                // Load posts on init
                init() {
                    this.loadPosts();
                },

                async loadPosts() {
                    this.loading = true;

                    try {
                        const response = await fetch('/api/posts.php');
                        const result = await response.json();

                        if (result.success) {
                            this.posts = result.data;
                        }

                    } catch (error) {
                        alert('Failed to load posts');
                        console.error(error);

                    } finally {
                        this.loading = false;
                    }
                },

                async deletePost(postId) {
                    if (!confirm('Delete this post?')) {
                        return;
                    }

                    this.deleting = postId;

                    try {
                        const response = await fetch(`/api/posts.php?id=${postId}`, {
                            method: 'DELETE'
                        });

                        if (response.ok) {
                            // Remove from array
                            this.posts = this.posts.filter(p => p.id !== postId);
                        } else {
                            throw new Error('Failed to delete');
                        }

                    } catch (error) {
                        alert('Failed to delete post');
                        console.error(error);

                    } finally {
                        this.deleting = null;
                    }
                },

                startEdit(post) {
                    this.editingId = post.id;
                    this.editForm = {
                        title: post.title,
                        content: post.content
                    };
                },

                cancelEdit() {
                    this.editingId = null;
                    this.editForm = {};
                },

                async saveEdit(postId) {
                    this.updating = true;

                    try {
                        const response = await fetch(`/api/posts.php?id=${postId}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.editForm)
                        });

                        if (!response.ok) {
                            throw new Error('Failed to update');
                        }

                        const result = await response.json();

                        // Update in array
                        const index = this.posts.findIndex(p => p.id === postId);
                        if (index !== -1) {
                            this.posts[index] = {
                                ...this.posts[index],
                                ...this.editForm
                            };
                        }

                        this.cancelEdit();

                    } catch (error) {
                        alert('Failed to update post');
                        console.error(error);

                    } finally {
                        this.updating = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
```

---

## Pattern: Real-Time Search

### Live Search Component

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Search</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .search-box { margin-bottom: 20px; }
        .search-box input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
        }
        .search-info { color: #666; margin-bottom: 10px; }
        .result { padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; }
        .highlight { background: yellow; }
        .no-results { color: #999; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <div class="container" x-data="liveSearch()">
        <h1>Live Search</h1>

        <!-- Search box -->
        <div class="search-box">
            <input
                type="text"
                x-model="query"
                @input.debounce.300ms="search"
                placeholder="Search posts..."
                :disabled="searching"
            >
        </div>

        <!-- Search info -->
        <div class="search-info" x-show="query">
            <span x-show="searching">Searching...</span>
            <span x-show="!searching && results.length > 0" x-text="`Found ${results.length} results`"></span>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0">
            <template x-for="result in results" :key="result.id">
                <div class="result">
                    <h3 x-html="highlightMatch(result.title)"></h3>
                    <p x-html="highlightMatch(result.excerpt)"></p>
                </div>
            </template>
        </div>

        <!-- No results -->
        <div x-show="!searching && query && results.length === 0" class="no-results">
            No results found for "<span x-text="query"></span>"
        </div>
    </div>

    <script>
        function liveSearch() {
            return {
                query: '',
                results: [],
                searching: false,

                async search() {
                    // Don't search if query is empty
                    if (!this.query.trim()) {
                        this.results = [];
                        return;
                    }

                    this.searching = true;

                    try {
                        const params = new URLSearchParams({ q: this.query });
                        const response = await fetch(`/api/search.php?${params}`);
                        const result = await response.json();

                        if (result.success) {
                            this.results = result.data;
                        }

                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];

                    } finally {
                        this.searching = false;
                    }
                },

                highlightMatch(text) {
                    if (!this.query) return text;

                    const regex = new RegExp(`(${this.query})`, 'gi');
                    return text.replace(regex, '<span class="highlight">$1</span>');
                }
            }
        }
    </script>
</body>
</html>
```

**Key feature:** `@input.debounce.300ms` - waits 300ms after user stops typing before searching!

---

## Pattern: Infinite Scroll

### Load More on Scroll

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Infinite Scroll</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .post { border: 1px solid #ddd; padding: 20px; margin: 10px 0; }
        .loading { text-align: center; padding: 20px; color: gray; }
        .end-message { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>
    <div x-data="infiniteScroll()" @scroll.window="handleScroll">
        <h1>Infinite Scroll Posts</h1>

        <!-- Posts -->
        <template x-for="post in posts" :key="post.id">
            <div class="post">
                <h3 x-text="post.title"></h3>
                <p x-text="post.content"></p>
            </div>
        </template>

        <!-- Loading indicator -->
        <div x-show="loading" class="loading">
            Loading more posts...
        </div>

        <!-- End message -->
        <div x-show="reachedEnd" class="end-message">
            No more posts to load
        </div>
    </div>

    <script>
        function infiniteScroll() {
            return {
                posts: [],
                loading: false,
                page: 1,
                reachedEnd: false,

                init() {
                    this.loadPosts();
                },

                async loadPosts() {
                    if (this.loading || this.reachedEnd) return;

                    this.loading = true;

                    try {
                        const response = await fetch(`/api/posts.php?page=${this.page}&per_page=10`);
                        const result = await response.json();

                        if (result.data.length === 0) {
                            this.reachedEnd = true;
                        } else {
                            this.posts = [...this.posts, ...result.data];
                            this.page++;
                        }

                    } catch (error) {
                        console.error('Load error:', error);

                    } finally {
                        this.loading = false;
                    }
                },

                handleScroll() {
                    // Calculate if near bottom
                    const scrollPosition = window.scrollY + window.innerHeight;
                    const pageHeight = document.documentElement.scrollHeight;

                    // Load more if within 200px of bottom
                    if (scrollPosition > pageHeight - 200) {
                        this.loadPosts();
                    }
                }
            }
        }
    </script>
</body>
</html>
```

---

## Pattern: Modal with Dynamic Content

### Post Details Modal

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modal Example</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .post { border: 1px solid #ddd; padding: 15px; margin: 10px 0; cursor: pointer; }
        .post:hover { background: #f5f5f5; }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
            border: none;
            background: none;
        }
    </style>
</head>
<body>
    <div x-data="postsWithModal()">
        <h1>Click a post to view details</h1>

        <!-- Posts list -->
        <template x-for="post in posts" :key="post.id">
            <div class="post" @click="viewPost(post.id)">
                <h3 x-text="post.title"></h3>
                <p x-text="post.excerpt"></p>
            </div>
        </template>

        <!-- Modal -->
        <div x-show="modalOpen" class="modal" @click.self="closeModal">
            <div class="modal-content">
                <!-- Loading state -->
                <div x-show="loadingPost">
                    Loading post...
                </div>

                <!-- Post content -->
                <div x-show="!loadingPost && currentPost">
                    <div class="modal-header">
                        <h2 x-text="currentPost?.title"></h2>
                        <button class="close-btn" @click="closeModal">&times;</button>
                    </div>

                    <div x-text="currentPost?.content"></div>

                    <div style="margin-top: 20px; color: #666;">
                        <small>By <span x-text="currentPost?.author?.name"></span></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function postsWithModal() {
            return {
                posts: [],
                modalOpen: false,
                currentPost: null,
                loadingPost: false,

                init() {
                    this.loadPosts();
                },

                async loadPosts() {
                    try {
                        const response = await fetch('/api/posts.php');
                        const result = await response.json();
                        this.posts = result.data;
                    } catch (error) {
                        console.error('Error:', error);
                    }
                },

                async viewPost(postId) {
                    this.modalOpen = true;
                    this.loadingPost = true;
                    this.currentPost = null;

                    try {
                        const response = await fetch(`/api/posts.php?id=${postId}`);
                        const result = await response.json();

                        if (result.success) {
                            this.currentPost = result.data;
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        alert('Failed to load post');
                        this.closeModal();

                    } finally {
                        this.loadingPost = false;
                    }
                },

                closeModal() {
                    this.modalOpen = false;
                    this.currentPost = null;
                }
            }
        }
    </script>
</body>
</html>
```

---

## Alpine.js Magic Properties

Useful for API calls:

### $watch - React to Changes

```javascript
x-data="{
    postId: 1,
    post: null,

    init() {
        // Watch postId and reload when it changes
        this.$watch('postId', async (newId) => {
            const response = await fetch(`/api/posts/${newId}`);
            this.post = await response.json();
        });
    }
}"
```

### $dispatch - Communication Between Components

```javascript
// Component 1: Triggers event
x-data="{
    async createPost(data) {
        await fetch('/api/posts', { method: 'POST', body: JSON.stringify(data) });
        this.$dispatch('post-created');
    }
}"

// Component 2: Listens for event
x-data="{
    posts: [],

    init() {
        this.$el.addEventListener('post-created', () => {
            this.loadPosts(); // Refresh list
        });
    }
}"
```

---

## Best Practices

### 1. Use `init()` for Initial Loading

```javascript
x-data="{
    posts: [],

    init() {
        this.loadPosts(); // Load on component mount
    },

    async loadPosts() {
        // ...
    }
}"
```

### 2. Debounce User Input

```html
<!-- Wait 300ms after user stops typing -->
<input x-model="query" @input.debounce.300ms="search">
```

### 3. Handle Errors Gracefully

```javascript
async loadData() {
    this.loading = true;
    this.error = null;

    try {
        const response = await fetch('/api/data');
        if (!response.ok) throw new Error('Failed');
        this.data = await response.json();
    } catch (error) {
        this.error = error.message;
    } finally {
        this.loading = false;
    }
}
```

### 4. Prevent Double Submissions

```html
<button @click="submit" :disabled="submitting">
    <span x-text="submitting ? 'Saving...' : 'Save'"></span>
</button>
```

---

## Connection to Laravel Livewire

This Alpine + Fetch pattern is **exactly** how Livewire works under the hood!

**What you're doing now:**
```html
<div x-data="{ count: 0 }">
    <button @click="count++">Increment</button>
    <span x-text="count"></span>
</div>
```

**What Livewire does (Module 17):**
```html
<div>
    <button wire:click="increment">Increment</button>
    <span>{{ $count }}</span>
</div>
```

Livewire automatically:
- Syncs state to server
- Re-renders component
- Uses Alpine.js for interactions

**You're learning the foundation!**

---

## Summary

**Key Patterns:**
1. **Data Loading** - Fetch and display with loading states
2. **Form Submission** - POST data with validation
3. **CRUD Operations** - Create, Read, Update, Delete
4. **Live Search** - Real-time search with debouncing
5. **Infinite Scroll** - Load more on scroll
6. **Modals** - Dynamic content in modals

**Alpine.js Benefits:**
- Reactive data binding
- Automatic DOM updates
- Clean, declarative syntax
- Minimal boilerplate

**Best Practices:**
- Use `init()` for initial data loading
- Debounce user input
- Handle loading/error states
- Disable buttons during operations
- Use `:key` in `x-for` loops

---

## Practice Exercises

**Exercise 1:** Build a todo app with Alpine + Fetch (create, toggle, delete)

**Exercise 2:** Create a real-time comment system with live updates

**Exercise 3:** Implement a product filter with multiple criteria (category, price, search)

---

**Next up:** Lesson 07 - Live Search and Real-Time Features - Build production-ready search!
