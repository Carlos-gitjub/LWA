<?php

namespace App\Domain\Library\UseCases\UpdateBook;

final readonly class UpdateBookOutputData
{
    public function __construct(public \App\Domain\Library\UseCases\Data\BookData $book) {}
}
