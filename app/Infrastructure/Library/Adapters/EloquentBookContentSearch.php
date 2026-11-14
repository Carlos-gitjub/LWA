<?php

namespace App\Infrastructure\Library\Adapters;

use App\Domain\Library\UseCases\Data\PageMatchData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookContentSearch;
use App\Domain\Library\UseCases\LibraryUnavailable;
use App\Infrastructure\Library\Models\BookPage;

final class EloquentBookContentSearch implements BookContentSearch
{
    public function search(string $query, array $authors, array $bookIds, int $limit): array
    {
        try {
            return BookPage::with('book')
                ->when($query !== '', fn ($builder) => $builder->where('content', 'like', '%'.$query.'%'))
                ->when($authors !== [], fn ($builder) => $builder->whereHas('book', fn ($books) => $books->whereIn('author', $authors)))
                ->when($bookIds !== [], fn ($builder) => $builder->whereIn('book_id', $bookIds))
                ->limit($limit)->get()->map(fn ($page) => new PageMatchData(
                    $page->id, EloquentBookRepository::data($page->book), $page->page_number,
                    mb_convert_encoding($page->content, 'UTF-8', 'UTF-8'),
                ))->all();
        } catch (\Illuminate\Database\QueryException $error) {
            throw new LibraryUnavailable('Book search unavailable.', 0, $error);
        }
    }
}
