<?php

namespace App\Domain\Library\Entities;

final readonly class Book
{
    public string $title;

    public ?string $author;

    public function __construct(string $title, ?string $author)
    {
        $title = trim($title);
        $author = trim($author ?? '');
        if ($title === '' || mb_strlen($title) > 255 || mb_strlen($author) > 255) {
            throw new \InvalidArgumentException('A title is required; title and author must not exceed 255 characters.');
        }
        $this->title = $title;
        $this->author = $author === '' || strtolower($author) === 'null' ? null : $author;
    }
}
