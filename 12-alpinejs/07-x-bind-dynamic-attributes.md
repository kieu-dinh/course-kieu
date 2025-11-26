# Lesson 07 - x-bind (Dynamic Attributes)

**Duration**: 1-1.5 hours

---

## What is x-bind?

`x-bind` lets you **dynamically set HTML attributes** based on your data.

Instead of hardcoding attributes, you can make them reactive!

### Static vs Dynamic Attributes

**Static (hardcoded):**
```html
<img src="photo.jpg" alt="My Photo" class="rounded">
```

**Dynamic (reactive):**
```html
<img
    x-bind:src="currentPhoto"
    x-bind:alt="photoDescription"
    x-bind:class="photoClass"
>
```

When data changes → attributes update automatically!

---

## Basic Syntax

### Long Form

```html
<div x-bind:class="myClass">Content</div>
```

### Short Form (Recommended)

```html
<div :class="myClass">Content</div>
```

`:` is shorthand for `x-bind:` - much cleaner!

---

## Common Use Cases

### Binding Classes

```html
<div x-data="{ active: false }">
    <button
        @click="active = !active"
        :class="active ? 'bg-blue-500' : 'bg-gray-500'"
    >
        Toggle (<span x-text="active ? 'Active' : 'Inactive'"></span>)
    </button>
</div>
```

### Binding Styles

```html
<div x-data="{ color: 'red', size: 20 }">
    <div :style="`color: ${color}; font-size: ${size}px;`">
        Styled Text
    </div>

    <input type="color" x-model="color">
    <input type="number" x-model.number="size" min="10" max="100">
</div>
```

### Binding Images

```html
<div x-data="{
    photos: [
        'photo1.jpg',
        'photo2.jpg',
        'photo3.jpg'
    ],
    currentIndex: 0,
    get currentPhoto() {
        return this.photos[this.currentIndex]
    }
}">
    <img :src="currentPhoto" :alt="`Photo ${currentIndex + 1}`">

    <button @click="currentIndex = (currentIndex - 1 + photos.length) % photos.length">
        Previous
    </button>
    <button @click="currentIndex = (currentIndex + 1) % photos.length">
        Next
    </button>
</div>
```

### Binding Links

```html
<div x-data="{
    url: 'https://google.com',
    openInNewTab: true
}">
    <a
        :href="url"
        :target="openInNewTab ? '_blank' : '_self'"
    >
        Visit Link
    </a>

    <label>
        <input type="checkbox" x-model="openInNewTab">
        Open in new tab
    </label>
</div>
```

### Disabled State

```html
<div x-data="{ agreed: false }">
    <label>
        <input type="checkbox" x-model="agreed">
        I agree to the terms
    </label>

    <button :disabled="!agreed">
        Continue
    </button>
</div>
```

---

## Class Binding

Classes are the most common use case for x-bind!

### String Syntax

```html
<div x-data="{ status: 'success' }">
    <div :class="`alert alert-${status}`">
        Message
    </div>
</div>
```

### Ternary Expression

```html
<div x-data="{ isActive: false }">
    <button
        @click="isActive = !isActive"
        :class="isActive ? 'active' : 'inactive'"
    >
        Toggle
    </button>
</div>
```

### Object Syntax

```html
<div x-data="{ isActive: false, hasError: false }">
    <div :class="{
        'active': isActive,
        'error': hasError,
        'base-class': true
    }">
        Content
    </div>

    <button @click="isActive = !isActive">Toggle Active</button>
    <button @click="hasError = !hasError">Toggle Error</button>
</div>
```

**How it works:**
- Property is the class name
- Value is true/false
- Only classes with `true` values are added

### Array Syntax

```html
<div x-data="{
    baseClass: 'btn',
    isPrimary: true,
    isLarge: false
}">
    <button :class="[
        baseClass,
        isPrimary ? 'btn-primary' : 'btn-secondary',
        isLarge ? 'btn-lg' : 'btn-sm'
    ]">
        Button
    </button>
</div>
```

### Combining with Static Classes

