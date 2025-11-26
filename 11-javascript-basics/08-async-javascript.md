# Lesson 08 - Asynchronous JavaScript Basics

**Duration**: 3-4 hours
**Prerequisites**: Lesson 07 - ES6 Features

---

## What is Asynchronous JavaScript?

**Synchronous** = One thing at a time, in order
**Asynchronous** = Multiple things can happen at the same time

### Synchronous Code (Normal)

```javascript
console.log("First");
console.log("Second");
console.log("Third");

// Output:
// First
// Second
// Third
```

Each line waits for the previous one to finish.

### Asynchronous Code

```javascript
console.log("First");

setTimeout(() => {
    console.log("Second");
}, 2000); // Wait 2 seconds

console.log("Third");

// Output:
// First
// Third
// Second (after 2 seconds)
```

"Third" doesn't wait for "Second"!

### Why Do We Need This?

**JavaScript is single-threaded** - it can only do one thing at a time.

**Problems without async:**
- Fetching data from API → Page freezes for 2 seconds
- Reading a file → Page freezes
- Uploading an image → Page freezes

**With async:**
- Start fetching data → Continue doing other things → Handle data when ready
- User can still interact with page while waiting

---

## setTimeout() - Run Code Later

Execute code after a delay.

### Basic Usage

```javascript
setTimeout(() => {
    console.log("This runs after 2 seconds");
}, 2000); // 2000 milliseconds = 2 seconds
```

### With Parameters

```javascript
function greet(name) {
    console.log(`Hello, ${name}!`);
}

// Old way
setTimeout(function() {
    greet("Kieu");
}, 2000);

// Modern way (pass parameters)
setTimeout(greet, 2000, "Kieu");
```

### Canceling a Timeout

```javascript
// Save the timeout ID
const timeoutId = setTimeout(() => {
    console.log("This might not run");
}, 5000);

// Cancel it before it runs
clearTimeout(timeoutId);
console.log("Timeout canceled");
```

**Real-world example:**

```html
<button id="start">Start Timer</button>
<button id="cancel">Cancel Timer</button>
```

```javascript
let timeoutId;

document.querySelector("#start").addEventListener("click", () => {
    timeoutId = setTimeout(() => {
        alert("Time's up!");
    }, 5000);
    console.log("Timer started (5 seconds)");
});

document.querySelector("#cancel").addEventListener("click", () => {
    clearTimeout(timeoutId);
    console.log("Timer canceled");
});
```

---

## setInterval() - Repeat Code

Execute code repeatedly at intervals.

### Basic Usage

```javascript
setInterval(() => {
    console.log("This runs every 2 seconds");
}, 2000);
```

### Canceling an Interval

```javascript
const intervalId = setInterval(() => {
    console.log("Tick");
}, 1000);

// Stop after 5 seconds
setTimeout(() => {
    clearInterval(intervalId);
    console.log("Interval stopped");
}, 5000);
```

### Counter Example

```html
<div id="counter">0</div>
<button id="start">Start</button>
<button id="stop">Stop</button>
<button id="reset">Reset</button>
```

```javascript
let count = 0;
let intervalId = null;

const counterDisplay = document.querySelector("#counter");
const startBtn = document.querySelector("#start");
const stopBtn = document.querySelector("#stop");
const resetBtn = document.querySelector("#reset");

startBtn.addEventListener("click", () => {
    if (intervalId) return; // Already running

    intervalId = setInterval(() => {
        count++;
        counterDisplay.textContent = count;
    }, 1000);
});

stopBtn.addEventListener("click", () => {
    clearInterval(intervalId);
    intervalId = null;
});

resetBtn.addEventListener("click", () => {
    clearInterval(intervalId);
    intervalId = null;
    count = 0;
    counterDisplay.textContent = count;
});
```

### Clock Example

```html
<div id="clock"></div>
```

```javascript
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, "0");
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");

    const timeString = `${hours}:${minutes}:${seconds}`;
    document.querySelector("#clock").textContent = timeString;
}

// Update immediately
updateClock();

// Update every second
setInterval(updateClock, 1000);
```

---

## Callbacks

A **callback** is a function passed to another function to be executed later.

### Synchronous Callbacks

```javascript
function processUser(name, callback) {
    console.log(`Processing ${name}...`);
    callback(name);
}

processUser("Kieu", (name) => {
    console.log(`Hello, ${name}!`);
});

// Output:
// Processing Kieu...
// Hello, Kieu!
```

### Asynchronous Callbacks

