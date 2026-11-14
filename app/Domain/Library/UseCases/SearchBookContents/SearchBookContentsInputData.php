<?php

namespace App\Domain\Library\UseCases\SearchBookContents;

final readonly class SearchBookContentsInputData
{
    /** @param list<string> $authors @param list<int> $bookIds */
    public function __construct(public string $query, public array $authors = [], public array $bookIds = []) {}
}
