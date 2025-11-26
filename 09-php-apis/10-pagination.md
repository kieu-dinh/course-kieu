# Lesson 10 - Pagination and Large Datasets

**Duration**: 45-60 minutes

---

## Why Pagination?

Imagine your API returns all 1 million products in one request:
- **Slow**: Takes forever to load
- **Memory**: Server runs out of memory
- **Bandwidth**: Huge response size
- **Unusable**: Client can't handle that much data

**Solution:** Return data in small chunks (pages)!

---

## Types of Pagination

### 1. Offset/Limit Pagination (Most Common)

Request specific page and number of items:
```
GET /api/posts?page=2&per_page=10
```

**Pros:**
- Simple to implement
- Can jump to any page
- Total count available

**Cons:**
- Slow for large offsets (OFFSET 10000)
- Inconsistent if data changes between requests

### 2. Cursor Pagination

Use a cursor (pointer) to next set of results:
```
GET /api/posts?cursor=eyJpZCI6MTAwfQ&limit=10
```

**Pros:**
- Fast for any position
- Consistent even if data changes
- Good for infinite scroll

**Cons:**
- Can't jump to specific page
- No total count

### 3. Keyset Pagination

Use last item's ID as starting point:
```
GET /api/posts?after_id=100&limit=10
```

**Pros:**
- Very fast
- Simple

**Cons:**
- Only works with sortable unique fields
- Can't go backwards easily

We'll implement all three!

---

## Offset/Limit Pagination

### Basic Implementation

```php
<?php
// controllers/PostController.php

public function index() {
    // Get pagination parameters
    $page = Request::query('page', 1);
    $perPage = Request::query('per_page', 10);

    // Validate and constrain
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage)); // Max 100 items

    // Calculate offset
    $offset = ($page - 1) * $perPage;

    // Get posts
    $postModel = new Post();
    $posts = $postModel->paginate($offset, $perPage);

    // Get total count
    $total = $postModel->count();

    // Calculate pagination metadata
    $lastPage = ceil($total / $perPage);

    Response::success($posts, 200, [
        'pagination' => [
            'total' => $total,
            'count' => count($posts),
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $lastPage,
            'links' => [
                'first' => '/api/posts?page=1&per_page=' . $perPage,
                'last' => '/api/posts?page=' . $lastPage . '&per_page=' . $perPage,
                'prev' => $page > 1 ? '/api/posts?page=' . ($page - 1) . '&per_page=' . $perPage : null,
                'next' => $page < $lastPage ? '/api/posts?page=' . ($page + 1) . '&per_page=' . $perPage : null
            ]
        ]
    ]);
}
```

### Model Method

```php
// models/Post.php

public function paginate($offset, $limit) {
    $stmt = $this->db->prepare("
        SELECT * FROM posts
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

public function count() {
    return $this->db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
}
```

### Response Example

```json
{
  "success": true,
  "data": [
    {"id": 11, "title": "Post 11"},
    {"id": 12, "title": "Post 12"}
  ],
  "meta": {
    "pagination": {
      "total": 50,
      "count": 10,
      "per_page": 10,
      "current_page": 2,
      "total_pages": 5,
      "links": {
        "first": "/api/posts?page=1&per_page=10",
        "last": "/api/posts?page=5&per_page=10",
        "prev": "/api/posts?page=1&per_page=10",
        "next": "/api/posts?page=3&per_page=10"
      }
    }
  }
}
```

---

## Paginator Helper Class

Reusable pagination logic:

