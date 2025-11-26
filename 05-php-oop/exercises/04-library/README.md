# Exercise 5.4 - Library Management System

## Objective

Create a complete library management system using OOP principles with multiple interconnected classes.

## Duration

2-2.5 hours

## Task

Build a library system with books, members, and library management functionality.

## Requirements

- [ ] Create a `Book.php` class with properties: `id`, `title`, `author`, `isbn`, `year`, `available`
- [ ] Book class methods: `getInfo()`, `markAsAvailable()`, `markAsUnavailable()`
- [ ] Create a `Member.php` class with properties: `id`, `name`, `email`, `joinDate`, `borrowedBooks` (array)
- [ ] Member class methods: `borrowBook()`, `returnBook()`, `getBorrowedBooks()`, `getInfo()`
- [ ] Create a `Library.php` class to manage the system
- [ ] Library properties: `books` (array), `members` (array), `name`
- [ ] Library methods: `addBook()`, `addMember()`, `findBook()`, `findMember()`, `lendBook()`, `returnBook()`, `displayBooks()`, `displayMembers()`
- [ ] Prevent lending same book twice (check availability)
- [ ] Track which member borrowed which book

## Starter Files

Work in `Book.php`, `Member.php`, and `Library.php` - see starter code there.

## Expected Output

```
php Library.php

=== City Library ===

Books in library:
1. The Great Gatsby by F. Scott Fitzgerald (Available)
2. 1984 by George Orwell (Available)

Members:
1. Alice Johnson (alice@example.com)
2. Bob Smith (bob@example.com)

Lending "The Great Gatsby" to Alice:
Success! Book lent to Alice.

Books after lending:
1. The Great Gatsby by F. Scott Fitzgerald (Not Available)
2. 1984 by George Orwell (Available)

Alice's books:
- The Great Gatsby
```

## Checklist

- [ ] Book class created with all properties
- [ ] Member class created with all properties
- [ ] Library class created with book and member management
- [ ] addBook() adds books to library
- [ ] addMember() adds members to library
- [ ] findBook() finds books by title or ISBN
- [ ] findMember() finds members by name or ID
- [ ] lendBook() prevents lending unavailable books
- [ ] returnBook() properly updates availability
- [ ] All methods implemented and working
- [ ] Proper error handling for invalid operations

## Tips

- Use associative arrays to store books and members
- Book availability should be managed through class methods
- Each member should maintain a list of borrowed books
- When lending a book, update both the book and member status
- Use foreach loops to display all books and members
