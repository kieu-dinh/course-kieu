# Asset Optimization

**Duration**: 3-4 hours

---

## Introduction

Assets (CSS, JavaScript, images, fonts) often account for 60-80% of page load time. Optimizing these files can dramatically improve user experience. In this lesson, you'll learn how to minimize, bundle, compress, and deliver assets efficiently.

**What you'll learn:**
- CSS and JavaScript optimization
- Image optimization techniques
- Using Laravel Vite for asset bundling
- CDN integration
- Browser caching strategies
- Performance measurement

---

## Understanding Asset Performance

### The Cost of Unoptimized Assets

**Typical unoptimized page:**
```
index.html:           5 KB
styles.css:         150 KB
app.js:             300 KB
jquery.js:          100 KB
bootstrap.js:        75 KB
5 images:          2000 KB (2 MB)
2 fonts:            200 KB
-----------------------------------
Total:             2830 KB (~2.8 MB)

Load time on 3G: ~15 seconds
```

**Same page optimized:**
```
index.html:           5 KB (gzipped: 2 KB)
styles.min.css:      40 KB (gzipped: 8 KB)
app.min.js:          80 KB (gzipped: 25 KB)
images (optimized): 400 KB
fonts (woff2):       50 KB
-----------------------------------
Total:              575 KB

Load time on 3G: ~3 seconds
```

**Result:** 80% reduction in size, 5x faster load time!

### Performance Impact

**Every 100ms delay:**
- Amazon: 1% decrease in sales
- Google: 0.5% drop in searches
- Users: Increased frustration

**Mobile users:**
- 53% abandon sites that take > 3 seconds
- Average mobile site takes 15 seconds to load
- Optimized sites have 70% longer sessions

---

## CSS Optimization

### 1. Minimize CSS