```php
<?php
// helpers/Paginator.php

class Paginator {
    private $items;
    private $total;
    private $perPage;
    private $currentPage;
    private $path;

    public function __construct($items, $total, $perPage, $currentPage, $path = '') {
        $this->items = $items;
        $this->total = (int)$total;
        $this->perPage = (int)$perPage;
        $this->currentPage = (int)$currentPage;
        $this->path = $path;
    }

    /**
     * Get pagination metadata
     */
    public function meta() {
        $lastPage = $this->lastPage();

        return [
            'total' => $this->total,
            'count' => count($this->items),
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'total_pages' => $lastPage,
            'from' => $this->from(),
            'to' => $this->to(),
            'links' => $this->links()
        ];
    }

    /**
     * Calculate last page
     */
    public function lastPage() {
        return max(1, ceil($this->total / $this->perPage));
    }

    /**
     * Calculate "from" number (1-based)
     */
    public function from() {
        return $this->total > 0 ? (($this->currentPage - 1) * $this->perPage) + 1 : null;
    }

    /**
     * Calculate "to" number
     */
    public function to() {
        return $this->total > 0 ? min($this->currentPage * $this->perPage, $this->total) : null;
    }

    /**
     * Check if on first page
     */
    public function onFirstPage() {
        return $this->currentPage <= 1;
    }

    /**
     * Check if has more pages
     */
    public function hasMorePages() {
        return $this->currentPage < $this->lastPage();
    }

    /**
     * Generate pagination links
     */
    public function links() {
        $lastPage = $this->lastPage();

        $links = [
            'first' => $this->url(1),
            'last' => $this->url($lastPage),
            'prev' => $this->currentPage > 1 ? $this->url($this->currentPage - 1) : null,
            'next' => $this->currentPage < $lastPage ? $this->url($this->currentPage + 1) : null
        ];

        return $links;
    }

    /**
     * Generate URL for page
     */
    private function url($page) {
        $query = http_build_query([
            'page' => $page,
            'per_page' => $this->perPage
        ]);

        return $this->path . '?' . $query;
    }

    /**
     * Get items
     */
    public function items() {
        return $this->items;
    }

    /**
     * Convert to array for response
     */
    public function toArray() {
        return [
            'data' => $this->items,
            'meta' => [
                'pagination' => $this->meta()
            ]
        ];
    }
}
```

### Using Paginator

```php
public function index() {
    $page = Request::query('page', 1);
    $perPage = Request::query('per_page', 10);

    // Validate
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));

    $offset = ($page - 1) * $perPage;

    // Get data
    $posts = Post::paginate($offset, $perPage);
    $total = Post::count();

    // Create paginator
    $paginator = new Paginator($posts, $total, $perPage, $page, '/api/posts');

    // Send response
    $response = $paginator->toArray();
    Response::success($response['data'], 200, $response['meta']);
}
```

---

## Pagination with Filters and Sorting

```php
public function index() {
    // Pagination
    $page = Request::query('page', 1);
    $perPage = Request::query('per_page', 10);

    // Filters
    $author = Request::query('author');
    $category = Request::query('category');
    $search = Request::query('search');

    // Sorting
    $sortBy = Request::query('sort', 'created_at');
    $sortOrder = Request::query('order', 'DESC');

    // Validate
    $page = max(1, (int)$page);
    $perPage = max(1, min(100, (int)$perPage));
    $offset = ($page - 1) * $perPage;

    // Whitelist sort fields
    $allowedSort = ['id', 'title', 'created_at', 'views'];
    if (!in_array($sortBy, $allowedSort)) {
        $sortBy = 'created_at';
    }

    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

    // Build query
    $sql = "SELECT * FROM posts WHERE 1=1";
    $params = [];

    if ($author) {
        $sql .= " AND author_id = ?";
        $params[] = $author;
    }

    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }

    if ($search) {
        $sql .= " AND (title LIKE ? OR content LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }

    // Add sorting
    $sql .= " ORDER BY {$sortBy} {$sortOrder}";

    // Count total (before LIMIT)
    $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetchColumn();

    // Add pagination
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    // Execute
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Return paginated response
    $paginator = new Paginator($posts, $total, $perPage, $page, '/api/posts');
    $response = $paginator->toArray();

    Response::success($response['data'], 200, $response['meta']);
}
```

