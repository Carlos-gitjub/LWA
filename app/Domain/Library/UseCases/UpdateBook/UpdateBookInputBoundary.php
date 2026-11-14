<?php

namespace App\Domain\Library\UseCases\UpdateBook;

interface UpdateBookInputBoundary
{
    public function execute(UpdateBookInputData $input): void;
}
