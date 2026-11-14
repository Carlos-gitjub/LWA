<?php

namespace App\Domain\Library\UseCases\UpdateBook;

final readonly class UpdateBookInputData
{
    public function __construct(public int $id, public string $title, public ?string $author, public ?string $pdfContents = null, public ?string $coverContents = null) {}
}