```javascript
function loadUser(userId, callback) {
    console.log(`Loading user ${userId}...`);

    // Simulate API call (2 seconds)
    setTimeout(() => {
        const user = { id: userId, name: "Kieu" };
        callback(user);
    }, 2000);
}

console.log("Start");

loadUser(1, (user) => {
    console.log("User loaded:", user);
});

console.log("End");

// Output:
// Start
// Loading user 1...
// End
// (2 seconds later)
// User loaded: { id: 1, name: "Kieu" }
```

### Error-First Callbacks (Node.js Pattern)

```javascript
function loadUser(userId, callback) {
    setTimeout(() => {
        // Simulate error
        if (userId < 0) {
            callback(new Error("Invalid user ID"), null);
            return;
        }

        // Success
        const user = { id: userId, name: "Kieu" };
        callback(null, user);
    }, 1000);
}

// Usage
loadUser(1, (error, user) => {
    if (error) {
        console.error("Error:", error.message);
        return;
    }

    console.log("User loaded:", user);
});
```

---

## Callback Hell (The Problem)

When you have many nested callbacks, code becomes hard to read:

```javascript
// Pyramid of Doom
getUserData(1, (error, user) => {
    if (error) {
        console.error(error);
        return;
    }

    getUserPosts(user.id, (error, posts) => {
        if (error) {
            console.error(error);
            return;
        }

        getPostComments(posts[0].id, (error, comments) => {
            if (error) {
                console.error(error);
                return;
            }

            getUserProfile(comments[0].userId, (error, profile) => {
                if (error) {
                    console.error(error);
                    return;
                }

                console.log("Finally done:", profile);
            });
        });
    });
});
```

**Problems:**
- Hard to read (nested too deep)
- Error handling repeated everywhere
- Hard to maintain

**Solution**: Promises and async/await (next section)

---

## Introduction to Promises

A **Promise** is an object representing the eventual completion (or failure) of an async operation.

### Promise States

A Promise is in one of three states:
1. **Pending**: Initial state, not fulfilled or rejected yet
2. **Fulfilled**: Operation completed successfully
3. **Rejected**: Operation failed

### Creating a Promise

```javascript
const promise = new Promise((resolve, reject) => {
    // Do async operation
    setTimeout(() => {
        const success = true;

        if (success) {
            resolve("Success!"); // Fulfilled
        } else {
            reject("Error!"); // Rejected
        }
    }, 2000);
});

console.log(promise); // Promise { <pending> }
```

### Using a Promise

```javascript
promise
    .then((result) => {
        console.log("Success:", result);
    })
    .catch((error) => {
        console.error("Error:", error);
    });
```

### Real Example: Load User

```javascript
function loadUser(userId) {
    return new Promise((resolve, reject) => {
        setTimeout(() => {
            if (userId < 0) {
                reject(new Error("Invalid user ID"));
                return;
            }

            const user = { id: userId, name: "Kieu" };
            resolve(user);
        }, 1000);
    });
}

// Usage
loadUser(1)
    .then((user) => {
        console.log("User loaded:", user);
    })
    .catch((error) => {
        console.error("Error:", error.message);
    });
```

### Chaining Promises

```javascript
loadUser(1)
    .then((user) => {
        console.log("User:", user);
        return loadUserPosts(user.id); // Return another promise
    })
    .then((posts) => {
        console.log("Posts:", posts);
        return loadPostComments(posts[0].id);
    })
    .then((comments) => {
        console.log("Comments:", comments);
    })
    .catch((error) => {
        console.error("Error anywhere:", error);
    });
```

**Much cleaner than callback hell!**

### Promise.all() - Wait for Multiple

```javascript
const promise1 = loadUser(1);
const promise2 = loadUser(2);
const promise3 = loadUser(3);

Promise.all([promise1, promise2, promise3])
    .then((users) => {
        console.log("All users loaded:", users);
    })
    .catch((error) => {
        console.error("At least one failed:", error);
    });
```

**Use case**: Load multiple independent resources at once.

### Promise.race() - Wait for First

```javascript
const promise1 = loadUser(1); // Takes 2 seconds
const promise2 = loadUser(2); // Takes 1 second

Promise.race([promise1, promise2])
    .then((user) => {
        console.log("First user loaded:", user); // Whichever finishes first
    })
    .catch((error) => {
        console.error("First to finish failed:", error);
    });
```

---

## async/await (Modern Syntax)

**async/await** is syntactic sugar over Promises. It makes async code look synchronous.

### Basic Syntax