```html
<div x-data="{ isActive: false }">
    <!-- Static classes stay, dynamic classes change -->
    <div class="container" :class="{ 'active': isActive }">
        Content
    </div>
</div>
```

Static and dynamic classes merge together!

---

## Style Binding

### String Syntax

```html
<div x-data="{ color: 'blue', fontSize: 20 }">
    <div :style="`color: ${color}; font-size: ${fontSize}px;`">
        Styled Text
    </div>
</div>
```

### Object Syntax

```html
<div x-data="{ color: 'blue', fontSize: 20 }">
    <div :style="{
        color: color,
        fontSize: fontSize + 'px',
        fontWeight: 'bold'
    }">
        Styled Text
    </div>
</div>
```

### Conditional Styles

```html
<div x-data="{ isDark: false }">
    <div :style="{
        background: isDark ? '#1f2937' : '#f3f4f6',
        color: isDark ? '#f9fafb' : '#1f2937',
        padding: '2rem'
    }">
        <p>Theme: <span x-text="isDark ? 'Dark' : 'Light'"></span></p>
        <button @click="isDark = !isDark">Toggle Theme</button>
    </div>
</div>
```

---

## Binding Multiple Attributes

You can bind multiple attributes on one element:

```html
<div x-data="{
    imageUrl: 'photo.jpg',
    imageAlt: 'Beautiful photo',
    imageWidth: 300,
    isRounded: true
}">
    <img
        :src="imageUrl"
        :alt="imageAlt"
        :width="imageWidth"
        :class="{ 'rounded': isRounded }"
    >
</div>
```

---

## Special Bindings

### Boolean Attributes

Some HTML attributes are boolean (present = true, absent = false):

```html
<div x-data="{ isDisabled: false, isChecked: true, isHidden: false }">
    <button :disabled="isDisabled">Button</button>
    <input type="checkbox" :checked="isChecked">
    <div :hidden="isHidden">Content</div>
</div>
```

Alpine automatically handles these correctly!

### Data Attributes

```html
<div x-data="{ userId: 123, userRole: 'admin' }">
    <div
        :data-user-id="userId"
        :data-role="userRole"
    >
        User Info
    </div>
</div>
```

### ARIA Attributes (Accessibility)

```html
<div x-data="{ isExpanded: false }">
    <button
        @click="isExpanded = !isExpanded"
        :aria-expanded="isExpanded"
        :aria-label="isExpanded ? 'Collapse' : 'Expand'"
    >
        Toggle
    </button>

    <div :aria-hidden="!isExpanded">
        Collapsible content
    </div>
</div>
```

---

## Dynamic Component Patterns

### Pattern 1: Button States

```html
<div x-data="{
    state: 'idle',
    get buttonText() {
        if (this.state === 'idle') return 'Click me'
        if (this.state === 'loading') return 'Loading...'
        if (this.state === 'success') return 'Success!'
        if (this.state === 'error') return 'Error!'
    },
    get buttonClass() {
        const base = 'px-4 py-2 rounded '
        if (this.state === 'idle') return base + 'bg-blue-500'
        if (this.state === 'loading') return base + 'bg-gray-400'
        if (this.state === 'success') return base + 'bg-green-500'
        if (this.state === 'error') return base + 'bg-red-500'
    },
    async handleClick() {
        this.state = 'loading'
        await new Promise(resolve => setTimeout(resolve, 2000))
        this.state = Math.random() > 0.5 ? 'success' : 'error'

        setTimeout(() => {
            this.state = 'idle'
        }, 2000)
    }
}">
    <button
        @click="handleClick()"
        :class="buttonClass"
        :disabled="state === 'loading'"
        x-text="buttonText"
    ></button>
</div>
```

### Pattern 2: Tab Navigation

