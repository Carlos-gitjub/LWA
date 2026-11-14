<?php

namespace App\Domain\Library\UseCases\AddBook;

interface AddBookOutputBoundary
{
    public function present(AddBookOutputData $output): void;
}
