<?php

namespace App\Domain\Library\UseCases\SearchBooks;

use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;

final class SearchBooksInteractor implements SearchBooksInputBoundary
{
    public function __construct(private BookRepository $books, private SearchBooksOutputBoundary $output) {}

    public function execute(SearchBooksInputData $input): void
    {
        $this->output->present(new SearchBooksOutputData($this->books->search($input->query)));
    }
}
