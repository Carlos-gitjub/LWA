<?php

namespace App\Domain\Library\UseCases\ListBooks;

use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;

final class ListBooksInteractor implements ListBooksInputBoundary
{
    public function __construct(private BookRepository $books, private ListBooksOutputBoundary $output) {}

    public function execute(ListBooksInputData $input): void
    {
        $this->output->present(new ListBooksOutputData($this->books->all()));
    }
}