**Manual approach (don't do this):**
```css
/* Before: 10 KB */
.header {
    background-color: #ffffff;
    padding: 20px;
    margin: 0 auto;
}

.button {
    background-color: #007bff;
    color: white;
    padding: 10px 20px;
}
```

```css
/* After minification: 6 KB */
.header{background-color:#fff;padding:20px;margin:0 auto}.button{background-color:#007bff;color:#fff;padding:10px 20px}
```

**Automated with Vite** (Laravel 9+):
```js
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        minify: 'terser', // Minify in production
    },
});
```

Build for production:
```bash
npm run build
```

### 2. Remove Unused CSS

**Problem: Tailwind or Bootstrap includes thousands of unused classes**

```css
/* Bootstrap: 150 KB */
/* You use: 20% of classes */
/* Waste: 120 KB */
```

**Solution: PurgeCSS (built into Tailwind)**

```js
// tailwind.config.js
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    // Tailwind automatically removes unused classes in production
}
```

**Result:**
```
Development: 3.5 MB (all classes)
Production:   15 KB (only used classes)
```

### 3. Critical CSS

Load above-the-fold CSS inline, defer the rest:

```blade
{{-- In <head> --}}
<style>
    /* Inline critical CSS for above-the-fold content */
    .header { ... }
    .hero { ... }
</style>

{{-- Load full CSS asynchronously --}}
<link rel="preload" href="{{ asset('css/app.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset('css/app.css') }}"></noscript>
```

### 4. Use CSS Variables for Faster Rendering

```css
/* ✅ Good: CSS variables are faster */
:root {
    --primary-color: #007bff;
    --secondary-color: #6c757d;
}

.button {
    background: var(--primary-color);
}

/* ❌ Bad: SASS variables require compilation */
$primary-color: #007bff;
.button {
    background: $primary-color;
}
```

---

## JavaScript Optimization

### 1. Minify and Bundle

**Problem: Multiple script files = multiple requests**
```html
<script src="jquery.js"></script>        <!-- 100 KB -->
<script src="bootstrap.js"></script>     <!-- 75 KB -->
<script src="alpine.js"></script>        <!-- 40 KB -->
<script src="app.js"></script>           <!-- 50 KB -->
<!-- 4 requests, 265 KB total -->
```

**Solution: Bundle with Vite**
```js
// resources/js/app.js
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

```bash
npm run build
# Creates: public/build/assets/app-[hash].js (80 KB minified)
# 1 request instead of 4!
```

### 2. Code Splitting

Load code only when needed:

```js
// ❌ Bad: Load everything upfront
import Chart from 'chart.js';
import DatePicker from 'datepicker';
import Editor from 'editor';
// All loaded even if not used on this page

// ✅ Good: Dynamic imports
document.getElementById('showChart').addEventListener('click', async () => {
    const { Chart } = await import('chart.js');
    // Only loaded when user clicks button
});
```

**In Laravel with Vite:**
```js
// resources/js/app.js
if (document.getElementById('chart')) {
    import('./modules/chart.js').then(module => {
        module.initChart();
    });
}
```

### 3. Defer and Async

**Blocking script (bad):**
```html
<script src="app.js"></script>
<!-- Browser stops rendering, downloads script, executes, then continues -->
```

**Defer (good for most scripts):**
```html
<script src="app.js" defer></script>
<!-- Downloaded in parallel, executes after DOM is ready -->
```

**Async (for independent scripts):**
```html
<script src="analytics.js" async></script>
<!-- Downloaded and executed as soon as ready, doesn't block -->
```

**In Laravel with Vite:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
<!-- Vite automatically adds appropriate defer/async -->
```

### 4. Tree Shaking

Remove unused code from libraries:

```js
// ❌ Bad: Imports entire Lodash (70 KB)
import _ from 'lodash';
_.debounce(fn, 300);

// ✅ Good: Import only what you need (5 KB)
import debounce from 'lodash/debounce';
debounce(fn, 300);
```

Vite does this automatically in production builds.

---

## Image Optimization

### 1. Choose the Right Format

**JPEG:**
- Best for: Photos, complex images
- Pros: Small file size, good quality
- Cons: No transparency
- Use when: Realistic photos

**PNG:**
- Best for: Logos, icons, transparency needed
- Pros: Lossless, transparency
- Cons: Larger than JPEG
- Use when: Need transparency or very sharp edges

**WebP:**
- Best for: Everything (modern browsers)
- Pros: 25-35% smaller than JPEG/PNG, supports transparency
- Cons: Not supported in old browsers
- Use when: Progressive enhancement possible

**SVG:**
- Best for: Icons, logos, simple graphics
- Pros: Scalable, tiny file size
- Cons: Not suitable for photos
- Use when: Vector graphics

### 2. Compress Images

**Tools:**

**TinyPNG/TinyJPG** (online):
- Reduces PNG files by 50-70%
- Reduces JPEG files by 40-60%
- Visit: tinypng.com

**ImageOptim** (Mac):
```bash
# Compress images
imageoptim /path/to/images/*.jpg
```

**Laravel Package:**
```bash
composer require spatie/laravel-image-optimizer

php artisan vendor:publish --provider="Spatie\LaravelImageOptimizer\ImageOptimizerServiceProvider"
```

```php
use Spatie\ImageOptimizer\OptimizerChainFactory;

$optimizerChain = OptimizerChainFactory::create();
$optimizerChain->optimize($pathToImage);
```

**In upload handler:**
```php
public function store(Request $request)
{
    $path = $request->file('image')->store('images');

    // Optimize the uploaded image
    $optimizerChain = OptimizerChainFactory::create();
    $optimizerChain->optimize(storage_path('app/' . $path));

    return back()->with('success', 'Image uploaded and optimized!');
}
```

### 3. Responsive Images

Serve different sizes for different screens:

```blade
<img
    src="{{ asset('images/photo-800.jpg') }}"
    srcset="
        {{ asset('images/photo-400.jpg') }} 400w,
        {{ asset('images/photo-800.jpg') }} 800w,
        {{ asset('images/photo-1200.jpg') }} 1200w
    "
    sizes="(max-width: 600px) 400px, (max-width: 1200px) 800px, 1200px"
    alt="Description"
>
```

**Explanation:**
- Mobile (< 600px): Loads 400px version
- Tablet (600-1200px): Loads 800px version
- Desktop (> 1200px): Loads 1200px version

**Generate thumbnails on upload:**
```php
use Intervention\Image\Facades\Image;

public function store(Request $request)
{
    $image = Image::make($request->file('image'));

    // Save different sizes
    $image->resize(400, null, function ($constraint) {
        $constraint->aspectRatio();
    })->save(storage_path('app/public/images/photo-400.jpg'));

    $image->resize(800, null, function ($constraint) {
        $constraint->aspectRatio();
    })->save(storage_path('app/public/images/photo-800.jpg'));

    $image->resize(1200, null, function ($constraint) {
        $constraint->aspectRatio();
    })->save(storage_path('app/public/images/photo-1200.jpg'));
}
```

### 4. Lazy Loading

Load images only when they're about to be visible:

```blade
<img
    src="{{ asset('images/placeholder.jpg') }}"
    data-src="{{ asset('images/photo.jpg') }}"
    loading="lazy"
    alt="Description"
>
```

**Native lazy loading (modern browsers):**
```html
<img src="image.jpg" loading="lazy" alt="Description">
```

**Alpine.js lazy loading:**
```blade
<div x-data="{ loaded: false }" x-intersect="loaded = true">
    <img
        x-show="loaded"
        x-bind:src="loaded ? '{{ asset('images/photo.jpg') }}' : ''"
        alt="Description"
    >
</div>
```

### 5. Use CDN for Images

```blade
{{-- ❌ Bad: Serve from your server --}}
<img src="{{ asset('images/photo.jpg') }}">

{{-- ✅ Good: Serve from CDN --}}
<img src="https://cdn.yourdomain.com/images/photo.jpg">
```

**Benefits:**
- Faster delivery (geographically closer servers)
- Reduced load on your server
- Better caching
- Automatic optimization (some CDNs)

---

## Laravel Vite Setup

### Installation (Laravel 9+)

Vite is included by default in new Laravel projects.

**package.json:**
```json
{
    "devDependencies": {
        "axios": "^1.1.2",
        "laravel-vite-plugin": "^0.7.2",
        "vite": "^4.0.0"
    }
}
```

**vite.config.js:**
```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
```

### Using Vite in Blade

**In layout:**
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

### Development

```bash
npm run dev
```

Vite starts a development server with:
- Hot Module Replacement (HMR)
- Instant updates
- No build step needed

### Production Build

```bash
npm run build
```

Creates optimized assets in `public/build/`:
- Minified CSS and JS
- Versioned filenames (cache busting)
- Source maps (for debugging)

---

## Browser Caching

### Understanding Cache Headers

**Cache-Control header tells browsers how long to cache:**

```
Cache-Control: public, max-age=31536000
```

- `public`: Can be cached by browsers and CDNs
- `private`: Only cached by user's browser
- `max-age=31536000`: Cache for 1 year (in seconds)
- `no-cache`: Check with server before using cached version
- `no-store`: Don't cache at all

### Setting Cache Headers in Laravel

**In your web server (nginx):**
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

**Or in Laravel (less efficient):**
```php
// In a middleware
public function handle($request, Closure $next)
{
    $response = $next($request);

    if ($request->is('css/*') || $request->is('js/*')) {
        $response->header('Cache-Control', 'public, max-age=31536000');
    }

    return $response;
}
```

### Vite's Built-in Cache Busting

Vite automatically versions files:

```
# Development
http://localhost:5173/resources/css/app.css

# Production
/build/assets/app.a3f2b1c9.css
/build/assets/app.d4e5f6g7.js
```

When you update code, the hash changes, forcing browsers to download new version.

---

## CDN Integration

### What Is a CDN?

Content Delivery Network: Servers around the world that cache and serve your assets.

**Without CDN:**
```
User in Japan → Your server in USA (300ms latency)
```

**With CDN:**
```
User in Japan → CDN server in Tokyo (20ms latency)
```

### Setting Up a CDN

**Popular CDNs:**
- Cloudflare (free tier available)
- AWS CloudFront
- DigitalOcean Spaces + CDN
- BunnyCDN

**Configuration in Laravel:**

```php
// config/filesystems.php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],

    'cdn' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('CDN_URL'), // Your CDN URL
    ],
],
```

**.env:**
```
CDN_URL=https://cdn.yourdomain.com
ASSET_URL=https://cdn.yourdomain.com
```

**Upload to CDN:**
```php
// Upload file to CDN
Storage::disk('cdn')->put('images/photo.jpg', $fileContents);

// Generate CDN URL
$url = Storage::disk('cdn')->url('images/photo.jpg');
// https://cdn.yourdomain.com/images/photo.jpg
```

**Use in views:**
```blade
<img src="{{ Storage::disk('cdn')->url('images/photo.jpg') }}">
```

---

## Font Optimization

### 1. Use Modern Font Formats

**Format priority:**
1. WOFF2 (best compression, modern browsers)
2. WOFF (older browsers)
3. TTF/OTF (fallback)

```css
@font-face {
    font-family: 'MyFont';
    src: url('/fonts/myfont.woff2') format('woff2'),
         url('/fonts/myfont.woff') format('woff');
    font-display: swap; /* Show fallback font while loading */
}
```

### 2. Subset Fonts

Only include characters you need:

```bash
# Using pyftsubset (install from pip)
pyftsubset font.ttf \
    --output-file=font-subset.woff2 \
    --flavor=woff2 \
    --unicodes=U+0020-007F  # Only basic Latin characters
```

**Result:**
- Full font: 200 KB
- Subset font: 30 KB

### 3. Font Display Strategy

```css
@font-face {
    font-family: 'MyFont';
    src: url('/fonts/myfont.woff2') format('woff2');
    font-display: swap; /* Options: auto, block, swap, fallback, optional */
}
```

**Options:**
- `swap`: Show fallback immediately, swap when custom font loads (recommended)
- `block`: Hide text briefly, then show custom font
- `fallback`: Show fallback, swap to custom if loads within 3s
- `optional`: Use custom font only if cached

### 4. Preload Critical Fonts

```blade
<link rel="preload" href="/fonts/myfont.woff2" as="font" type="font/woff2" crossorigin>
```

Tells browser to download font immediately.

---

## Performance Measurement

### Tools

**1. Chrome DevTools:**
- Network tab: See all requests and sizes
- Lighthouse: Comprehensive performance audit
- Performance tab: Timeline of page load

**2. WebPageTest.org:**
- Test from multiple locations
- Detailed waterfall charts
- Film strip view

**3. GTmetrix:**
- Page speed score
- Recommendations
- Historical tracking

### Key Metrics

**LCP (Largest Contentful Paint):**
- Good: < 2.5s
- Needs improvement: 2.5-4s
- Poor: > 4s

**FID (First Input Delay):**
- Good: < 100ms
- Needs improvement: 100-300ms
- Poor: > 300ms

**CLS (Cumulative Layout Shift):**
- Good: < 0.1
- Needs improvement: 0.1-0.25
- Poor: > 0.25

### Laravel Debugbar Performance Tab

```bash
composer require barryvdh/laravel-debugbar --dev
```

Shows:
- Page load time
- Memory usage
- Database queries
- View rendering time

---

## Complete Optimization Checklist

```bash
# 1. Install dependencies
npm install

# 2. Build for production
npm run build

# 3. Optimize images
# - Use WebP format
# - Compress all images
# - Generate responsive sizes

# 4. Enable caching
# - Set cache headers
# - Use CDN
# - Enable browser caching

# 5. Optimize CSS
# - Remove unused CSS (PurgeCSS/Tailwind)
# - Minify CSS
# - Inline critical CSS

# 6. Optimize JavaScript
# - Minify JS
# - Code splitting
# - Defer/async non-critical scripts

# 7. Optimize fonts
# - Use WOFF2
# - Subset fonts
# - Preload critical fonts

# 8. Test
# - Run Lighthouse
# - Test on real devices
# - Check WebPageTest
```

---

## Quick Reference

```bash
# Vite commands
npm run dev           # Development with HMR
npm run build         # Production build

# Optimization
composer require spatie/laravel-image-optimizer  # Image optimization
composer require intervention/image              # Image manipulation
composer require barryvdh/laravel-debugbar --dev # Performance debugging
```

```blade
{{-- Vite assets --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Lazy loading --}}
<img src="image.jpg" loading="lazy">

{{-- Responsive images --}}
<img srcset="small.jpg 400w, large.jpg 800w" sizes="(max-width: 600px) 400px, 800px">

{{-- Preload --}}
<link rel="preload" href="font.woff2" as="font" crossorigin>
```

---

## Practice Questions

1. **What are the three most impactful asset optimizations?** Why?

2. **What's the difference between defer and async?** When would you use each?

3. **What is lazy loading?** How does it improve performance?

4. **What is a CDN?** What are its benefits?

5. **What file format is best for photos?** What about logos?

6. **What does Vite do?** How is it different from Laravel Mix?

---

## Next Steps

Now you understand asset optimization! Next, you'll learn about server setup and configuration for deployment.

**Coming up in Lesson 06:**
- Server requirements
- Web server configuration (nginx)
- PHP-FPM setup
- Security hardening
- Performance tuning
