<?php

namespace App\Domain\Library\UseCases\DataAccessInterfaces;

use App\Domain\Library\UseCases\Data\PageMatchData;

interface BookContentSearch
{
    /** @param list<string> $authors @param list<int> $bookIds
     * @return list<PageMatchData>
     */
    public function search(string $query, array $authors, array $bookIds, int $limit): array;
}
