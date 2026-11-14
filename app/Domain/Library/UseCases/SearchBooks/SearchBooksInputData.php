<?php

namespace App\Domain\Library\UseCases\SearchBooks;

final readonly class SearchBooksInputData
{
    public function __construct(public string $query) {}
}