```javascript
// Promise way
function getUser() {
    return loadUser(1)
        .then((user) => {
            console.log(user);
            return user;
        });
}

// async/await way (cleaner)
async function getUser() {
    const user = await loadUser(1);
    console.log(user);
    return user;
}
```

### Error Handling

```javascript
async function getUser() {
    try {
        const user = await loadUser(1);
        console.log("User:", user);
    } catch (error) {
        console.error("Error:", error.message);
    }
}
```

### Multiple Async Operations

```javascript
// Sequential (one after another)
async function loadData() {
    try {
        const user = await loadUser(1);
        console.log("User loaded:", user);

        const posts = await loadUserPosts(user.id);
        console.log("Posts loaded:", posts);

        const comments = await loadPostComments(posts[0].id);
        console.log("Comments loaded:", comments);
    } catch (error) {
        console.error("Error:", error);
    }
}

// Parallel (at the same time)
async function loadData() {
    try {
        const [user1, user2, user3] = await Promise.all([
            loadUser(1),
            loadUser(2),
            loadUser(3)
        ]);

        console.log("All users:", user1, user2, user3);
    } catch (error) {
        console.error("Error:", error);
    }
}
```

### async/await with Arrow Functions

```javascript
const getUser = async () => {
    const user = await loadUser(1);
    return user;
};

// Event handler
button.addEventListener("click", async () => {
    const user = await loadUser(1);
    console.log(user);
});
```

### Rules of async/await

1. **`await` only works inside `async` functions**
   ```javascript
   // Error!
   const user = await loadUser(1);

   // Good
   async function getUser() {
       const user = await loadUser(1);
   }
   ```

2. **`async` functions always return a Promise**
   ```javascript
   async function getValue() {
       return 42;
   }

   getValue().then((value) => {
       console.log(value); // 42
   });
   ```

3. **Can't use `await` at top level (yet)**
   ```javascript
   // Error in most browsers
   const user = await loadUser(1);

   // Good
   (async () => {
       const user = await loadUser(1);
   })();
   ```

---

## Practical Examples

### Example 1: Loading Indicator

```html
<button id="load-btn">Load Data</button>
<div id="loading" style="display: none;">Loading...</div>
<div id="result"></div>
```

```javascript
async function loadData() {
    const loadingDiv = document.querySelector("#loading");
    const resultDiv = document.querySelector("#result");

    try {
        // Show loading
        loadingDiv.style.display = "block";
        resultDiv.textContent = "";

        // Simulate API call
        await new Promise((resolve) => setTimeout(resolve, 2000));

        // Hide loading, show result
        loadingDiv.style.display = "none";
        resultDiv.textContent = "Data loaded successfully!";
    } catch (error) {
        loadingDiv.style.display = "none";
        resultDiv.textContent = `Error: ${error.message}`;
    }
}

document.querySelector("#load-btn").addEventListener("click", loadData);
```

### Example 2: Retry Logic

```javascript
async function fetchWithRetry(url, retries = 3) {
    for (let i = 0; i < retries; i++) {
        try {
            const response = await fetch(url);
            return await response.json();
        } catch (error) {
            console.log(`Attempt ${i + 1} failed`);

            if (i === retries - 1) {
                throw error; // Last attempt failed
            }

            // Wait before retry (exponential backoff)
            await new Promise((resolve) => setTimeout(resolve, 1000 * (i + 1)));
        }
    }
}
```

### Example 3: Timeout Promise

```javascript
function timeout(ms) {
    return new Promise((_, reject) => {
        setTimeout(() => {
            reject(new Error("Timeout"));
        }, ms);
    });
}

async function fetchWithTimeout(url, ms) {
    try {
        const result = await Promise.race([
            fetch(url),
            timeout(ms)
        ]);
        return result;
    } catch (error) {
        console.error("Request timed out or failed:", error);
    }
}

// Use it
fetchWithTimeout("https://api.example.com/data", 5000);
```

### Example 4: Sequential Animation

```javascript
function wait(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function animateElements() {
    const elements = document.querySelectorAll(".box");

    for (let element of elements) {
        element.classList.add("highlight");
        await wait(500); // Wait 500ms before next
    }

    console.log("All animations complete!");
}
```

### Example 5: Auto-Save

```javascript
let saveTimeout;

const input = document.querySelector("#content");

input.addEventListener("input", () => {
    // Clear previous timeout
    clearTimeout(saveTimeout);

    // Set new timeout (debounced auto-save)
    saveTimeout = setTimeout(async () => {
        console.log("Auto-saving...");
        await saveContent(input.value);
        console.log("Saved!");
    }, 2000); // Save 2 seconds after user stops typing
});

async function saveContent(content) {
    // Simulate API call
    return new Promise((resolve) => {
        setTimeout(() => {
            console.log("Content saved:", content);
            resolve();
        }, 500);
    });
}
```

