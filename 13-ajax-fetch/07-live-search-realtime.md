# Lesson 07 - Live Search and Real-Time Features

**Duration**: 60-70 minutes
**Prerequisites**: Lesson 06 - Alpine.js + Fetch Combination

---

## Introduction

Live search is one of the most powerful UX features you can implement. Users expect instant results as they type - like Google, Amazon, or any modern web app. This lesson teaches you to build production-ready live search and other real-time features.

**What you'll learn:**
- Build live search with instant feedback
- Implement autocomplete/suggestions
- Optimize search performance
- Handle rapid requests efficiently
- Create real-time data updates
- Build notification systems

---

## Basic Live Search

### Simple Search Implementation

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Search</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }

        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 15px 45px 15px 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #3498db;
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .search-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            color: #666;
            font-size: 14px;
        }

        .result-card {
            border: 1px solid #e0e0e0;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .result-card:hover {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .result-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 8px;
        }

        .result-excerpt {
            color: #666;
            line-height: 1.6;
        }

        .highlight {
            background: #fff59d;
            padding: 2px 4px;
            border-radius: 2px;
            font-weight: 500;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top-color: #3498db;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container" x-data="liveSearch()">
        <h1 style="margin-bottom: 30px;">Live Search</h1>

        <!-- Search Box -->
        <div class="search-box">
            <input
                type="text"
                class="search-input"
                x-model="query"
                @input.debounce.300ms="search"
                placeholder="Search posts, articles, or topics..."
                autofocus
            >
            <span class="search-icon">
                <span x-show="!searching">🔍</span>
                <span x-show="searching" class="spinner"></span>
            </span>
        </div>

        <!-- Search Meta Info -->
        <div class="search-meta" x-show="query">
            <span x-show="!searching && results.length > 0">
                Found <strong x-text="results.length"></strong> results
                in <strong x-text="searchTime"></strong>ms
            </span>
            <span x-show="searching">Searching...</span>
        </div>

        <!-- Results -->
        <div x-show="results.length > 0">
            <template x-for="result in results" :key="result.id">
                <div class="result-card">
                    <h3 class="result-title" x-html="highlightMatches(result.title)"></h3>
                    <p class="result-excerpt" x-html="highlightMatches(result.excerpt)"></p>
                    <small style="color: #999;">
                        By <span x-text="result.author"></span> •
                        <span x-text="result.date"></span>
                    </small>
                </div>
            </template>
        </div>

        <!-- No Results -->
        <div x-show="!searching && query && results.length === 0" class="no-results">
            <p style="font-size: 24px; margin-bottom: 10px;">😕</p>
            <p>No results found for "<strong x-text="query"></strong>"</p>
            <p style="margin-top: 10px; font-size: 14px;">Try different keywords</p>
        </div>
    </div>

    <script>
        function liveSearch() {
            return {
                query: '',
                results: [],
                searching: false,
                searchTime: 0,

                async search() {
                    // Clear results if query is empty
                    if (!this.query.trim()) {
                        this.results = [];
                        return;
                    }

                    this.searching = true;
                    const startTime = Date.now();

                    try {
                        const params = new URLSearchParams({
                            q: this.query,
                            limit: 20
                        });

                        const response = await fetch(`/api/search.php?${params}`);

                        if (!response.ok) {
                            throw new Error('Search failed');
                        }

                        const data = await response.json();

                        this.results = data.results || [];
                        this.searchTime = Date.now() - startTime;

                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];

                    } finally {
                        this.searching = false;
                    }
                },

                highlightMatches(text) {
                    if (!this.query || !text) return text;

                    // Escape special regex characters
                    const escapedQuery = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

                    // Split query into words for better highlighting
                    const words = escapedQuery.split(/\s+/).filter(w => w.length > 0);
                    const regex = new RegExp(`(${words.join('|')})`, 'gi');

                    return text.replace(regex, '<span class="highlight">$1</span>');
                }
            }
        }
    </script>
</body>
</html>
```

### PHP Search API

```php
<?php
// /api/search.php
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$limit = (int)($_GET['limit'] ?? 20);

