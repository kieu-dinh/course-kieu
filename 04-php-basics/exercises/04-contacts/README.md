# Exercise 04 - Contact Manager

## Objective

Practice classes and OOP with a contact management system.

---

## Task

Create `contacts.php` with a Contact class and ContactManager class.

---

## Requirements

### Contact Class

Properties:
- id (int)
- name (string)
- email (string)
- phone (string, optional)

Methods:
- Constructor
- `getInfo(): string` - Returns formatted contact info

### ContactManager Class

Properties:
- contacts (array)
- nextId (int) - auto-increment ID

Methods:
- `add(string $name, string $email, ?string $phone = null): Contact`
- `remove(int $id): bool`
- `find(int $id): ?Contact`
- `findByEmail(string $email): ?Contact`
- `search(string $term): array` - Search by name or email
- `getAll(): array`
- `count(): int`

---

## Starter Code

```php
<?php

class Contact {
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $phone = null
    ) {}

    public function getInfo(): string {
        $info = "{$this->id}. {$this->name} <{$this->email}>";
        if ($this->phone) {
            $info .= " - {$this->phone}";
        }
        return $info;
    }
}

class ContactManager {
    private array $contacts = [];
    private int $nextId = 1;

    public function add(string $name, string $email, ?string $phone = null): Contact {
        // Create new Contact with auto-increment ID
        // Add to contacts array
        // Return the new contact
    }

    public function remove(int $id): bool {
        // Find and remove contact by ID
        // Return true if removed, false if not found
    }

    public function find(int $id): ?Contact {
        // Find contact by ID
    }

    public function findByEmail(string $email): ?Contact {
        // Find contact by email
    }

    public function search(string $term): array {
        // Search in name and email (case-insensitive)
        // Return array of matching contacts
    }

    public function getAll(): array {
        return $this->contacts;
    }

    public function count(): int {
        return count($this->contacts);
    }

    public function displayAll(): void {
        if (empty($this->contacts)) {
            echo "No contacts.\n";
            return;
        }

        echo "=== Contacts ({$this->count()}) ===\n";
        foreach ($this->contacts as $contact) {
            echo $contact->getInfo() . "\n";
        }
    }
}

// Test the system
$manager = new ContactManager();

echo "Adding contacts...\n";
$manager->add("Alice Smith", "alice@email.com", "123-456-7890");
$manager->add("Bob Johnson", "bob@email.com");
$manager->add("Charlie Brown", "charlie@email.com", "098-765-4321");

$manager->displayAll();

echo "\nSearching for 'ali'...\n";
$results = $manager->search("ali");
foreach ($results as $contact) {
    echo $contact->getInfo() . "\n";
}

echo "\nFinding by email 'bob@email.com'...\n";
$bob = $manager->findByEmail("bob@email.com");
if ($bob) {
    echo "Found: " . $bob->getInfo() . "\n";
}

echo "\nRemoving contact ID 2...\n";
$manager->remove(2);
$manager->displayAll();
```

---

## Expected Output

```
Adding contacts...
=== Contacts (3) ===
1. Alice Smith <alice@email.com> - 123-456-7890
2. Bob Johnson <bob@email.com>
3. Charlie Brown <charlie@email.com> - 098-765-4321

Searching for 'ali'...
1. Alice Smith <alice@email.com> - 123-456-7890

Finding by email 'bob@email.com'...
Found: 2. Bob Johnson <bob@email.com>

Removing contact ID 2...
=== Contacts (2) ===
1. Alice Smith <alice@email.com> - 123-456-7890
3. Charlie Brown <charlie@email.com> - 098-765-4321
```

---

## Bonus Challenges

1. Add `update(int $id, ?string $name, ?string $email, ?string $phone): bool`
2. Add email validation in `add()`
3. Add `exportToArray(): array` - Returns contacts as simple arrays

---

## Checklist

- [ ] Contact class works correctly
- [ ] Can add contacts with auto-increment ID
- [ ] Can remove contacts by ID
- [ ] Can find by ID and email
- [ ] Search works (case-insensitive)
- [ ] Display shows all info

---

## Congratulations!

You've completed Module 04! You now understand:
- Variables and types
- Conditions
- Loops
- Arrays
- Functions
- Classes and objects

**Next Module:** [05 - JavaScript & Alpine](../../05-javascript-alpine/)
