# Lesson 06 - x-model (Two-Way Binding)

**Duration**: 1 hour

---

## What is x-model?

`x-model` creates **two-way data binding** between form inputs and your data.

**Two-way** means:
1. Data → Input: When data changes, input updates
2. Input → Data: When user types, data updates

It's like `x-bind` and `@input` combined into one directive!

---

## Why Use x-model?

### Without x-model (Manual Binding)

```html
<div x-data="{ message: '' }">
    <input
        type="text"
        :value="message"
        @input="message = $event.target.value"
    >
    <p x-text="message"></p>
</div>
```

Lots of boilerplate for a simple input!

### With x-model (Two-Way Binding)

```html
<div x-data="{ message: '' }">
    <input type="text" x-model="message">
    <p x-text="message"></p>
</div>
```

**Much cleaner!** Type in input → data updates → paragraph updates!

---

## Basic Usage

### Text Input

```html
<div x-data="{ name: '' }">
    <input type="text" x-model="name" placeholder="Enter your name">
    <p>Hello, <span x-text="name || 'stranger'"></span>!</p>
</div>
```

### Textarea

```html
<div x-data="{ bio: '' }">
    <textarea x-model="bio" rows="4" placeholder="Tell us about yourself"></textarea>
    <p>Character count: <span x-text="bio.length"></span></p>
</div>
```

### Checkbox (Single)

```html
<div x-data="{ accepted: false }">
    <label>
        <input type="checkbox" x-model="accepted">
        I accept the terms and conditions
    </label>

    <p x-show="accepted">Thank you for accepting!</p>

    <button :disabled="!accepted">Continue</button>
</div>
```

### Checkbox (Multiple)

```html
<div x-data="{ skills: [] }">
    <label>
        <input type="checkbox" value="html" x-model="skills">
        HTML
    </label>
    <label>
        <input type="checkbox" value="css" x-model="skills">
        CSS
    </label>
    <label>
        <input type="checkbox" value="javascript" x-model="skills">
        JavaScript
    </label>

    <p>Selected skills: <span x-text="skills.join(', ')"></span></p>
</div>
```

**Note:** For multiple checkboxes, x-model must bind to an **array**!

### Radio Buttons

```html
<div x-data="{ size: '' }">
    <label>
        <input type="radio" value="small" x-model="size">
        Small
    </label>
    <label>
        <input type="radio" value="medium" x-model="size">
        Medium
    </label>
    <label>
        <input type="radio" value="large" x-model="size">
        Large
    </label>

    <p>Selected size: <span x-text="size || 'None'"></span></p>
</div>
```

### Select Dropdown

```html
<div x-data="{ country: '' }">
    <select x-model="country">
        <option value="">Choose a country</option>
        <option value="us">United States</option>
        <option value="uk">United Kingdom</option>
        <option value="ca">Canada</option>
        <option value="vn">Vietnam</option>
    </select>

    <p>Selected: <span x-text="country || 'None'"></span></p>
</div>
```

### Multi-Select

```html
<div x-data="{ colors: [] }">
    <select x-model="colors" multiple size="5">
        <option value="red">Red</option>
        <option value="green">Green</option>
        <option value="blue">Blue</option>
        <option value="yellow">Yellow</option>
        <option value="purple">Purple</option>
    </select>

    <p>Selected colors: <span x-text="colors.join(', ')"></span></p>
</div>
```

**Note:** For multi-select, x-model must bind to an **array**!

---

## x-model Modifiers

Modifiers change how x-model behaves.

### .lazy

Updates data on `change` event instead of `input`:

```html
<div x-data="{ message: '' }">
    <!-- Updates on every keystroke -->
    <input type="text" x-model="message" placeholder="Instant">

    <!-- Updates only when input loses focus -->
    <input type="text" x-model.lazy="message" placeholder="Lazy">

    <p x-text="message"></p>
</div>
```

**Use `.lazy` for:**
- Performance optimization
- Validation on blur
- Reducing updates

### .number

Automatically converts input value to a number:

```html
<div x-data="{ age: 0 }">
    <!-- Without .number - value is a string -->
    <input type="number" x-model="age">
    <p>Type: <span x-text="typeof age"></span></p>

    <!-- With .number - value is a number -->
    <input type="number" x-model.number="age">
    <p>Type: <span x-text="typeof age"></span></p>

    <!-- Calculations work correctly -->
    <p>In 5 years: <span x-text="age + 5"></span></p>
</div>
```

