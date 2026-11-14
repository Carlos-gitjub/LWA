<?php

namespace App\Domain\Library\UseCases\AddBook;

interface AddBookInputBoundary
{
    public function execute(AddBookInputData $input): void;
}
