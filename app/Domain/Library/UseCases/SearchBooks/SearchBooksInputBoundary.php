<?php

namespace App\Domain\Library\UseCases\SearchBooks;

interface SearchBooksInputBoundary
{
    public function execute(SearchBooksInputData $input): void;
}
