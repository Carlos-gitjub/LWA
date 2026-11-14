<?php

namespace App\Domain\Library\UseCases\SearchBookContents;

final readonly class SearchBookContentsOutputData
{
    /** @param list<\App\Domain\Library\UseCases\Data\SearchResultData> $results */
    public function __construct(public string $query, public array $results, public int $totalMatches) {}
}
