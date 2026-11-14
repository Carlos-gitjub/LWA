<?php

namespace App\Domain\Library\UseCases\SearchBooks;

final readonly class SearchBooksOutputData
{
    /** @param list<\App\Domain\Library\UseCases\Data\BookData> $books */
    public function __construct(public array $books) {}
}