**Important for:**
- Math calculations
- Comparisons
- Type consistency

### .debounce

Delays updating data until user stops typing:

```html
<div x-data="{ search: '', searching: false }">
    <!-- Updates immediately -->
    <input type="text" x-model="search" placeholder="Instant search">

    <!-- Waits 500ms after user stops typing -->
    <input
        type="text"
        x-model.debounce.500ms="search"
        placeholder="Debounced search"
    >

    <p>Searching for: <span x-text="search"></span></p>
</div>
```

**Perfect for:**
- Search inputs
- API calls
- Expensive operations

### .fill

Pre-fills input with initial value from server:

```html
<div x-data="{ name: 'John Doe' }">
    <!-- Starts with 'John Doe' -->
    <input type="text" x-model.fill="name">
</div>
```

**Use when:**
- Editing existing data
- Form has default values from database
- Want to preserve initial server values

### Combining Modifiers

You can combine modifiers:

```html
<div x-data="{ price: 0 }">
    <input
        type="number"
        x-model.number.debounce.500ms="price"
        placeholder="Enter price"
    >

    <p>Price: $<span x-text="price.toFixed(2)"></span></p>
</div>
```

---

## Working with Objects

x-model works great with object properties:

### Nested Properties

```html
<div x-data="{
    user: {
        name: '',
        email: '',
        age: 0
    }
}">
    <input type="text" x-model="user.name" placeholder="Name">
    <input type="email" x-model="user.email" placeholder="Email">
    <input type="number" x-model.number="user.age" placeholder="Age">

    <pre x-text="JSON.stringify(user, null, 2)"></pre>
</div>
```

### Complex Form Example

```html
<div x-data="{
    form: {
        firstName: '',
        lastName: '',
        email: '',
        password: '',
        newsletter: false,
        role: 'user'
    },
    submit() {
        console.log('Form data:', this.form)
        alert('Check console for form data!')
    }
}">
    <form @submit.prevent="submit()">
        <div>
            <label>First Name:</label>
            <input type="text" x-model="form.firstName" required>
        </div>

        <div>
            <label>Last Name:</label>
            <input type="text" x-model="form.lastName" required>
        </div>

        <div>
            <label>Email:</label>
            <input type="email" x-model="form.email" required>
        </div>

        <div>
            <label>Password:</label>
            <input type="password" x-model="form.password" required>
        </div>

        <div>
            <label>
                <input type="checkbox" x-model="form.newsletter">
                Subscribe to newsletter
            </label>
        </div>

        <div>
            <label>Role:</label>
            <select x-model="form.role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="moderator">Moderator</option>
            </select>
        </div>

        <button type="submit">Submit</button>
    </form>

    <pre x-text="JSON.stringify(form, null, 2)"></pre>
</div>
```

---

## Real-Time Validation

Combine x-model with computed properties for live validation:

### Email Validation

```html
<div x-data="{
    email: '',
    get isValidEmail() {
        return this.email.includes('@') && this.email.includes('.')
    }
}">
    <input
        type="email"
        x-model="email"
        placeholder="Enter email"
        :class="email && !isValidEmail ? 'border-red-500' : ''"
    >

    <p x-show="email && !isValidEmail" style="color: red;">
        Please enter a valid email address
    </p>

    <p x-show="email && isValidEmail" style="color: green;">
        ✓ Email looks good!
    </p>
</div>
```

### Password Strength

```html
<div x-data="{
    password: '',
    get passwordStrength() {
        if (this.password.length === 0) return ''
        if (this.password.length < 6) return 'weak'
        if (this.password.length < 10) return 'medium'
        return 'strong'
    },
    get strengthColor() {
        if (this.passwordStrength === 'weak') return 'red'
        if (this.passwordStrength === 'medium') return 'orange'
        if (this.passwordStrength === 'strong') return 'green'
        return 'gray'
    }
}">
    <input
        type="password"
        x-model="password"
        placeholder="Enter password"
    >

    <div
        x-show="password"
        :style="`color: ${strengthColor}`"
    >
        Password strength:
        <span x-text="passwordStrength"></span>
    </div>
</div>
```

### Form Validation

