<?php

namespace App\Domain\Library\UseCases\Data;

final readonly class BookData
{
    public function __construct(
        public ?int $id, public string $title, public ?string $author,
        public ?string $filePath, public ?string $coverPath,
        public ?string $createdAt = null, public ?string $updatedAt = null,
    ) {}
}
