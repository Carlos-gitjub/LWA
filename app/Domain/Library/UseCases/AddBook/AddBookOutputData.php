<?php

namespace App\Domain\Library\UseCases\AddBook;

final readonly class AddBookOutputData
{
    public function __construct(public \App\Domain\Library\UseCases\Data\BookData $book) {}
}
