<?php

namespace App\Infrastructure\Library\Adapters;

use App\Domain\Library\UseCases\BookNotFound;
use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\LibraryUnavailable;
use App\Infrastructure\Library\Models\Book;
use Illuminate\Support\Facades\DB;

final class EloquentBookRepository implements BookRepository
{
    public function all(): array
    {
        return $this->run(fn () => Book::latest()->get()->map(fn ($book) => self::data($book))->all());
    }

    public function search(string $query): array
    {
        return $this->run(fn () => Book::where('title', 'like', "%{$query}%")->orWhere('author', 'like', "%{$query}%")
            ->get()->map(fn ($book) => self::data($book))->all());
    }

    public function find(int $id): BookData
    {
        return $this->run(function () use ($id) {
            $book = Book::find($id);
            if (! $book) {
                throw new BookNotFound('Book not found.');
            }

            return self::data($book);
        });
    }

    public function save(BookData $data, ?array $pages): BookData
    {
        return $this->run(fn () => DB::transaction(function () use ($data, $pages) {
            $book = $data->id === null ? new Book : Book::lockForUpdate()->find($data->id);
            if (! $book) {
                throw new BookNotFound('Book not found.');
            }
            $book->fill(['title' => $data->title, 'author' => $data->author, 'file_path' => $data->filePath, 'cover_path' => $data->coverPath]);
            $book->save();
            if ($pages !== null) {
                $book->pages()->delete();
                foreach ($pages as $page) {
                    $book->pages()->create(['page_number' => $page->number, 'content' => $page->content]);
                }
            }

            return self::data($book->fresh());
        }));
    }

    public function delete(int $id): void
    {
        $this->run(fn () => DB::transaction(function () use ($id) {
            $book = Book::lockForUpdate()->find($id);
            if (! $book) {
                throw new BookNotFound('Book not found.');
            }
            $book->pages()->delete();
            $book->delete();
        }));
    }

    public static function data(Book $book): BookData
    {
        return new BookData($book->id, $book->title, $book->author, $book->file_path, $book->cover_path,
            $book->created_at?->toJSON(), $book->updated_at?->toJSON());
    }

    private function run(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (\Illuminate\Database\QueryException $error) {
            throw new LibraryUnavailable('Book persistence unavailable.', 0, $error);
        }
    }
}