```html
<div x-data="{
    form: {
        username: '',
        email: '',
        password: '',
        confirmPassword: ''
    },
    errors: {},
    validate() {
        this.errors = {}

        if (this.form.username.length < 3) {
            this.errors.username = 'Username must be at least 3 characters'
        }

        if (!this.form.email.includes('@')) {
            this.errors.email = 'Invalid email address'
        }

        if (this.form.password.length < 8) {
            this.errors.password = 'Password must be at least 8 characters'
        }

        if (this.form.password !== this.form.confirmPassword) {
            this.errors.confirmPassword = 'Passwords do not match'
        }

        return Object.keys(this.errors).length === 0
    },
    submit() {
        if (this.validate()) {
            alert('Form is valid!')
            console.log('Form data:', this.form)
        }
    }
}">
    <form @submit.prevent="submit()">
        <div>
            <input
                type="text"
                x-model="form.username"
                @blur="validate()"
                placeholder="Username"
            >
            <p x-show="errors.username" style="color: red;" x-text="errors.username"></p>
        </div>

        <div>
            <input
                type="email"
                x-model="form.email"
                @blur="validate()"
                placeholder="Email"
            >
            <p x-show="errors.email" style="color: red;" x-text="errors.email"></p>
        </div>

        <div>
            <input
                type="password"
                x-model="form.password"
                @blur="validate()"
                placeholder="Password"
            >
            <p x-show="errors.password" style="color: red;" x-text="errors.password"></p>
        </div>

        <div>
            <input
                type="password"
                x-model="form.confirmPassword"
                @blur="validate()"
                placeholder="Confirm Password"
            >
            <p x-show="errors.confirmPassword" style="color: red;" x-text="errors.confirmPassword"></p>
        </div>

        <button type="submit">Register</button>
    </form>
</div>
```

---

## Common Patterns

### Pattern 1: Character Counter

```html
<div x-data="{ message: '', maxLength: 100 }">
    <textarea
        x-model="message"
        :maxlength="maxLength"
        rows="4"
        placeholder="Enter your message"
    ></textarea>

    <div>
        <span x-text="message.length"></span> / <span x-text="maxLength"></span>
        <span
            x-show="message.length > maxLength * 0.9"
            style="color: orange;"
        >
            (almost there!)
        </span>
    </div>
</div>
```

### Pattern 2: Search Filter

```html
<div x-data="{
    search: '',
    items: ['Apple', 'Banana', 'Cherry', 'Date', 'Elderberry', 'Fig', 'Grape'],
    get filteredItems() {
        if (!this.search) return this.items

        return this.items.filter(item =>
            item.toLowerCase().includes(this.search.toLowerCase())
        )
    }
}">
    <input
        type="text"
        x-model="search"
        placeholder="Search fruits..."
    >

    <ul>
        <template x-for="item in filteredItems" :key="item">
            <li x-text="item"></li>
        </template>
    </ul>

    <p x-show="filteredItems.length === 0">No results found</p>
</div>
```

### Pattern 3: Price Calculator

```html
<div x-data="{
    price: 100,
    quantity: 1,
    taxRate: 0.2,
    discount: 0,
    get subtotal() {
        return this.price * this.quantity
    },
    get discountAmount() {
        return this.subtotal * (this.discount / 100)
    },
    get afterDiscount() {
        return this.subtotal - this.discountAmount
    },
    get tax() {
        return this.afterDiscount * this.taxRate
    },
    get total() {
        return this.afterDiscount + this.tax
    }
}">
    <div>
        <label>Price per item:</label>
        <input type="number" x-model.number="price" min="0">
    </div>

    <div>
        <label>Quantity:</label>
        <input type="number" x-model.number="quantity" min="1">
    </div>

    <div>
        <label>Discount (%):</label>
        <input type="number" x-model.number="discount" min="0" max="100">
    </div>

    <div style="margin-top: 1rem; padding: 1rem; background: #f3f4f6;">
        <p>Subtotal: $<span x-text="subtotal.toFixed(2)"></span></p>
        <p x-show="discount > 0">
            Discount (<span x-text="discount"></span>%):
            -$<span x-text="discountAmount.toFixed(2)"></span>
        </p>
        <p>Tax (20%): $<span x-text="tax.toFixed(2)"></span></p>
        <p style="font-size: 1.25rem; font-weight: bold;">
            Total: $<span x-text="total.toFixed(2)"></span>
        </p>
    </div>
</div>
```

### Pattern 4: Dynamic Form Fields

