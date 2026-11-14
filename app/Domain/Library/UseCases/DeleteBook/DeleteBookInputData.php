<?php

namespace App\Domain\Library\UseCases\DeleteBook;

final readonly class DeleteBookInputData
{
    public function __construct(public int $id) {}
}
