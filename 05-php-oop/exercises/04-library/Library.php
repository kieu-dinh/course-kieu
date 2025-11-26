<?php

require_once 'Book.php';
require_once 'Member.php';

// TODO: Create a Library class
// - Properties: $name, $books (array), $members (array) - all private or protected
// - Create a constructor to initialize library name
// - Create addBook(Book $book) method
// - Create addMember(Member $member) method
// - Create findBook($title) method - returns Book or null
// - Create findMember($name) method - returns Member or null
// - Create lendBook($memberName, $bookTitle) method
// - Create returnBook($memberName, $bookTitle) method
// - Create displayBooks() method - shows all books
// - Create displayMembers() method - shows all members

class Library
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Create addBook() method
    public function addBook($book)
    {

    }

    // TODO: Create addMember() method
    public function addMember($member)
    {

    }

    // TODO: Create findBook() method
    public function findBook($title)
    {

    }

    // TODO: Create findMember() method
    public function findMember($name)
    {

    }

    // TODO: Create lendBook() method
    public function lendBook($memberName, $bookTitle)
    {

    }

    // TODO: Create returnBook() method
    public function returnBook($memberName, $bookTitle)
    {

    }

    // TODO: Create displayBooks() method
    public function displayBooks()
    {

    }

    // TODO: Create displayMembers() method
    public function displayMembers()
    {

    }
}


// Test your classes (don't modify this part)
echo "=== City Library ===\n\n";

// Create books
$book1 = new Book(1, "The Great Gatsby", "F. Scott Fitzgerald", "978-0743273565", 1925);
$book2 = new Book(2, "1984", "George Orwell", "978-0451524935", 1949);
$book3 = new Book(3, "To Kill a Mockingbird", "Harper Lee", "978-0061120084", 1960);

// Create members
$member1 = new Member(1, "Alice Johnson", "alice@example.com", "2023-01-15");
$member2 = new Member(2, "Bob Smith", "bob@example.com", "2023-03-20");

// Create library
$library = new Library("City Library");

// Add books and members
$library->addBook($book1);
$library->addBook($book2);
$library->addBook($book3);

$library->addMember($member1);
$library->addMember($member2);

echo "Books in library:\n";
$library->displayBooks();

echo "\nMembers:\n";
$library->displayMembers();

echo "\nLending \"The Great Gatsby\" to Alice:\n";
$library->lendBook("Alice Johnson", "The Great Gatsby");
echo "Success! Book lent to Alice.\n\n";

echo "Books after lending:\n";
$library->displayBooks();

echo "\nAlice's books:\n";
$aliceMember = $library->findMember("Alice Johnson");
$aliceBooks = $aliceMember->getBorrowedBooks();
foreach ($aliceBooks as $book) {
    echo "- " . $book->getTitle() . "\n";
}