```html
<div x-data="{
    fields: [
        { id: 1, value: '' }
    ],
    nextId: 2,
    addField() {
        this.fields.push({ id: this.nextId++, value: '' })
    },
    removeField(id) {
        this.fields = this.fields.filter(field => field.id !== id)
    }
}">
    <h3>Add Items</h3>

    <template x-for="field in fields" :key="field.id">
        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
            <input
                type="text"
                x-model="field.value"
                placeholder="Enter item"
            >
            <button
                @click="removeField(field.id)"
                x-show="fields.length > 1"
            >
                Remove
            </button>
        </div>
    </template>

    <button @click="addField()">Add Field</button>

    <div style="margin-top: 1rem;">
        <strong>Items:</strong>
        <ul>
            <template x-for="field in fields" :key="field.id">
                <li x-show="field.value" x-text="field.value"></li>
            </template>
        </ul>
    </div>
</div>
```

### Pattern 5: Toggle List

```html
<div x-data="{
    items: [
        { id: 1, text: 'Learn Alpine.js', done: false },
        { id: 2, text: 'Build a project', done: false },
        { id: 3, text: 'Deploy to production', done: false }
    ],
    get completedCount() {
        return this.items.filter(item => item.done).length
    },
    get progress() {
        return Math.round((this.completedCount / this.items.length) * 100)
    }
}">
    <div style="margin-bottom: 1rem;">
        <strong>Progress: <span x-text="progress"></span>%</strong>
        (<span x-text="completedCount"></span>/<span x-text="items.length"></span> completed)
    </div>

    <template x-for="item in items" :key="item.id">
        <div>
            <label>
                <input type="checkbox" x-model="item.done">
                <span
                    x-text="item.text"
                    :style="item.done ? 'text-decoration: line-through' : ''"
                ></span>
            </label>
        </div>
    </template>
</div>
```

---

## Comparison: Vanilla JS vs Alpine

### Vanilla JavaScript

```html
<div id="app">
    <input id="nameInput" type="text">
    <p>Hello, <span id="nameDisplay">stranger</span>!</p>
</div>

<script>
    const input = document.getElementById('nameInput')
    const display = document.getElementById('nameDisplay')

    input.addEventListener('input', (e) => {
        const value = e.target.value
        display.textContent = value || 'stranger'
    })
</script>
```

### Alpine

```html
<div x-data="{ name: '' }">
    <input type="text" x-model="name">
    <p>Hello, <span x-text="name || 'stranger'"></span>!</p>
</div>
```

**Alpine handles the synchronization automatically!**

---

## Common Mistakes

### Mistake 1: Using x-model on Non-Form Elements

```html
<!-- Wrong - x-model only works on form inputs -->
<div x-model="value">Text</div>

<!-- Correct - use x-text for display -->
<div x-text="value"></div>
```

### Mistake 2: Forgetting .number for Numbers

```html
<div x-data="{ age: 0 }">
    <!-- Wrong - age becomes a string! -->
    <input type="number" x-model="age">

    <!-- Correct - converts to number -->
    <input type="number" x-model.number="age">
</div>
```

### Mistake 3: Wrong Type for Multiple Values

```html
<div x-data="{ skills: '' }">  <!-- Wrong - should be array! -->
    <input type="checkbox" value="html" x-model="skills">
    <input type="checkbox" value="css" x-model="skills">
</div>

<!-- Correct -->
<div x-data="{ skills: [] }">
    <input type="checkbox" value="html" x-model="skills">
    <input type="checkbox" value="css" x-model="skills">
</div>
```

---

## Practice Exercise

Create a **contact form** with validation.

### Requirements

1. Form fields:
   - Name (required, min 3 characters)
   - Email (required, valid format)
   - Subject (required, min 5 characters)
   - Message (required, min 10 characters)
   - Priority (select: low, medium, high)
   - Send copy (checkbox)

2. Features:
   - Real-time character counters
   - Validation on blur
   - Show errors below each field
   - Submit button disabled if form invalid
   - Show success message on submit
   - Reset form after submit

