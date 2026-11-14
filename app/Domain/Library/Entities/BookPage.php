<?php

namespace App\Domain\Library\Entities;

final readonly class BookPage
{
    public function __construct(public int $number, public string $content)
    {
        if ($number < 1) {
            throw new \InvalidArgumentException('Page numbers must be positive.');
        }
    }

    public function occurrences(string $query): int
    {
        return $query === '' ? 0 : substr_count(mb_strtolower($this->content), mb_strtolower($query));
    }
}
