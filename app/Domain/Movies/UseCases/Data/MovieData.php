<?php

namespace App\Domain\Movies\UseCases\Data;

final readonly class MovieData
{
    public function __construct(public string $id, public string $title, public string $year) {}
}
