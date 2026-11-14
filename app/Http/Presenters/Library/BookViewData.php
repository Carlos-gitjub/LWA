<?php

namespace App\Http\Presenters\Library;

use App\Domain\Library\UseCases\Data\BookData;

final class BookViewData
{
    public static function from(BookData $book): array
    {
        return ['id' => $book->id, 'title' => $book->title, 'author' => $book->author,
            'file_path' => $book->filePath, 'cover_path' => $book->coverPath,
            'created_at' => $book->createdAt, 'updated_at' => $book->updatedAt];
    }
}
