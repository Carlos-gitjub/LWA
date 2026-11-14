<?php

namespace App\Domain\Library\UseCases\UpdateBook;

interface UpdateBookOutputBoundary
{
    public function present(UpdateBookOutputData $output): void;
}
