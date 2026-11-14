<?php

namespace App\Domain\Library\UseCases\ListBooks;

final readonly class ListBooksOutputData
{
    /** @param list<\App\Domain\Library\UseCases\Data\BookData> $books */
    public function __construct(public array $books) {}
}
