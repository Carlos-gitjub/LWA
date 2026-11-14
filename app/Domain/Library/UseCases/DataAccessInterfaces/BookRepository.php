<?php

namespace App\Domain\Library\UseCases\DataAccessInterfaces;

use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\Data\PageData;

interface BookRepository
{
    /** @return list<BookData> */
    public function all(): array;

    /** @return list<BookData> */
    public function search(string $query): array;

    public function find(int $id): BookData;

    /** Atomically save the book and, if supplied, replace its pages.
     * @param  list<PageData>|null  $pages  null preserves existing pages.
     */
    public function save(BookData $book, ?array $pages): BookData;

    /** Atomically delete the book and its indexed pages. */
    public function delete(int $id): void;
}
