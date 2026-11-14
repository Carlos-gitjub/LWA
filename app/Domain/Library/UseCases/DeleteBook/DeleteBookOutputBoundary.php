<?php

namespace App\Domain\Library\UseCases\DeleteBook;

interface DeleteBookOutputBoundary
{
    public function present(DeleteBookOutputData $output): void;
}
