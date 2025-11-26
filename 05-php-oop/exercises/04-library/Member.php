<?php

// TODO: Create a Member class
// - Properties: $id, $name, $email, $joinDate, $borrowedBooks (array, private or protected)
// - Create a constructor to initialize properties
// - Create borrowBook(Book $book) method - adds book to borrowed list
// - Create returnBook(Book $book) method - removes book from borrowed list
// - Create getBorrowedBooks() method - returns array of borrowed books
// - Create getInfo() method
// - Create getter methods for id, name, email, joinDate

class Member
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Create borrowBook() method
    public function borrowBook($book)
    {

    }

    // TODO: Create returnBook() method
    public function returnBook($book)
    {

    }

    // TODO: Create getBorrowedBooks() method
    public function getBorrowedBooks()
    {

    }

    // TODO: Create getInfo() method
    public function getInfo()
    {

    }

    // TODO: Create getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getJoinDate()
    {
        return $this->joinDate;
    }
}
