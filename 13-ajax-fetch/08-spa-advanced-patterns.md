# Lesson 08 - SPA Concepts and Advanced Patterns

**Duration**: 70-80 minutes
**Prerequisites**: All previous lessons in Module 13

---

## Introduction

Single Page Applications (SPAs) are the future of web development. Instead of loading new pages from the server, SPAs dynamically update the current page with JavaScript. This creates a smooth, app-like experience.

**What you'll learn:**
- Understand SPA architecture
- Implement client-side routing
- Manage application state
- Build page transitions
- Handle browser history
- Create reusable patterns
- Understand the path to React/Vue/Laravel

---

## What is a Single Page Application?

### Traditional Multi-Page App

```
Page 1 (index.html)
    ↓ Click link
Full page reload → Server sends Page 2 (about.html)
    ↓ Click link
Full page reload → Server sends Page 3 (contact.html)
```

**Every navigation = full page reload**

### Single Page Application

```
Initial Load: index.html + JavaScript
    ↓ Click link
JavaScript changes content (no reload)
    ↓ Click link
JavaScript changes content (no reload)
    ↓ Click link
JavaScript changes content (no reload)
```

**Navigation = JavaScript updates DOM**

### Benefits of SPAs

1. **Faster navigation** - No full page reloads
2. **Smooth transitions** - Animated page changes
3. **Better UX** - Feels like a native app
4. **Less server load** - Only data transferred
5. **Offline capabilities** - Can work without internet

### Challenges of SPAs

1. **Initial load slower** - Must download JavaScript first
2. **SEO harder** - Search engines prefer server-rendered HTML
3. **More complex** - Need routing, state management
4. **Browser history** - Must manage manually

---

## Simple Client-Side Router