**Request:**
```
GET /api/posts?page=2&per_page=20&author=5&category=tech&search=laravel&sort=views&order=DESC
```

---

## Cursor Pagination

Better for infinite scroll and large datasets:

```php
<?php
// controllers/PostController.php

public function indexCursor() {
    $cursor = Request::query('cursor');
    $limit = Request::query('limit', 10);

    // Validate
    $limit = max(1, min(100, (int)$limit));

    // Decode cursor (base64 encoded JSON)
    $cursorData = null;
    if ($cursor) {
        $cursorData = json_decode(base64_decode($cursor), true);
    }

    // Build query
    $sql = "SELECT * FROM posts WHERE 1=1";
    $params = [];

    if ($cursorData && isset($cursorData['id'])) {
        // Get posts after this ID
        $sql .= " AND id < ?";
        $params[] = $cursorData['id'];
    }

    $sql .= " ORDER BY id DESC LIMIT ?";
    $params[] = $limit + 1; // Get one extra to check if more exist

    // Execute
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Check if more pages exist
    $hasMore = count($posts) > $limit;
    if ($hasMore) {
        array_pop($posts); // Remove extra item
    }

    // Generate next cursor
    $nextCursor = null;
    if ($hasMore && !empty($posts)) {
        $lastPost = end($posts);
        $nextCursor = base64_encode(json_encode(['id' => $lastPost['id']]));
    }

    Response::success($posts, 200, [
        'cursor' => [
            'next' => $nextCursor,
            'has_more' => $hasMore
        ]
    ]);
}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {"id": 100, "title": "Post 100"},
    {"id": 99, "title": "Post 99"}
  ],
  "meta": {
    "cursor": {
      "next": "eyJpZCI6OTl9",
      "has_more": true
    }
  }
}
```

**Next request:**
```
GET /api/posts?cursor=eyJpZCI6OTl9&limit=10
```

---

## Keyset Pagination

Simplest and fastest:

```php
public function indexKeyset() {
    $afterId = Request::query('after_id', 0);
    $limit = Request::query('limit', 10);

    // Validate
    $afterId = max(0, (int)$afterId);
    $limit = max(1, min(100, (int)$limit));

    // Get posts after ID
    $stmt = $db->prepare("
        SELECT * FROM posts
        WHERE id > ?
        ORDER BY id ASC
        LIMIT ?
    ");

    $stmt->execute([$afterId, $limit + 1]);
    $posts = $stmt->fetchAll();

    // Check if more exist
    $hasMore = count($posts) > $limit;
    if ($hasMore) {
        array_pop($posts);
    }

    // Get last ID for next request
    $lastId = !empty($posts) ? end($posts)['id'] : null;

    Response::success($posts, 200, [
        'keyset' => [
            'last_id' => $lastId,
            'has_more' => $hasMore
        ]
    ]);
}
```

**Request:**
```
GET /api/posts?after_id=100&limit=10
```

---

## Performance Optimization

### 1. Add Database Indexes

```sql
-- Index for pagination (ORDER BY created_at DESC)
CREATE INDEX idx_posts_created_at ON posts(created_at DESC);

-- Composite index for filtered pagination
CREATE INDEX idx_posts_author_created ON posts(author_id, created_at DESC);

-- Index for cursor/keyset pagination
CREATE INDEX idx_posts_id ON posts(id DESC);
```

### 2. Avoid COUNT(*) for Large Tables

```php
// Slow for large tables
$total = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();

// Alternative: Estimate
$total = $db->query("
    SELECT table_rows
    FROM information_schema.tables
    WHERE table_name = 'posts'
")->fetchColumn();

// Or: Cache the count
$total = Cache::remember('posts_count', 300, function() use ($db) {
    return $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
});
```

### 3. Use Cursor for Large Offsets