if (empty($query)) {
    echo json_encode(['results' => []]);
    exit;
}

// In production, search database with full-text search
// This is a simple example

// Connect to database
$pdo = new PDO('mysql:host=localhost;dbname=your_db', 'username', 'password');

// Full-text search query
$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        LEFT(content, 200) as excerpt,
        author,
        DATE_FORMAT(created_at, '%b %d, %Y') as date
    FROM posts
    WHERE MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE)
    LIMIT ?
");

$stmt->execute([$query, $limit]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'results' => $results,
    'query' => $query,
    'count' => count($results)
]);
```

---

## Autocomplete / Suggestions

### Search with Dropdown Suggestions

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Autocomplete Search</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .autocomplete-container {
            position: relative;
            max-width: 600px;
            margin: 50px auto;
        }

        .search-input {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .suggestion-item {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover,
        .suggestion-item.selected {
            background: #f5f5f5;
        }

        .suggestion-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .suggestion-meta {
            font-size: 12px;
            color: #999;
        }

        .no-suggestions {
            padding: 20px;
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="autocomplete-container" x-data="autocomplete()">
        <input
            type="text"
            class="search-input"
            x-model="query"
            @input.debounce.200ms="fetchSuggestions"
            @focus="showDropdown = true"
            @blur="hideDropdown"
            @keydown.down.prevent="selectNext"
            @keydown.up.prevent="selectPrevious"
            @keydown.enter.prevent="selectCurrent"
            @keydown.escape="showDropdown = false"
            placeholder="Search..."
            autocomplete="off"
        >

        <!-- Suggestions Dropdown -->
        <div
            x-show="showDropdown && query"
            x-transition
            class="suggestions-dropdown"
        >
            <!-- Loading -->
            <div x-show="loading" class="no-suggestions">
                Searching...
            </div>

            <!-- Suggestions -->
            <template x-if="!loading && suggestions.length > 0">
                <div>
                    <template x-for="(suggestion, index) in suggestions" :key="suggestion.id">
                        <div
                            class="suggestion-item"
                            :class="{ 'selected': selectedIndex === index }"
                            @mousedown.prevent="selectSuggestion(suggestion)"
                            @mouseenter="selectedIndex = index"
                        >
                            <div class="suggestion-title" x-text="suggestion.title"></div>
                            <div class="suggestion-meta" x-text="suggestion.category"></div>
                        </div>
                    </template>
                </div>
            </template>

            <!-- No Results -->
            <div
                x-show="!loading && query && suggestions.length === 0"
                class="no-suggestions"
            >
                No suggestions found
            </div>
        </div>
    </div>

    <script>
        function autocomplete() {
            return {
                query: '',
                suggestions: [],
                loading: false,
                showDropdown: false,
                selectedIndex: -1,

                async fetchSuggestions() {
                    if (!this.query.trim()) {
                        this.suggestions = [];
                        return;
                    }

                    this.loading = true;
                    this.selectedIndex = -1;

                    try {
                        const response = await fetch(`/api/suggestions.php?q=${encodeURIComponent(this.query)}`);
                        const data = await response.json();

                        this.suggestions = data.suggestions || [];
                        this.showDropdown = true;

                    } catch (error) {
                        console.error('Fetch error:', error);
                        this.suggestions = [];

                    } finally {
                        this.loading = false;
                    }
                },

                selectNext() {
                    if (this.selectedIndex < this.suggestions.length - 1) {
                        this.selectedIndex++;
                    }
                },

                selectPrevious() {
                    if (this.selectedIndex > 0) {
                        this.selectedIndex--;
                    }
                },

                selectCurrent() {
                    if (this.selectedIndex >= 0 && this.suggestions[this.selectedIndex]) {
                        this.selectSuggestion(this.suggestions[this.selectedIndex]);
                    }
                },

                selectSuggestion(suggestion) {
                    this.query = suggestion.title;
                    this.showDropdown = false;
                    this.suggestions = [];

                    // Navigate or perform action
                    console.log('Selected:', suggestion);
                    window.location.href = `/posts/${suggestion.id}`;
                },

                hideDropdown() {
                    // Small delay to allow click events to fire
                    setTimeout(() => {
                        this.showDropdown = false;
                    }, 200);
                }
            }
        }
    </script>
</body>
</html>
```