```html
<div x-data="{ activeTab: 'home' }">
    <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e5e7eb;">
        <button
            @click="activeTab = 'home'"
            :class="{
                'border-b-2 border-blue-500 text-blue-600': activeTab === 'home',
                'text-gray-600': activeTab !== 'home'
            }"
            style="padding: 0.5rem 1rem; background: none; border: none; cursor: pointer;"
        >
            Home
        </button>

        <button
            @click="activeTab = 'profile'"
            :class="{
                'border-b-2 border-blue-500 text-blue-600': activeTab === 'profile',
                'text-gray-600': activeTab !== 'profile'
            }"
            style="padding: 0.5rem 1rem; background: none; border: none; cursor: pointer;"
        >
            Profile
        </button>

        <button
            @click="activeTab = 'settings'"
            :class="{
                'border-b-2 border-blue-500 text-blue-600': activeTab === 'settings',
                'text-gray-600': activeTab !== 'settings'
            }"
            style="padding: 0.5rem 1rem; background: none; border: none; cursor: pointer;"
        >
            Settings
        </button>
    </div>

    <div style="padding: 1rem;">
        <div x-show="activeTab === 'home'">Home content</div>
        <div x-show="activeTab === 'profile'">Profile content</div>
        <div x-show="activeTab === 'settings'">Settings content</div>
    </div>
</div>
```

### Pattern 3: Progress Bar

```html
<div x-data="{
    progress: 0,
    start() {
        this.progress = 0
        const interval = setInterval(() => {
            this.progress += 10
            if (this.progress >= 100) {
                clearInterval(interval)
            }
        }, 500)
    }
}">
    <div
        style="width: 100%; height: 30px; background: #e5e7eb; border-radius: 0.375rem; overflow: hidden;"
    >
        <div
            :style="`width: ${progress}%; height: 100%; background: #3b82f6; transition: width 0.3s;`"
        ></div>
    </div>

    <p><span x-text="progress"></span>%</p>

    <button @click="start()" :disabled="progress > 0 && progress < 100">
        Start
    </button>
</div>
```

### Pattern 4: Card with Hover Effect

```html
<div x-data="{ isHovered: false }">
    <div
        @mouseenter="isHovered = true"
        @mouseleave="isHovered = false"
        :class="{
            'transform scale-105 shadow-lg': isHovered,
            'transform scale-100 shadow': !isHovered
        }"
        style="transition: all 0.3s; padding: 2rem; border-radius: 0.5rem; border: 1px solid #e5e7eb; cursor: pointer;"
    >
        <h3>Hover over me!</h3>
        <p>I change when you hover</p>
    </div>
</div>
```

### Pattern 5: Alert with Types

```html
<div x-data="{
    type: 'info',
    message: 'This is an alert message',
    get alertClass() {
        const base = 'p-4 rounded mb-4 '
        if (this.type === 'success') return base + 'bg-green-100 text-green-800 border-l-4 border-green-500'
        if (this.type === 'error') return base + 'bg-red-100 text-red-800 border-l-4 border-red-500'
        if (this.type === 'warning') return base + 'bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500'
        return base + 'bg-blue-100 text-blue-800 border-l-4 border-blue-500'
    },
    get icon() {
        if (this.type === 'success') return '✓'
        if (this.type === 'error') return '✗'
        if (this.type === 'warning') return '⚠'
        return 'ℹ'
    }
}">
    <div>
        <button @click="type = 'success'">Success</button>
        <button @click="type = 'error'">Error</button>
        <button @click="type = 'warning'">Warning</button>
        <button @click="type = 'info'">Info</button>
    </div>

    <div :class="alertClass">
        <span x-text="icon" style="margin-right: 0.5rem; font-weight: bold;"></span>
        <span x-text="message"></span>
    </div>
</div>
```

---

## Working with Tailwind CSS

Alpine and Tailwind are perfect together!

### Dynamic Tailwind Classes

```html
<div x-data="{
    size: 'md',
    color: 'blue',
    get buttonClasses() {
        const sizes = {
            sm: 'px-2 py-1 text-sm',
            md: 'px-4 py-2 text-base',
            lg: 'px-6 py-3 text-lg'
        }

        const colors = {
            blue: 'bg-blue-500 hover:bg-blue-600',
            green: 'bg-green-500 hover:bg-green-600',
            red: 'bg-red-500 hover:bg-red-600'
        }

        return `${sizes[this.size]} ${colors[this.color]} text-white rounded`
    }
}">
    <button :class="buttonClasses">
        Dynamic Button
    </button>

    <div>
        <select x-model="size">
            <option value="sm">Small</option>
            <option value="md">Medium</option>
            <option value="lg">Large</option>
        </select>

        <select x-model="color">
            <option value="blue">Blue</option>
            <option value="green">Green</option>
            <option value="red">Red</option>
        </select>
    </div>
