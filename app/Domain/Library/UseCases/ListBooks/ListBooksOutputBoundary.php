<?php

namespace App\Domain\Library\UseCases\ListBooks;

interface ListBooksOutputBoundary
{
    public function present(ListBooksOutputData $output): void;
}
