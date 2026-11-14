<?php

namespace App\Domain\Library\UseCases\DeleteBook;

final readonly class DeleteBookOutputData
{
    public function __construct(public int $id) {}
}