</div>
```

### Responsive Classes with State

```html
<div x-data="{ isMobileMenuOpen: false }">
    <nav class="flex items-center justify-between p-4 bg-gray-800 text-white">
        <div class="text-xl font-bold">Logo</div>

        <!-- Mobile menu button -->
        <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="md:hidden"
        >
            ☰
        </button>

        <!-- Desktop menu -->
        <div class="hidden md:flex gap-4">
            <a href="#" class="hover:text-blue-400">Home</a>
            <a href="#" class="hover:text-blue-400">About</a>
            <a href="#" class="hover:text-blue-400">Contact</a>
        </div>
    </nav>

    <!-- Mobile menu -->
    <div
        x-show="isMobileMenuOpen"
        class="md:hidden bg-gray-700 text-white p-4"
    >
        <a href="#" class="block py-2">Home</a>
        <a href="#" class="block py-2">About</a>
        <a href="#" class="block py-2">Contact</a>
    </div>
</div>
```

---

## Performance Tip: Computed Classes

For complex class logic, use computed properties:

**Bad (recalculates on every render):**
```html
<div :class="isActive ? 'bg-blue-500 text-white p-4 rounded shadow-lg' : 'bg-gray-200 text-gray-800 p-4 rounded'">
    Content
</div>
```

**Good (computed once, cached):**
```html
<div x-data="{
    isActive: false,
    get containerClass() {
        const base = 'p-4 rounded'
        return this.isActive
            ? `${base} bg-blue-500 text-white shadow-lg`
            : `${base} bg-gray-200 text-gray-800`
    }
}">
    <div :class="containerClass">
        Content
    </div>
</div>
```

---

## Common Mistakes

### Mistake 1: Forgetting the Colon

```html
<!-- Wrong - treats as regular attribute -->
<div class="myClass">Content</div>

<!-- Correct - dynamic binding -->
<div :class="myClass">Content</div>
```

### Mistake 2: Quotes Inside Strings

```html
<!-- Wrong - syntax error -->
<div :class="isActive ? 'active' : 'inactive'">

<!-- Correct - use different quotes -->
<div :class="isActive ? 'active' : 'inactive'">
```

### Mistake 3: Binding Style Without Units

```html
<!-- Wrong - no units -->
<div :style="`font-size: ${size}`">Text</div>

<!-- Correct - include units -->
<div :style="`font-size: ${size}px`">Text</div>
```

---

## Practice Exercise

Create a **theme switcher** with multiple themes and customization options.

### Requirements

1. Three preset themes: Light, Dark, Blue
2. Custom theme with controls for:
   - Background color
   - Text color
   - Font size
3. Apply theme to a preview card
4. Save selected theme (bonus: use localStorage)

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Switcher</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
        }
        .theme-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .theme-selector button {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            background: white;
        }
        .theme-selector button.active {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .preview-card {
            padding: 2rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }
        .controls {
            margin: 1rem 0;
            padding: 1rem;
            background: #f3f4f6;
            border-radius: 0.375rem;
        }
        .controls label {
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- TODO: Add your theme switcher component -->
    <h1>Theme Switcher</h1>
</body>
</html>
```

### Hints

- Store themes as objects with background, text, fontSize properties
- Use computed property for current theme
- Bind style object to preview card
- Use :class for active button state

Try it yourself first!

---

## Solution