```php
// Slow (OFFSET 10000)
SELECT * FROM posts ORDER BY id LIMIT 10 OFFSET 10000;

// Fast (cursor/keyset)
SELECT * FROM posts WHERE id < 5000 ORDER BY id DESC LIMIT 10;
```

---

## Laravel Pagination (Preview)

Later with Laravel, pagination is much simpler:

```php
// Offset pagination
$posts = Post::paginate(10);

// Cursor pagination
$posts = Post::cursorPaginate(10);

// Simple pagination (no total count)
$posts = Post::simplePaginate(10);

// Return as JSON
return response()->json($posts);
```

Laravel handles everything automatically!

---

## Client-Side Usage

### JavaScript (Fetch API)

```javascript
let currentPage = 1;

async function loadPosts() {
  const response = await fetch(`/api/posts?page=${currentPage}&per_page=10`);
  const data = await response.json();

  // Display posts
  data.data.forEach(post => {
    displayPost(post);
  });

  // Update pagination UI
  const { current_page, total_pages, links } = data.meta.pagination;

  document.getElementById('prev').disabled = !links.prev;
  document.getElementById('next').disabled = !links.next;
  document.getElementById('page-info').textContent = `Page ${current_page} of ${total_pages}`;
}

// Next page
document.getElementById('next').onclick = () => {
  currentPage++;
  loadPosts();
};

// Previous page
document.getElementById('prev').onclick = () => {
  currentPage--;
  loadPosts();
};
```

### Infinite Scroll (Cursor)

```javascript
let cursor = null;
let loading = false;

async function loadMore() {
  if (loading) return;
  loading = true;

  const url = cursor
    ? `/api/posts?cursor=${cursor}&limit=10`
    : '/api/posts?limit=10';

  const response = await fetch(url);
  const data = await response.json();

  // Display posts
  data.data.forEach(post => {
    displayPost(post);
  });

  // Update cursor
  cursor = data.meta.cursor.next;
  loading = false;

  // Stop if no more posts
  if (!data.meta.cursor.has_more) {
    window.removeEventListener('scroll', handleScroll);
  }
}

// Detect scroll to bottom
function handleScroll() {
  const { scrollTop, scrollHeight, clientHeight } = document.documentElement;

  if (scrollTop + clientHeight >= scrollHeight - 100) {
    loadMore();
  }
}

window.addEventListener('scroll', handleScroll);
loadMore(); // Initial load
```

---

## Quick Quiz

**Question 1:** What's the difference between offset and cursor pagination?
<details>
<summary>Answer</summary>
Offset: Jump to any page, but slow for large offsets. Cursor: Fast for any position, but can't jump to specific page.
</details>

**Question 2:** Why limit the maximum per_page value?
<details>
<summary>Answer</summary>
Prevent users from requesting too much data at once, which could overload server memory and bandwidth.
</details>

**Question 3:** Which pagination is best for infinite scroll?
<details>
<summary>Answer</summary>
Cursor or keyset pagination - they're consistent even when data changes and fast for any position.
</details>

**Question 4:** Why is OFFSET 10000 slow?
<details>
<summary>Answer</summary>
Database still needs to read and skip 10,000 rows before returning results. Use cursor/keyset instead.
</details>

**Question 5:** Should you always include total count?
<details>
<summary>Answer</summary>
No. COUNT(*) is expensive on large tables. For infinite scroll, you don't need it. Only use when showing "Page X of Y".
</details>

---

## Summary

You learned:
- Three pagination types: offset/limit, cursor, keyset
- Implementing offset pagination with Paginator helper
- Pagination with filters and sorting
- Cursor pagination for infinite scroll
- Keyset pagination for speed
- Performance optimization with indexes
- Avoiding expensive COUNT queries
- Client-side pagination and infinite scroll
- When to use each pagination type

---

## Next Lesson

**11-cors.md** - Learn about Cross-Origin Resource Sharing (CORS) and how to allow your API to be called from different domains!