### Starter Code

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .counter {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        button {
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover:not(:disabled) {
            background: #2563eb;
        }
        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .success {
            padding: 1rem;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- TODO: Add your contact form component -->
    <h1>Contact Us</h1>
</body>
</html>
```

### Hints

- Use `x-model` for all form inputs
- Create a `validate()` method that checks all fields
- Use computed property `get isValid()` for submit button
- Show character counters for name, subject, and message
- Clear form with a `reset()` method
- Show success message for 3 seconds then hide it

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
    <title>Contact Form</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: system-ui;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        input, select, textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .error {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .counter {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        button {
            padding: 0.75rem 1.5rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 0.375rem;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover:not(:disabled) {
            background: #2563eb;
        }
        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .success {
            padding: 1rem;
            background: #d1fae5;
            border-left: 4px solid #10b981;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div x-data="{
        form: {
            name: '',
            email: '',
            subject: '',
            message: '',
            priority: 'medium',
            sendCopy: false
        },
        errors: {},
        submitted: false,
        validate() {
            this.errors = {}

            if (this.form.name.length < 3) {
                this.errors.name = 'Name must be at least 3 characters'
            }

            if (!this.form.email.includes('@') || !this.form.email.includes('.')) {
                this.errors.email = 'Please enter a valid email address'
            }

            if (this.form.subject.length < 5) {
                this.errors.subject = 'Subject must be at least 5 characters'
            }

            if (this.form.message.length < 10) {
                this.errors.message = 'Message must be at least 10 characters'
            }

            return Object.keys(this.errors).length === 0
        },
        get isValid() {
            return this.form.name.length >= 3 &&
                   this.form.email.includes('@') &&
                   this.form.subject.length >= 5 &&
                   this.form.message.length >= 10
        },
        submit() {
            if (this.validate()) {
                console.log('Form submitted:', this.form)
                this.submitted = true

                // Hide success message after 3 seconds
                setTimeout(() => {
                    this.submitted = false
                }, 3000)

                this.reset()
            }
        },
        reset() {
            this.form = {
                name: '',
                email: '',
                subject: '',
                message: '',
                priority: 'medium',
                sendCopy: false
            }
        }
    }">
        <h1>Contact Us</h1>

        <div x-show="submitted" class="success">
            <strong>Success!</strong> Your message has been sent.
        </div>

        <form @submit.prevent="submit()">
            <div class="form-group">
                <label>Name *</label>
                <input
                    type="text"
                    x-model="form.name"
                    @blur="validate()"
                    placeholder="Your name"
                >
                <div class="counter" x-text="`${form.name.length} characters`"></div>
                <div x-show="errors.name" class="error" x-text="errors.name"></div>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input
                    type="email"
                    x-model="form.email"
                    @blur="validate()"
                    placeholder="your@email.com"
                >
                <div x-show="errors.email" class="error" x-text="errors.email"></div>
            </div>

            <div class="form-group">
                <label>Subject *</label>
                <input
                    type="text"
                    x-model="form.subject"
                    @blur="validate()"
                    placeholder="Brief subject"
                >
                <div class="counter" x-text="`${form.subject.length} characters`"></div>
                <div x-show="errors.subject" class="error" x-text="errors.subject"></div>
            </div>

            <div class="form-group">
                <label>Message *</label>
                <textarea
                    x-model="form.message"
                    @blur="validate()"
                    rows="5"
                    placeholder="Your message"
                ></textarea>
                <div class="counter" x-text="`${form.message.length} characters`"></div>
                <div x-show="errors.message" class="error" x-text="errors.message"></div>
            </div>

            <div class="form-group">
                <label>Priority</label>
                <select x-model="form.priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" x-model="form.sendCopy">
                    Send me a copy of this message
                </label>
            </div>

            <button type="submit" :disabled="!isValid">
                Send Message
            </button>
        </form>
    </div>
</body>
</html>
```

**Key concepts:**
1. `x-model` binds all form inputs to `form` object
2. `validate()` method checks all fields and sets errors
3. `@blur` triggers validation when field loses focus
4. `isValid` computed property enables/disables submit button
5. Character counters show using `x-text` with string length
6. `submitted` boolean shows success message
7. `setTimeout()` hides success message after 3 seconds
8. `reset()` clears form back to initial state

</details>

---

## Key Takeaways

1. **x-model creates two-way binding** - Changes sync both ways automatically
2. **Works with all form inputs** - Text, textarea, checkbox, radio, select
3. **Modifiers enhance behavior** - `.lazy`, `.number`, `.debounce`, `.fill`
4. **Arrays for multiple values** - Checkboxes and multi-select need arrays
5. **Perfect for validation** - Combine with computed properties and @blur
6. **Clean object binding** - Use nested properties for organized forms
7. **Much simpler than vanilla JS** - No manual event listeners needed

**Next lesson**: We'll explore `x-bind` for dynamic attributes and classes!
