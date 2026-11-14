<?php

namespace App\Domain\Library\UseCases\ListBooks;

interface ListBooksInputBoundary
{
    public function execute(ListBooksInputData $input): void;
}
