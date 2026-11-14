<?php

namespace App\Domain\Library\UseCases\AddBook;

final readonly class AddBookInputData
{
    public function __construct(public string $title, public ?string $author, public string $pdfContents, public ?string $coverContents = null) {}
}
