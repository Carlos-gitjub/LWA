<?php

namespace App\Domain\Movies\Entities;

final readonly class Movie
{
    public function __construct(public string $id, public string $title, public string $year) {}
}
