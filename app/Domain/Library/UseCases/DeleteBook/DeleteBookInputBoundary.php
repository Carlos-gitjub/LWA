<?php

namespace App\Domain\Library\UseCases\DeleteBook;

interface DeleteBookInputBoundary
{
    public function execute(DeleteBookInputData $input): void;
}
