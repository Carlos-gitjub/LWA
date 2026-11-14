<?php

namespace App\Domain\Library\UseCases\Data;

final readonly class SearchResultData
{
    public function __construct(public PageMatchData $page, public int $count) {}
}
