<?php

// TODO: Create a Book class
// - Properties: $id, $title, $author, $isbn, $year, $available (all private or protected)
// - Create a constructor to initialize properties
// - Create getInfo() method that returns book information
// - Create markAsAvailable() method
// - Create markAsUnavailable() method
// - Create getter methods for id, title, author, isbn, year, available

class Book
{
    // TODO: Add properties


    // TODO: Create constructor
    public function __construct()
    {

    }

    // TODO: Create getInfo() method
    public function getInfo()
    {

    }

    // TODO: Create markAsAvailable() method
    public function markAsAvailable()
    {

    }

    // TODO: Create markAsUnavailable() method
    public function markAsUnavailable()
    {

    }

    // TODO: Create getter methods
    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getIsbn()
    {
        return $this->isbn;
    }

    public function getYear()
    {
        return $this->year;
    }

    public function isAvailable()
    {
        // TODO: Return availability status
        return $this->available;
    }
}
