<?php

namespace Tests\Unit\Library;

use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\Data\PageMatchData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookContentSearch;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInputData;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsInteractor;
use App\Domain\Library\UseCases\SearchBookContents\SearchBookContentsOutputBoundary;
use PHPUnit\Framework\TestCase;

final class SearchBookContentsTest extends TestCase
{
    public function test_counts_matches_once_per_book_page_and_passes_filters_and_limit(): void
    {
        $book = new BookData(1, 'Book', 'Author', null, null);
        $page = new PageMatchData(10, $book, 2, 'Hello HELLO');
        $search = $this->createMock(BookContentSearch::class);
        $search->expects(self::once())->method('search')->with('hello', ['Author'], [1], 10000)->willReturn([$page, $page]);
        $output = $this->createMock(SearchBookContentsOutputBoundary::class);
        $output->expects(self::once())->method('present')->with(self::callback(
            fn ($data) => $data->totalMatches === 2 && count($data->results) === 1 && $data->results[0]->page === $page
        ));
        (new SearchBookContentsInteractor($search, $output))->execute(new SearchBookContentsInputData('hello', ['Author'], [1]));
    }
}