### Basic Router Implementation

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple SPA Router</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        nav {
            background: #2c3e50;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            padding: 8px 15px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        nav a:hover,
        nav a.active {
            background: #34495e;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .page-title {
            font-size: 32px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .page-content {
            color: #666;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div x-data="spa()">
        <!-- Navigation -->
        <nav>
            <a
                href="#/"
                @click.prevent="navigate('/')"
                :class="{ 'active': currentRoute === '/' }"
            >Home</a>

            <a
                href="#/about"
                @click.prevent="navigate('/about')"
                :class="{ 'active': currentRoute === '/about' }"
            >About</a>

            <a
                href="#/posts"
                @click.prevent="navigate('/posts')"
                :class="{ 'active': currentRoute === '/posts' }"
            >Posts</a>

            <a
                href="#/contact"
                @click.prevent="navigate('/contact')"
                :class="{ 'active': currentRoute === '/contact' }"
            >Contact</a>
        </nav>

        <!-- Page Container -->
        <div class="container">
            <!-- Home Page -->
            <div x-show="currentRoute === '/'" class="page">
                <h1 class="page-title">Welcome Home</h1>
                <div class="page-content">
                    <p>This is a simple Single Page Application with client-side routing.</p>
                    <p>Click the navigation links above - notice no page reload!</p>
                </div>
            </div>

            <!-- About Page -->
            <div x-show="currentRoute === '/about'" class="page">
                <h1 class="page-title">About Us</h1>
                <div class="page-content">
                    <p>This page was loaded without a server request.</p>
                    <p>The content changed instantly using JavaScript.</p>
                </div>
            </div>

            <!-- Posts Page -->
            <div x-show="currentRoute === '/posts'" class="page">
                <h1 class="page-title">Blog Posts</h1>
                <div class="page-content">
                    <div x-show="loading">Loading posts...</div>

                    <template x-if="!loading && posts.length > 0">
                        <div>
                            <template x-for="post in posts" :key="post.id">
                                <div style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                    <h3 x-text="post.title"></h3>
                                    <p x-text="post.excerpt"></p>
                                    <a
                                        href="#"
                                        @click.prevent="navigate(`/posts/${post.id}`)"
                                        style="color: #3498db;"
                                    >Read more →</a>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Single Post Page -->
            <div x-show="currentRoute.startsWith('/posts/') && currentRoute !== '/posts'" class="page">
                <div x-show="loading">Loading post...</div>

                <template x-if="!loading && currentPost">
                    <div>
                        <h1 class="page-title" x-text="currentPost.title"></h1>
                        <p style="color: #999; margin-bottom: 20px;">
                            By <span x-text="currentPost.author"></span> •
                            <span x-text="currentPost.date"></span>
                        </p>
                        <div class="page-content" x-text="currentPost.content"></div>

                        <button
                            @click="navigate('/posts')"
                            style="margin-top: 30px; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;"
                        >← Back to Posts</button>
                    </div>
                </template>
            </div>

            <!-- Contact Page -->
            <div x-show="currentRoute === '/contact'" class="page">
                <h1 class="page-title">Contact Us</h1>
                <div class="page-content">
                    <form @submit.prevent="submitContact">
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Name</label>
                            <input type="text" x-model="contactForm.name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Email</label>
                            <input type="email" x-model="contactForm.email" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>

                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px;">Message</label>
                            <textarea x-model="contactForm.message" required rows="5" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"></textarea>
                        </div>

                        <button
                            type="submit"
                            :disabled="submitting"
                            style="padding: 10px 20px; background: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer;"
                            x-text="submitting ? 'Sending...' : 'Send Message'"
                        ></button>

                        <div x-show="contactSuccess" style="margin-top: 15px; padding: 15px; background: #d4edda; color: #155724; border-radius: 4px;">
                            Message sent successfully!
                        </div>
                    </form>
                </div>
            </div>

            <!-- 404 Page -->
            <div x-show="currentRoute === '/404'" class="page">
                <h1 class="page-title">404 - Page Not Found</h1>
                <div class="page-content">
                    <p>The page you're looking for doesn't exist.</p>
                    <button
                        @click="navigate('/')"
                        style="margin-top: 20px; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;"
                    >Go Home</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function spa() {
            return {
                currentRoute: '/',
                posts: [],
                currentPost: null,
                loading: false,
                contactForm: {
                    name: '',
                    email: '',
                    message: ''
                },
                submitting: false,
                contactSuccess: false,

                init() {
                    // Handle browser back/forward
                    window.addEventListener('popstate', () => {
                        this.handleRouteChange();
                    });

                    // Handle initial route
                    this.handleRouteChange();
                },

                navigate(route) {
                    // Update URL without reload
                    window.history.pushState({}, '', '#' + route);

                    // Handle route change
                    this.handleRouteChange();
                },

                handleRouteChange() {
                    // Get route from URL hash
                    const hash = window.location.hash.slice(1) || '/';
                    this.currentRoute = hash;

                    // Load data based on route
                    if (hash === '/posts') {
                        this.loadPosts();
                    } else if (hash.startsWith('/posts/')) {
                        const postId = hash.split('/')[2];
                        this.loadPost(postId);
                    }

                    // Scroll to top
                    window.scrollTo(0, 0);
                },

                async loadPosts() {
                    this.loading = true;

                    try {
                        const response = await fetch('/api/posts.php');
                        const data = await response.json();

                        this.posts = data.data || [];

                    } catch (error) {
                        console.error('Error loading posts:', error);
                        this.posts = [];

                    } finally {
                        this.loading = false;
                    }
                },

                async loadPost(postId) {
                    this.loading = true;
                    this.currentPost = null;

                    try {
                        const response = await fetch(`/api/posts.php?id=${postId}`);
                        const data = await response.json();

                        if (data.success) {
                            this.currentPost = data.data;
                        } else {
                            this.navigate('/404');
                        }

                    } catch (error) {
                        console.error('Error loading post:', error);
                        this.navigate('/404');

                    } finally {
                        this.loading = false;
                    }
                },

                async submitContact() {
                    this.submitting = true;
                    this.contactSuccess = false;

                    try {
                        const response = await fetch('/api/contact.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.contactForm)
                        });

                        if (response.ok) {
                            this.contactSuccess = true;
                            this.contactForm = { name: '', email: '', message: '' };

                            setTimeout(() => {
                                this.contactSuccess = false;
                            }, 3000);
                        }

                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('Failed to send message');

                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
```

---

## Advanced Router with Parameters

### Router with Dynamic Routes

```javascript
class Router {
    constructor() {
        this.routes = [];
        this.currentRoute = null;
        this.params = {};
    }

    // Add route
    addRoute(path, handler) {
        this.routes.push({ path, handler });
    }

    // Navigate to route
    navigate(path) {
        window.history.pushState({}, '', path);
        this.handleRoute();
    }

    // Handle route change
    handleRoute() {
        const path = window.location.pathname;

        // Find matching route
        for (const route of this.routes) {
            const match = this.matchRoute(route.path, path);

            if (match) {
                this.currentRoute = route;
                this.params = match.params;
                route.handler(match.params);
                return;
            }
        }

        // No match - 404
        this.handle404();
    }

    // Match route with parameters
    matchRoute(pattern, path) {
        // Convert pattern to regex
        // /posts/:id → /posts/([^/]+)
        const paramNames = [];
        const regexPattern = pattern.replace(/:([^/]+)/g, (match, paramName) => {
            paramNames.push(paramName);
            return '([^/]+)';
        });

        const regex = new RegExp(`^${regexPattern}$`);
        const match = path.match(regex);

        if (!match) return null;

        // Extract parameters
        const params = {};
        paramNames.forEach((name, index) => {
            params[name] = match[index + 1];
        });

        return { params };
    }

    handle404() {
        console.log('404 - Page not found');
    }

    // Start router
    start() {
        window.addEventListener('popstate', () => this.handleRoute());
        this.handleRoute();
    }
}

// Usage
const router = new Router();

router.addRoute('/', () => {
    console.log('Home page');
});

router.addRoute('/posts', () => {
    console.log('Posts list');
});

router.addRoute('/posts/:id', (params) => {
    console.log('Post ID:', params.id);
});

router.addRoute('/users/:userId/posts/:postId', (params) => {
    console.log('User:', params.userId, 'Post:', params.postId);
});

router.start();
```

---

## State Management Pattern

### Centralized App State

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>State Management</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body>
    <div x-data="app">
        <!-- User info shown everywhere -->
        <header>
            <div x-show="$store.auth.isLoggedIn">
                Welcome, <span x-text="$store.auth.user.name"></span>!
                <button @click="$store.auth.logout()">Logout</button>
            </div>

            <div x-show="!$store.auth.isLoggedIn">
                <button @click="$store.auth.login()">Login</button>
            </div>

            <div>
                Cart: <span x-text="$store.cart.itemCount"></span> items
            </div>
        </header>

        <!-- Product list -->
        <div>
            <template x-for="product in $store.products.items" :key="product.id">
                <div>
                    <h3 x-text="product.name"></h3>
                    <button @click="$store.cart.addItem(product)">
                        Add to Cart
                    </button>
                </div>
            </template>
        </div>
    </div>

    <script>
        // Global stores
        document.addEventListener('alpine:init', () => {
            // Auth store
            Alpine.store('auth', {
                user: null,
                token: localStorage.getItem('token'),

                get isLoggedIn() {
                    return !!this.token;
                },

                async login(credentials) {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(credentials)
                    });

                    const data = await response.json();

                    this.user = data.user;
                    this.token = data.token;
                    localStorage.setItem('token', data.token);
                },

                logout() {
                    this.user = null;
                    this.token = null;
                    localStorage.removeItem('token');
                }
            });

            // Cart store
            Alpine.store('cart', {
                items: JSON.parse(localStorage.getItem('cart') || '[]'),

                get itemCount() {
                    return this.items.reduce((total, item) => total + item.quantity, 0);
                },

                get total() {
                    return this.items.reduce((total, item) => {
                        return total + (item.price * item.quantity);
                    }, 0);
                },

                addItem(product) {
                    const existing = this.items.find(item => item.id === product.id);

                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.items.push({
                            ...product,
                            quantity: 1
                        });
                    }

                    this.save();
                },

                removeItem(productId) {
                    this.items = this.items.filter(item => item.id !== productId);
                    this.save();
                },

                save() {
                    localStorage.setItem('cart', JSON.stringify(this.items));
                }
            });

            // Products store
            Alpine.store('products', {
                items: [],
                loading: false,

                async load() {
                    this.loading = true;

                    try {
                        const response = await fetch('/api/products');
                        const data = await response.json();
                        this.items = data.products;
                    } finally {
                        this.loading = false;
                    }
                }
            });
        });

        function app() {
            return {
                init() {
                    this.$store.products.load();
                }
            }
        }
    </script>
</body>
</html>
```

---

## Page Transitions

### Smooth Page Changes

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Page Transitions</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Fade transition */
        .page-fade-enter {
            opacity: 0;
        }

        .page-fade-enter-active {
            transition: opacity 0.3s ease;
        }

        .page-fade-enter-to {
            opacity: 1;
        }

        /* Slide transition */
        .page-slide-enter {
            transform: translateX(100%);
        }

        .page-slide-enter-active {
            transition: transform 0.4s ease;
        }

        .page-slide-enter-to {
            transform: translateX(0);
        }

        /* Zoom transition */
        .page-zoom-enter {
            opacity: 0;
            transform: scale(0.9);
        }

        .page-zoom-enter-active {
            transition: all 0.3s ease;
        }

        .page-zoom-enter-to {
            opacity: 1;
            transform: scale(1);
        }
    </style>
</head>
<body>
    <div x-data="{ page: 'home' }">
        <nav>
            <button @click="page = 'home'">Home</button>
            <button @click="page = 'about'">About</button>
            <button @click="page = 'contact'">Contact</button>
        </nav>

        <!-- Pages with transitions -->
        <div
            x-show="page === 'home'"
            x-transition:enter="page-fade-enter"
            x-transition:enter-start="page-fade-enter"
            x-transition:enter-end="page-fade-enter-to"
        >
            <h1>Home Page</h1>
        </div>

        <div
            x-show="page === 'about'"
            x-transition:enter="page-slide-enter"
            x-transition:enter-start="page-slide-enter"
            x-transition:enter-end="page-slide-enter-to"
        >
            <h1>About Page</h1>
        </div>

        <div
            x-show="page === 'contact'"
            x-transition:enter="page-zoom-enter"
            x-transition:enter-start="page-zoom-enter"
            x-transition:enter-end="page-zoom-enter-to"
        >
            <h1>Contact Page</h1>
        </div>
    </div>
</body>
</html>
```

---

## Complete SPA Example: Blog

This brings everything together:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blog SPA</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        /* Header */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        header h1 { font-size: 28px; margin-bottom: 15px; }

        nav {
            display: flex;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.3s;
        }

        nav a:hover,
        nav a.active {
            background: rgba(255,255,255,0.2);
        }

        /* Main content */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Post grid */
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .post-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .post-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .post-card-content {
            padding: 20px;
        }

        .post-card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .post-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .post-meta {
            color: #999;
            font-size: 14px;
        }

        /* Single post */
        .single-post {
            max-width: 800px;
            margin: 0 auto;
        }

        .single-post h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #333;
        }

        .single-post img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .single-post-content {
            color: #444;
            line-height: 1.8;
            font-size: 18px;
        }

        /* Loading */
        .loading {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div x-data="blogApp()">
        <!-- Header -->
        <header>
            <h1>My Blog</h1>
            <nav>
                <a
                    href="#/"
                    @click.prevent="navigate('/')"
                    :class="{ 'active': currentPage === 'home' }"
                >Home</a>

                <a
                    href="#/about"
                    @click.prevent="navigate('/about')"
                    :class="{ 'active': currentPage === 'about' }"
                >About</a>

                <a
                    href="#/contact"
                    @click.prevent="navigate('/contact')"
                    :class="{ 'active': currentPage === 'contact' }"
                >Contact</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main>
            <!-- Home: Posts List -->
            <div x-show="currentPage === 'home'" class="fade-in">
                <h2 style="margin-bottom: 30px; font-size: 32px;">Latest Posts</h2>

                <div x-show="loading" class="loading">
                    <div class="spinner"></div>
                    <p>Loading posts...</p>
                </div>

                <div x-show="!loading" class="posts-grid">
                    <template x-for="post in posts" :key="post.id">
                        <div class="post-card" @click="navigate(`/posts/${post.id}`)">
                            <img :src="post.image" :alt="post.title">
                            <div class="post-card-content">
                                <h3 x-text="post.title"></h3>
                                <p x-text="post.excerpt"></p>
                                <div class="post-meta">
                                    <span x-text="post.author"></span> •
                                    <span x-text="post.date"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Single Post -->
            <div x-show="currentPage === 'post'" class="single-post fade-in">
                <div x-show="loading" class="loading">
                    <div class="spinner"></div>
                    <p>Loading post...</p>
                </div>

                <template x-if="!loading && currentPost">
                    <article>
                        <h1 x-text="currentPost.title"></h1>
                        <div class="post-meta" style="margin-bottom: 30px; font-size: 16px;">
                            By <span x-text="currentPost.author"></span> •
                            <span x-text="currentPost.date"></span>
                        </div>
                        <img :src="currentPost.image" :alt="currentPost.title">
                        <div class="single-post-content" x-html="currentPost.content"></div>

                        <button
                            @click="navigate('/')"
                            style="margin-top: 40px; padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;"
                        >← Back to Home</button>
                    </article>
                </template>
            </div>

            <!-- About Page -->
            <div x-show="currentPage === 'about'" class="fade-in">
                <h2 style="margin-bottom: 20px; font-size: 32px;">About This Blog</h2>
                <p style="color: #666; line-height: 1.8; font-size: 18px;">
                    This is a Single Page Application built with Alpine.js and Fetch API.
                    It demonstrates modern web development patterns including client-side routing,
                    state management, and smooth page transitions.
                </p>
            </div>

            <!-- Contact Page -->
            <div x-show="currentPage === 'contact'" class="fade-in">
                <h2 style="margin-bottom: 20px; font-size: 32px;">Contact Us</h2>
                <p style="color: #666; margin-bottom: 30px;">Send us a message!</p>

                <form @submit.prevent="submitContact" style="max-width: 600px;">
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Name</label>
                        <input
                            type="text"
                            x-model="contactForm.name"
                            required
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                        >
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                        <input
                            type="email"
                            x-model="contactForm.email"
                            required
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                        >
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 500;">Message</label>
                        <textarea
                            x-model="contactForm.message"
                            required
                            rows="6"
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; resize: vertical;"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        :disabled="submitting"
                        style="padding: 12px 30px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;"
                        x-text="submitting ? 'Sending...' : 'Send Message'"
                    ></button>

                    <div
                        x-show="contactSuccess"
                        style="margin-top: 20px; padding: 15px; background: #d4edda; color: #155724; border-radius: 4px;"
                    >Message sent successfully!</div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function blogApp() {
            return {
                currentPage: 'home',
                currentRoute: '/',
                posts: [],
                currentPost: null,
                loading: false,
                contactForm: {
                    name: '',
                    email: '',
                    message: ''
                },
                submitting: false,
                contactSuccess: false,

                init() {
                    // Handle browser back/forward
                    window.addEventListener('popstate', () => {
                        this.handleRoute();
                    });

                    // Initial route
                    this.handleRoute();
                },

                navigate(route) {
                    window.history.pushState({}, '', '#' + route);
                    this.handleRoute();
                },

                handleRoute() {
                    const hash = window.location.hash.slice(1) || '/';
                    this.currentRoute = hash;

                    // Determine page
                    if (hash === '/') {
                        this.currentPage = 'home';
                        this.loadPosts();
                    } else if (hash.startsWith('/posts/')) {
                        this.currentPage = 'post';
                        const postId = hash.split('/')[2];
                        this.loadPost(postId);
                    } else if (hash === '/about') {
                        this.currentPage = 'about';
                    } else if (hash === '/contact') {
                        this.currentPage = 'contact';
                    } else {
                        this.currentPage = '404';
                    }

                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                async loadPosts() {
                    if (this.posts.length > 0) return; // Already loaded

                    this.loading = true;

                    try {
                        const response = await fetch('/api/posts.php');
                        const data = await response.json();
                        this.posts = data.posts || [];
                    } catch (error) {
                        console.error('Error loading posts:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async loadPost(postId) {
                    this.loading = true;
                    this.currentPost = null;

                    try {
                        const response = await fetch(`/api/posts.php?id=${postId}`);
                        const data = await response.json();

                        if (data.success) {
                            this.currentPost = data.post;
                        }
                    } catch (error) {
                        console.error('Error loading post:', error);
                    } finally {
                        this.loading = false;
                    }
                },

                async submitContact() {
                    this.submitting = true;
                    this.contactSuccess = false;

                    try {
                        await fetch('/api/contact.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.contactForm)
                        });

                        this.contactSuccess = true;
                        this.contactForm = { name: '', email: '', message: '' };

                        setTimeout(() => {
                            this.contactSuccess = false;
                        }, 3000);

                    } catch (error) {
                        alert('Failed to send message');
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
```

---

## The Road to Modern Frameworks

### What You've Learned

**SPA Concepts:**
- Client-side routing
- State management
- Page transitions
- Data fetching
- Form handling

**These are the building blocks of:**
- React
- Vue.js
- Angular
- Laravel Livewire

### How This Connects to Laravel (Module 17)

**What you built (Alpine + Fetch):**
```html
<div x-data="{ posts: [] }">
    <button @click="loadPosts()">Load</button>
    <div x-for="post in posts">...</div>
</div>
```

**What Livewire does:**
```html
<div>
    <button wire:click="loadPosts">Load</button>
    @foreach($posts as $post)
        ...
    @endforeach
</div>
```

**Livewire = Alpine + Fetch + PHP Magic**

You now understand what Livewire does under the hood!

---

## Best Practices for SPAs

### 1. Handle Loading States

```javascript
{
    loading: false,
    async loadData() {
        this.loading = true;
        try {
            // fetch data
        } finally {
            this.loading = false;
        }
    }
}
```

### 2. Cache Data When Appropriate

```javascript
{
    cache: new Map(),
    async loadData(id) {
        if (this.cache.has(id)) {
            return this.cache.get(id);
        }
        const data = await fetch(`/api/data/${id}`);
        this.cache.set(id, data);
        return data;
    }
}
```

### 3. Handle Errors Gracefully

```javascript
async fetchData() {
    try {
        const response = await fetch('/api/data');
        if (!response.ok) throw new Error('Failed');
        return await response.json();
    } catch (error) {
        this.showError(error.message);
        return null;
    }
}
```

### 4. Use URL for State

```javascript
// Good - state in URL
window.history.pushState({}, '', '/posts/123');

// Bad - state only in JavaScript
this.currentPostId = 123;
```

### 5. Optimize Performance

- Lazy load images
- Debounce user input
- Cancel outdated requests
- Use pagination/infinite scroll
- Cache API responses

---

## Summary

**SPA Core Concepts:**
1. **Client-Side Routing** - Navigate without page reloads
2. **State Management** - Centralized data store
3. **Page Transitions** - Smooth animations
4. **History Management** - Browser back/forward
5. **Data Fetching** - Load data asynchronously

**You Can Now Build:**
- Single Page Applications
- Dynamic dashboards
- Real-time interfaces
- E-commerce frontends
- Social media apps

**Next Steps:**
- Module 14-17: Laravel (server-side framework)
- Future: React/Vue (advanced SPAs)
- Future: Livewire (Alpine + PHP)

**You've mastered modern JavaScript!**

---

## Practice Exercises

**Exercise 1:** Build a complete SPA with:
- Home page with product list
- Product detail page
- Shopping cart (persistent with localStorage)
- Checkout form

**Exercise 2:** Create a social media feed with:
- Infinite scroll
- Like/comment functionality
- Real-time updates (polling)
- User profiles

**Exercise 3:** Build a dashboard with:
- Multiple views (overview, analytics, settings)
- Client-side routing
- Data visualization
- Real-time data updates

---

## Final Module Summary

**Module 13 Complete! You learned:**

1. AJAX history and concepts
2. Fetch API fundamentals
3. HTTP methods (GET, POST, PUT, DELETE)
4. JSON handling and error management
5. Loading states and UX patterns
6. Alpine.js + Fetch combination
7. Live search and real-time features
8. SPA architecture and patterns

**You're ready for:**
- Module 14: Laravel Basics
- Building production-ready web applications
- Understanding modern frameworks
- Creating amazing user experiences

**Congratulations!** You've completed the JavaScript journey!