<details>
<summary>Click to reveal solution</summary>

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Switcher</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        .theme-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .theme-selector button {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.375rem;
            cursor: pointer;
            background: white;
        }
        .theme-selector button.active {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .preview-card {
            padding: 2rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        .controls {
            margin: 1rem 0;
            padding: 1rem;
            background: #f3f4f6;
            border-radius: 0.375rem;
        }
        .controls label {
            display: block;
            margin-bottom: 0.5rem;
        }
        .controls input {
            margin-left: 0.5rem;
        }
    </style>
</head>
<body>
    <div x-data="{
        selectedTheme: 'light',
        themes: {
            light: {
                background: '#ffffff',
                color: '#1f2937',
                fontSize: 16
            },
            dark: {
                background: '#1f2937',
                color: '#f9fafb',
                fontSize: 16
            },
            blue: {
                background: '#dbeafe',
                color: '#1e40af',
                fontSize: 16
            },
            custom: {
                background: '#ffffff',
                color: '#000000',
                fontSize: 16
            }
        },
        get currentTheme() {
            return this.themes[this.selectedTheme]
        },
        get previewStyle() {
            return {
                backgroundColor: this.currentTheme.background,
                color: this.currentTheme.color,
                fontSize: this.currentTheme.fontSize + 'px'
            }
        }
    }">
        <h1>Theme Switcher</h1>

        <div class="theme-selector">
            <button
                @click="selectedTheme = 'light'"
                :class="{ 'active': selectedTheme === 'light' }"
            >
                Light Theme
            </button>

            <button
                @click="selectedTheme = 'dark'"
                :class="{ 'active': selectedTheme === 'dark' }"
            >
                Dark Theme
            </button>

            <button
                @click="selectedTheme = 'blue'"
                :class="{ 'active': selectedTheme === 'blue' }"
            >
                Blue Theme
            </button>

            <button
                @click="selectedTheme = 'custom'"
                :class="{ 'active': selectedTheme === 'custom' }"
            >
                Custom Theme
            </button>
        </div>

        <div x-show="selectedTheme === 'custom'" class="controls">
            <h3>Customize Theme</h3>

            <label>
                Background Color:
                <input
                    type="color"
                    x-model="themes.custom.background"
                >
            </label>

            <label>
                Text Color:
                <input
                    type="color"
                    x-model="themes.custom.color"
                >
            </label>

            <label>
                Font Size:
                <input
                    type="range"
                    x-model.number="themes.custom.fontSize"
                    min="12"
                    max="32"
                >
                <span x-text="themes.custom.fontSize + 'px'"></span>
            </label>
        </div>

        <div class="preview-card" :style="previewStyle">
            <h2>Preview Card</h2>
            <p>
                This is how your content will look with the
                <strong x-text="selectedTheme"></strong> theme.
            </p>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
            </p>
            <button :style="{
                background: currentTheme.color,
                color: currentTheme.background,
                padding: '0.5rem 1rem',
                border: 'none',
                borderRadius: '0.375rem',
                cursor: 'pointer'
            }">
                Sample Button
            </button>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #f9fafb; border-radius: 0.375rem;">
            <h3>Current Theme Values:</h3>
            <pre x-text="JSON.stringify(currentTheme, null, 2)"></pre>
        </div>
    </div>
</body>
</html>
```

**Key concepts:**
1. `:class` with object syntax for active button state
2. `:style` with object syntax for theme styles
3. `x-model` with color and range inputs
4. Computed `currentTheme` property for active theme
5. Computed `previewStyle` property for card styling
6. `x-show` to conditionally show custom controls
7. Dynamic button styling based on theme
8. Smooth transitions with CSS

</details>

---

## Key Takeaways

1. **x-bind makes attributes dynamic** - Any HTML attribute can be reactive
2. **`:` is shorthand for x-bind** - Use `:class` instead of `x-bind:class`
3. **Class binding has special syntax** - Objects, arrays, strings all work
4. **Style binding supports objects** - Cleaner than template strings
5. **Boolean attributes work automatically** - Alpine handles them correctly
6. **Perfect with Tailwind** - Dynamic utility classes are powerful
7. **Use computed properties for complex logic** - Keeps templates clean

**Next lesson**: We'll explore Alpine components, plugins, and best practices!
