<?php

namespace App\Domain\Library\UseCases\Data;

final readonly class PageMatchData
{
    public function __construct(public int $id, public BookData $book, public int $number, public string $content) {}
}
