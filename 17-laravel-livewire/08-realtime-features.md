# Lesson 8: Real-Time Features

**Duration**: 60 minutes
**Prerequisites**: Lesson 7 completed

---

## What You'll Learn

- Polling (auto-refresh)
- Event broadcasting
- Component events
- Listening for browser events
- Real-time notifications
- Live chat basics
- Presence (who's online)

---

## Polling

Automatically refresh component at intervals.

### Basic Polling

```html
<div wire:poll>
    Current time: {{ now() }}
</div>
```

Refreshes every 2 seconds (default).

### Custom Interval

```html
<!-- Poll every 5 seconds -->
<div wire:poll.5s>
    New messages: {{ $unreadCount }}
</div>

<!-- Poll every 30 seconds -->
<div wire:poll.30s>
    Active users: {{ $activeUsers }}
</div>

<!-- Poll every 2 minutes -->
<div wire:poll.120s>
    Stats: {{ $stats }}
</div>
```

### Poll Specific Method

```html
<div wire:poll.5s="refreshStats">
    Orders: {{ $orders }}
    Revenue: ${{ $revenue }}
</div>
```

```php
class Dashboard extends Component
{
    public $orders;
    public $revenue;

    public function mount()
    {
        $this->refreshStats();
    }

    public function refreshStats()
    {
        $this->orders = Order::count();
        $this->revenue = Order::sum('total');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
```

### Conditional Polling

Only poll when certain condition is true:

```html
<div wire:poll.5s="checkOrderStatus">
    @if($order->status === 'processing')
        <div class="flex items-center gap-2">
            <div class="animate-spin">⏳</div>
            <span>Processing your order...</span>
        </div>
    @else
        <div class="text-green-600">✓ Order {{ $order->status }}</div>
    @endif
</div>
```

```php
class OrderStatus extends Component
{
    public Order $order;

    public function checkOrderStatus()
    {
        $this->order->refresh();

        // Stop polling when done
        if ($this->order->status !== 'processing') {
            $this->dispatch('order-completed');
        }
    }
}
```

### Stop Polling with Alpine

```html
<div
    x-data="{ polling: true }"
    x-init="
        $wire.on('order-completed', () => {
            polling = false
        })
    "
>
    <div x-show="polling" wire:poll.5s="checkOrderStatus">
        Processing...
    </div>
    <div x-show="!polling">
        Completed!
    </div>
</div>
```

---

## Component Events

Components can communicate via events.

### Dispatching Events

```php
class CreatePost extends Component
{
    public $title = '';

    public function save()
    {
        $post = Post::create(['title' => $this->title]);

        // Dispatch event
        $this->dispatch('post-created', postId: $post->id);

        $this->reset('title');
    }
}
```

### Listening to Events

```php
use Livewire\Attributes\On;

class PostList extends Component
{
    #[On('post-created')]
    public function handlePostCreated($postId)
    {
        // Refresh the component
        // render() will be called automatically
    }

    public function render()
    {
        return view('livewire.post-list', [
            'posts' => Post::latest()->get()
        ]);
    }
}
```

### Dispatch to Specific Component

```php
// To all PostList components
$this->dispatch('post-created')->to(PostList::class);

// To self only
$this->dispatch('refresh')->self();

// To all except self
$this->dispatch('refresh')->except($this);
```

### Real-World Example: Shopping Cart

**AddToCart Component:**
```php
class AddToCart extends Component
{
    public Product $product;

    public function addToCart()
    {
        Cart::add($this->product);

        $this->dispatch('cart-updated');

        session()->flash('message', 'Added to cart!');
    }
}
```

**CartIcon Component:**
```php
class CartIcon extends Component
{
    #[On('cart-updated')]
    public function refreshCart()
    {
        // Component will re-render with updated count
    }

    public function render()
    {
        return view('livewire.cart-icon', [
            'itemCount' => Cart::count()
        ]);
    }
}
```

**CartSidebar Component:**
```php
class CartSidebar extends Component
{
    #[On('cart-updated')]
    public function refreshCart()
    {
        // Component will re-render with updated items
    }

    public function render()
    {
        return view('livewire.cart-sidebar', [
            'items' => Cart::items()
        ]);
    }
}
```

Now when you add to cart, **all three components update automatically!**

---

## Browser Events

Listen to browser events from JavaScript.

### Dispatch Browser Event from Livewire

```php
public function save()
{
    Post::create([...]);

    // JavaScript can listen to this
    $this->dispatch('post-saved')
        ->toJs();
}
```

### Listen in Alpine

```html
<div
    x-data="{ notification: '' }"
    @post-saved.window="notification = 'Post saved!'; setTimeout(() => notification = '', 3000)"
>
    <div x-show="notification" x-text="notification" class="alert"></div>

    <livewire:create-post />
</div>
```

### Listen in Vanilla JavaScript

```html
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('post-saved', (event) => {
        alert('Post saved!');
        // Or show toast notification
        // Or update external widget
    });
});
</script>
```

---

## Real-Time Notifications

### Toast Notifications

**Component:**
```php
public function save()
{
    Post::create([...]);

    $this->dispatch('notify', [
        'type' => 'success',
        'message' => 'Post created successfully!'
    ])->toJs();
}
```

**Layout (with Alpine):**
```html
<div
    x-data="{
        notifications: [],
        notify(event) {
            let id = Date.now();
            this.notifications.push({
                id: id,
                type: event.detail.type,
                message: event.detail.message
            });

            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 3000);
        }
    }"
    @notify.window="notify($event)"
>
    <!-- Notifications Container -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
        <template x-for="notification in notifications" :key="notification.id">
            <div
                x-show="true"
                x-transition
                class="px-4 py-3 rounded shadow-lg"
                :class="{
                    'bg-green-500 text-white': notification.type === 'success',
                    'bg-red-500 text-white': notification.type === 'error',
                    'bg-blue-500 text-white': notification.type === 'info'
                }"
                x-text="notification.message"
            ></div>
        </template>
    </div>

    <!-- Your app content -->
    {{ $slot }}
</div>
```

Now any component can show notifications:

```php
// Success
$this->dispatch('notify', [
    'type' => 'success',
    'message' => 'Changes saved!'
])->toJs();

// Error
$this->dispatch('notify', [
    'type' => 'error',
    'message' => 'Something went wrong!'
])->toJs();

// Info
$this->dispatch('notify', [
    'type' => 'info',
    'message' => 'New message received'
])->toJs();
```

---

## Laravel Echo & Broadcasting

For true real-time features (multiple users, different devices).

### 1. Setup Pusher (Easiest)

```bash
composer require pusher/pusher-php-server
```

**.env:**
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your-app-id
PUSHER_APP_KEY=your-app-key
PUSHER_APP_SECRET=your-app-secret
PUSHER_APP_CLUSTER=mt1
```

### 2. Create Event

```bash
php artisan make:event OrderShipped
```

```php
<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Order;

class OrderShipped implements ShouldBroadcast
{
    public function __construct(public Order $order)
    {
    }

    public function broadcastOn()
    {
        return new Channel('orders');
    }
}
```

### 3. Broadcast Event

```php
use App\Events\OrderShipped;

public function shipOrder($orderId)
{
    $order = Order::findOrFail($orderId);
    $order->update(['status' => 'shipped']);

    // Broadcast to all connected clients
    broadcast(new OrderShipped($order));
}
```

### 4. Listen in Livewire

```php
use Livewire\Attributes\On;

class OrderList extends Component
{
    protected $listeners = ['echo:orders,OrderShipped' => 'orderShipped'];

    public function orderShipped($event)
    {
        // Order was shipped, refresh list
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => "Order #{$event['order']['id']} was shipped!"
        ])->toJs();
    }

    public function render()
    {
        return view('livewire.order-list', [
            'orders' => Order::latest()->get()
        ]);
    }
}
```

**Now when ANY user ships an order, ALL users see the update in real-time!**

---

## Presence Channels

See who's currently online.

### 1. Create Presence Channel

```php
// routes/channels.php
Broadcast::channel('chat.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});
```

### 2. Component

```php
use Livewire\Attributes\On;

class ChatRoom extends Component
{
    public $roomId;
    public $onlineUsers = [];

    public function getListeners()
    {
        return [
            "echo-presence:chat.{$this->roomId},here" => 'usersHere',
            "echo-presence:chat.{$this->roomId},joining" => 'userJoining',
            "echo-presence:chat.{$this->roomId},leaving" => 'userLeaving',
        ];
    }

    public function usersHere($users)
    {
        $this->onlineUsers = $users;
    }

    public function userJoining($user)
    {
        $this->onlineUsers[] = $user;
    }

    public function userLeaving($user)
    {
        $this->onlineUsers = array_filter($this->onlineUsers, function($u) use ($user) {
            return $u['id'] !== $user['id'];
        });
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
```

### 3. View

```html
<div>
    <div class="mb-4">
        <h3 class="font-bold mb-2">Online ({{ count($onlineUsers) }})</h3>
        <div class="space-y-1">
            @foreach($onlineUsers as $user)
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span>{{ $user['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Chat messages... -->
</div>
```

---

## Live Chat Example

Complete live chat implementation:

### Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;
use Livewire\Attributes\On;

class ChatRoom extends Component
{
    public $roomId;
    public $messageText = '';

    protected $listeners = ['echo:chat,MessageSent' => 'messageReceived'];

    public function sendMessage()
    {
        $this->validate([
            'messageText' => 'required|min:1|max:500'
        ]);

        $message = Message::create([
            'room_id' => $this->roomId,
            'user_id' => auth()->id(),
            'text' => $this->messageText
        ]);

        // Broadcast to all users in this chat
        broadcast(new \App\Events\MessageSent($message))->toOthers();

        $this->reset('messageText');
    }

    public function messageReceived($event)
    {
        // Message received from another user
        // Component will re-render
    }

    public function render()
    {
        return view('livewire.chat-room', [
            'messages' => Message::where('room_id', $this->roomId)
                ->with('user')
                ->latest()
                ->take(50)
                ->get()
                ->reverse()
        ]);
    }
}
```

### View

```html
<div class="flex flex-col h-screen">
    <!-- Messages -->
    <div class="flex-1 overflow-y-auto p-4 space-y-4">
        @foreach($messages as $message)
            <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs">
                    <div class="text-xs text-gray-500 mb-1">
                        {{ $message->user->name }}
                    </div>
                    <div class="px-4 py-2 rounded-lg {{ $message->user_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                        {{ $message->text }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Input -->
    <div class="border-t p-4">
        <form wire:submit="sendMessage" class="flex gap-2">
            <input
                type="text"
                wire:model="messageText"
                placeholder="Type a message..."
                class="flex-1 px-4 py-2 border rounded"
                autofocus
            >
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="bg-blue-500 text-white px-6 py-2 rounded"
            >
                Send
            </button>
        </form>
    </div>
</div>
```

---

## Typing Indicator

Show "User is typing..." in chat:

### Component

```php
class ChatRoom extends Component
{
    public $typingUsers = [];

    protected $listeners = [
        'echo:chat,MessageSent' => 'messageReceived',
        'echo:chat,UserTyping' => 'userTyping',
        'echo:chat,UserStoppedTyping' => 'userStoppedTyping',
    ];

    public function typing()
    {
        broadcast(new UserTyping(auth()->user()))->toOthers();
    }

    public function userTyping($event)
    {
        if (!in_array($event['user']['id'], $this->typingUsers)) {
            $this->typingUsers[] = $event['user'];
        }
    }

    public function userStoppedTyping($event)
    {
        $this->typingUsers = array_filter($this->typingUsers, function($user) use ($event) {
            return $user['id'] !== $event['user']['id'];
        });
    }
}
```

### View

```html
<input
    type="text"
    wire:model="messageText"
    wire:keydown="typing"
    placeholder="Type a message..."
>

@if(count($typingUsers) > 0)
    <div class="text-sm text-gray-500 italic">
        {{ implode(', ', array_column($typingUsers, 'name')) }}
        {{ count($typingUsers) === 1 ? 'is' : 'are' }} typing...
    </div>
@endif
```

---

## Polling vs Broadcasting

### When to Use Polling

✅ **Use polling when:**
- Simple real-time needs (not critical)
- Single user experience
- Low frequency updates (every 30s+)
- Small applications
- Don't want to set up broadcasting

**Examples:**
- Dashboard stats
- Order status check
- Weather updates

### When to Use Broadcasting

✅ **Use broadcasting when:**
- Multiple users need updates
- High frequency updates
- Critical real-time features
- Presence (who's online)
- Large applications

**Examples:**
- Chat applications
- Notifications
- Live feeds
- Collaborative editing
- Real-time dashboards

### Comparison

| Feature | Polling | Broadcasting |
|---------|---------|-------------|
| Setup | Easy | Complex |
| Server load | Higher | Lower |
| Real-time | Delayed | Instant |
| Multi-user | No | Yes |
| Scalability | Limited | Excellent |

---

## Quick Quiz

**Question 1**: How do you poll every 10 seconds?

<details>
<summary>Show Answer</summary>

```html
<div wire:poll.10s>
    Content...
</div>
```

</details>

**Question 2**: How do you listen to an event in a component?

<details>
<summary>Show Answer</summary>

```php
use Livewire\Attributes\On;

#[On('event-name')]
public function handleEvent($data)
{
    // Handle event
}
```

</details>

**Question 3**: What's the difference between `dispatch()` and `dispatch()->toJs()`?

<details>
<summary>Show Answer</summary>

- `dispatch()`: Sends event to other Livewire components
- `dispatch()->toJs()`: Sends event to JavaScript/Alpine (browser event)

</details>

---

## Practice Exercise

Create a **Real-Time Notification System**:

**Requirements:**
1. Bell icon showing unread count
2. Dropdown showing recent notifications
3. Mark as read functionality
4. Poll for new notifications every 30s
5. Show toast when new notification arrives

Try it yourself!

---

## Summary

You mastered:

- ✅ Polling for auto-refresh
- ✅ Component events for communication
- ✅ Browser events with JavaScript
- ✅ Toast notifications
- ✅ Laravel Echo & Broadcasting
- ✅ Presence channels (who's online)
- ✅ Live chat implementation
- ✅ Polling vs Broadcasting comparison

**Next Lesson**: Livewire + Alpine Integration - combine their powers!

---

**Real-time features make your app feel alive! You now know how to build them!**
