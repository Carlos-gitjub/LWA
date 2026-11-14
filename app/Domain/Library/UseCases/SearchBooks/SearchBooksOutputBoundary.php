<?php

namespace App\Domain\Library\UseCases\SearchBooks;

interface SearchBooksOutputBoundary
{
    public function present(SearchBooksOutputData $output): void;
}