---

## Common Mistakes

### Mistake 1: Forgetting await

```javascript
// Bad: Returns Promise, not user
async function getUser() {
    const user = loadUser(1); // Forgot await!
    console.log(user); // Promise { <pending> }
    return user;
}

// Good
async function getUser() {
    const user = await loadUser(1);
    console.log(user); // { id: 1, name: "Kieu" }
    return user;
}
```

### Mistake 2: Not Handling Errors

```javascript
// Bad: Errors are silently ignored
async function getUser() {
    const user = await loadUser(1);
    return user;
}

// Good
async function getUser() {
    try {
        const user = await loadUser(1);
        return user;
    } catch (error) {
        console.error("Failed to load user:", error);
        return null;
    }
}
```

### Mistake 3: Sequential When Parallel is Possible

```javascript
// Bad: Takes 6 seconds (2 + 2 + 2)
async function loadAllUsers() {
    const user1 = await loadUser(1); // 2 seconds
    const user2 = await loadUser(2); // 2 seconds
    const user3 = await loadUser(3); // 2 seconds
    return [user1, user2, user3];
}

// Good: Takes 2 seconds (all at once)
async function loadAllUsers() {
    const users = await Promise.all([
        loadUser(1),
        loadUser(2),
        loadUser(3)
    ]);
    return users;
}
```

### Mistake 4: Using await in Loops

```javascript
// Bad: Sequential (slow)
async function processUsers(userIds) {
    const users = [];
    for (let id of userIds) {
        const user = await loadUser(id); // Waits for each!
        users.push(user);
    }
    return users;
}

// Good: Parallel (fast)
async function processUsers(userIds) {
    const promises = userIds.map((id) => loadUser(id));
    const users = await Promise.all(promises);
    return users;
}
```

---

## Practice Exercises

### Exercise 1: Countdown Timer

Create a countdown from 10 to 0 using `setInterval`. When it reaches 0, show "Time's up!".

### Exercise 2: Delayed Messages

Create three messages that appear one after another with delays:
- "Loading..." (immediately)
- "Almost there..." (after 2 seconds)
- "Done!" (after 2 more seconds)

Use Promises or async/await.

### Exercise 3: Traffic Light

Create a traffic light simulation:
- Red (3 seconds)
- Yellow (1 second)
- Green (3 seconds)
- Repeat

Change the color of a div element.

### Exercise 4: Promise Chain

Create three functions that return Promises:
```javascript
function step1() { return Promise.resolve("Step 1 done"); }
function step2() { return Promise.resolve("Step 2 done"); }
function step3() { return Promise.resolve("Step 3 done"); }
```

Chain them and log each result.

### Exercise 5: Parallel Requests (Simulation)

Simulate loading 5 users (each takes random 1-3 seconds). Load them all at once and show total time.

---

## Quick Reference

### setTimeout/setInterval
```javascript
const id = setTimeout(fn, delay);
clearTimeout(id);

const id = setInterval(fn, delay);
clearInterval(id);
```

### Promises
```javascript
new Promise((resolve, reject) => {
    // async operation
    resolve(value);  // or
    reject(error);
});

promise
    .then(result => { })
    .catch(error => { })
    .finally(() => { });
```

### async/await
```javascript
async function fn() {
    try {
        const result = await promise;
        return result;
    } catch (error) {
        console.error(error);
    }
}
```

### Promise Utilities
```javascript
Promise.all([p1, p2, p3])     // Wait for all
Promise.race([p1, p2])        // Wait for first
Promise.resolve(value)        // Create resolved promise
Promise.reject(error)         // Create rejected promise
```

---

## Key Takeaways

1. **JavaScript is single-threaded** but can handle async operations
2. **setTimeout** delays execution, **setInterval** repeats execution
3. **Callbacks** are functions passed to be executed later
4. **Promises** are better than callbacks for async code
5. **async/await** makes async code look synchronous
6. **Always handle errors** in async code
7. **Use Promise.all()** for parallel operations
8. **await** only works inside **async** functions

---

## Next Lesson

In Lesson 09, we'll learn about:
- Fetch API for making HTTP requests
- Working with JSON
- GET and POST requests
- Handling API responses
- Error handling in API calls

Time to connect to real APIs!