### PHP Suggestions API

```php
<?php
// /api/suggestions.php
header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (empty($query)) {
    echo json_encode(['suggestions' => []]);
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=your_db', 'username', 'password');

// Get suggestions - limit to 10 for performance
$stmt = $pdo->prepare("
    SELECT
        id,
        title,
        category
    FROM posts
    WHERE title LIKE CONCAT('%', ?, '%')
    ORDER BY views DESC
    LIMIT 10
");

$stmt->execute([$query]);
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'suggestions' => $suggestions
]);
```

---

## Advanced Search with Filters

### Multi-Filter Search

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Search</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }

        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        select, input[type="date"], input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .active-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tag button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            line-height: 1;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .result-card {
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="container" x-data="advancedSearch()">
        <h1>Advanced Search</h1>

        <!-- Filters -->
        <div class="filters">
            <div class="filter-group">
                <label>Keyword</label>
                <input
                    type="text"
                    x-model="filters.keyword"
                    @input.debounce.300ms="search"
                    placeholder="Search..."
                >
            </div>

            <div class="filter-group">
                <label>Category</label>
                <select x-model="filters.category" @change="search">
                    <option value="">All Categories</option>
                    <option value="tech">Technology</option>
                    <option value="design">Design</option>
                    <option value="business">Business</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Min Price</label>
                <input
                    type="number"
                    x-model.number="filters.minPrice"
                    @input.debounce.500ms="search"
                    placeholder="0"
                >
            </div>

            <div class="filter-group">
                <label>Max Price</label>
                <input
                    type="number"
                    x-model.number="filters.maxPrice"
                    @input.debounce.500ms="search"
                    placeholder="1000"
                >
            </div>

            <div class="filter-group">
                <label>Sort By</label>
                <select x-model="filters.sortBy" @change="search">
                    <option value="relevance">Relevance</option>
                    <option value="date">Date</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
            </div>
        </div>

        <!-- Active Filters -->
        <div x-show="activeFiltersCount > 0" class="active-filters">
            <template x-if="filters.keyword">
                <div class="filter-tag">
                    Keyword: <strong x-text="filters.keyword"></strong>
                    <button @click="clearFilter('keyword')">×</button>
                </div>
            </template>

            <template x-if="filters.category">
                <div class="filter-tag">
                    Category: <strong x-text="filters.category"></strong>
                    <button @click="clearFilter('category')">×</button>
                </div>
            </template>

            <button
                @click="clearAllFilters"
                style="background: #f44336; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer;"
            >
                Clear All
            </button>
        </div>

        <!-- Results Count -->
        <div style="margin-bottom: 20px; color: #666;">
            <span x-show="!loading">
                Found <strong x-text="results.length"></strong> results
            </span>
            <span x-show="loading">Searching...</span>
        </div>

        <!-- Results -->
        <div class="results-grid">
            <template x-for="result in results" :key="result.id">
                <div class="result-card">
                    <h3 x-text="result.title"></h3>
                    <p style="color: #666; font-size: 14px;" x-text="result.category"></p>
                    <p style="font-weight: bold; color: #2196f3;" x-text="'$' + result.price"></p>
                </div>
            </template>
        </div>

        <!-- No Results -->
        <div x-show="!loading && results.length === 0" style="text-align: center; padding: 40px; color: #999;">
            No results found. Try adjusting your filters.
        </div>
    </div>

    <script>
        function advancedSearch() {
            return {
                filters: {
                    keyword: '',
                    category: '',
                    minPrice: null,
                    maxPrice: null,
                    sortBy: 'relevance'
                },
                results: [],
                loading: false,

                init() {
                    this.search();
                },

                get activeFiltersCount() {
                    let count = 0;
                    if (this.filters.keyword) count++;
                    if (this.filters.category) count++;
                    if (this.filters.minPrice !== null) count++;
                    if (this.filters.maxPrice !== null) count++;
                    return count;
                },

                async search() {
                    this.loading = true;

                    try {
                        // Build query params
                        const params = new URLSearchParams();

                        Object.entries(this.filters).forEach(([key, value]) => {
                            if (value !== null && value !== '') {
                                params.append(key, value);
                            }
                        });

                        const response = await fetch(`/api/advanced-search.php?${params}`);
                        const data = await response.json();

                        this.results = data.results || [];

                    } catch (error) {
                        console.error('Search error:', error);
                        this.results = [];

                    } finally {
                        this.loading = false;
                    }
                },

                clearFilter(filterName) {
                    if (filterName === 'minPrice' || filterName === 'maxPrice') {
                        this.filters[filterName] = null;
                    } else {
                        this.filters[filterName] = '';
                    }
                    this.search();
                },

                clearAllFilters() {
                    this.filters = {
                        keyword: '',
                        category: '',
                        minPrice: null,
                        maxPrice: null,
                        sortBy: 'relevance'
                    };
                    this.search();
                }
            }
        }
    </script>
</body>
</html>
```

---

## Real-Time Notifications

### Polling for Updates

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-Time Notifications</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .notifications-bell {
            position: relative;
            cursor: pointer;
            font-size: 24px;
        }

        .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #f44336;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }

        .notifications-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 350px;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-top: 10px;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f5f5f5;
        }

        .notification-item.unread {
            background: #e3f2fd;
        }

        .notification-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 12px;
            color: #999;
        }
    </style>
</head>
<body>
    <div style="padding: 20px;" x-data="notificationSystem()">
        <!-- Notifications Bell -->
        <div style="display: flex; justify-content: flex-end;">
            <div class="notifications-bell" @click="toggleDropdown">
                🔔
                <span x-show="unreadCount > 0" class="badge" x-text="unreadCount"></span>

                <!-- Dropdown -->
                <div
                    x-show="showDropdown"
                    x-transition
                    @click.away="showDropdown = false"
                    class="notifications-dropdown"
                >
                    <div style="padding: 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                        <strong>Notifications</strong>
                        <button
                            x-show="unreadCount > 0"
                            @click="markAllAsRead"
                            style="background: none; border: none; color: #2196f3; cursor: pointer; font-size: 14px;"
                        >
                            Mark all as read
                        </button>
                    </div>

                    <template x-if="notifications.length === 0">
                        <div style="padding: 40px; text-align: center; color: #999;">
                            No notifications
                        </div>
                    </template>

                    <template x-for="notification in notifications" :key="notification.id">
                        <div
                            class="notification-item"
                            :class="{ 'unread': !notification.read }"
                            @click="markAsRead(notification.id)"
                        >
                            <div class="notification-title" x-text="notification.title"></div>
                            <div style="color: #666; font-size: 14px; margin-bottom: 5px;" x-text="notification.message"></div>
                            <div class="notification-time" x-text="formatTime(notification.created_at)"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <h1>Real-Time Notifications Demo</h1>
        <p>New notifications will appear automatically every 30 seconds</p>
    </div>

    <script>
        function notificationSystem() {
            return {
                notifications: [],
                showDropdown: false,
                pollingInterval: null,

                init() {
                    this.fetchNotifications();
                    this.startPolling();
                },

                get unreadCount() {
                    return this.notifications.filter(n => !n.read).length;
                },

                async fetchNotifications() {
                    try {
                        const response = await fetch('/api/notifications.php');
                        const data = await response.json();

                        this.notifications = data.notifications || [];

                    } catch (error) {
                        console.error('Fetch error:', error);
                    }
                },

                startPolling() {
                    // Poll every 30 seconds
                    this.pollingInterval = setInterval(() => {
                        this.fetchNotifications();
                    }, 30000);
                },

                stopPolling() {
                    if (this.pollingInterval) {
                        clearInterval(this.pollingInterval);
                    }
                },

                toggleDropdown() {
                    this.showDropdown = !this.showDropdown;
                    if (this.showDropdown) {
                        this.fetchNotifications();
                    }
                },

                async markAsRead(notificationId) {
                    try {
                        await fetch(`/api/notifications.php?id=${notificationId}`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ read: true })
                        });

                        // Update locally
                        const notification = this.notifications.find(n => n.id === notificationId);
                        if (notification) {
                            notification.read = true;
                        }

                    } catch (error) {
                        console.error('Mark as read error:', error);
                    }
                },

                async markAllAsRead() {
                    try {
                        await fetch('/api/notifications.php', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ read_all: true })
                        });

                        // Update all locally
                        this.notifications.forEach(n => n.read = true);

                    } catch (error) {
                        console.error('Mark all as read error:', error);
                    }
                },

                formatTime(timestamp) {
                    const date = new Date(timestamp);
                    const now = new Date();
                    const diffMs = now - date;
                    const diffMins = Math.floor(diffMs / 60000);

                    if (diffMins < 1) return 'Just now';
                    if (diffMins < 60) return `${diffMins}m ago`;

                    const diffHours = Math.floor(diffMins / 60);
                    if (diffHours < 24) return `${diffHours}h ago`;

                    const diffDays = Math.floor(diffHours / 24);
                    return `${diffDays}d ago`;
                }
            }
        }
    </script>
</body>
</html>
```

---

## Performance Optimization

### Request Cancellation

When user types quickly, cancel old requests:

```javascript
function optimizedSearch() {
    return {
        query: '',
        results: [],
        loading: false,
        abortController: null,

        async search() {
            // Cancel previous request
            if (this.abortController) {
                this.abortController.abort();
            }

            if (!this.query.trim()) {
                this.results = [];
                return;
            }

            // Create new abort controller
            this.abortController = new AbortController();
            this.loading = true;

            try {
                const response = await fetch(`/api/search.php?q=${this.query}`, {
                    signal: this.abortController.signal
                });

                const data = await response.json();
                this.results = data.results;

            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Search error:', error);
                }
            } finally {
                this.loading = false;
                this.abortController = null;
            }
        }
    }
}
```

### Caching Results

Cache search results to avoid duplicate requests:

```javascript
function cachedSearch() {
    return {
        query: '',
        results: [],
        cache: new Map(),
        loading: false,

        async search() {
            if (!this.query.trim()) {
                this.results = [];
                return;
            }

            // Check cache first
            if (this.cache.has(this.query)) {
                this.results = this.cache.get(this.query);
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`/api/search.php?q=${this.query}`);
                const data = await response.json();

                this.results = data.results;

                // Cache results
                this.cache.set(this.query, this.results);

                // Limit cache size
                if (this.cache.size > 50) {
                    const firstKey = this.cache.keys().next().value;
                    this.cache.delete(firstKey);
                }

            } catch (error) {
                console.error('Search error:', error);
            } finally {
                this.loading = false;
            }
        }
    }
}
```

---

## Summary

**Key Patterns:**
1. **Live Search** - Instant results with debouncing
2. **Autocomplete** - Dropdown suggestions with keyboard navigation
3. **Advanced Filters** - Multiple criteria search
4. **Real-Time Notifications** - Polling for updates
5. **Request Cancellation** - Abort outdated requests
6. **Result Caching** - Avoid duplicate API calls

**Best Practices:**
- Debounce user input (200-300ms)
- Show loading states
- Highlight search matches
- Provide keyboard navigation
- Cancel outdated requests
- Cache results when appropriate
- Optimize database queries with indexes

**Performance Tips:**
- Use full-text search in database
- Limit results (10-20 items)
- Add database indexes
- Cache frequent searches
- Use CDN for static assets

---

## Practice Exercises

**Exercise 1:** Build a product search with price range filter and category selection

**Exercise 2:** Create an autocomplete that searches multiple data sources (users, posts, products)

**Exercise 3:** Implement a notification system that polls every 30 seconds and shows toast on new notifications

---

**Next up:** Lesson 08 - SPA Concepts and Advanced Patterns - Build single-page applications!
