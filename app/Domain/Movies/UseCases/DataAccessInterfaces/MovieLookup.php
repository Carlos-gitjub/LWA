<?php

namespace App\Domain\Movies\UseCases\DataAccessInterfaces;

use App\Domain\Movies\UseCases\Data\MovieData;

interface MovieLookup
{
    public function findFirstByTitle(string $title): ?MovieData;
}
