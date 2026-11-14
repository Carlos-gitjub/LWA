<?php

namespace App\Domain\Library\UseCases\SearchBookContents;

use App\Domain\Library\Entities\BookPage;
use App\Domain\Library\UseCases\Data\SearchResultData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookContentSearch;

final class SearchBookContentsInteractor implements SearchBookContentsInputBoundary
{
    public function __construct(private BookContentSearch $pages, private SearchBookContentsOutputBoundary $output) {}

    public function execute(SearchBookContentsInputData $input): void
    {
        foreach ($input->authors as $author) {
            if (! is_string($author)) {
                throw new \InvalidArgumentException('Authors must be names.');
            }
        }
        foreach ($input->bookIds as $id) {
            if (! is_int($id) || $id < 1) {
                throw new \InvalidArgumentException('Book references must be positive integers.');
            }
        }
        $results = [];
        $total = 0;
        $seen = [];
        foreach ($this->pages->search($input->query, $input->authors, $input->bookIds, 10000) as $match) {
            $key = $match->book->id.':'.$match->number;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $count = (new BookPage($match->number, $match->content))->occurrences($input->query);
            $total += $count;
            $results[] = new SearchResultData($match, $count);
        }
        $this->output->present(new SearchBookContentsOutputData($input->query, $results, $total));
    }
}
