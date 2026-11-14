<?php

namespace App\Domain\Movies\UseCases\DataAccessInterfaces;

use App\Domain\Movies\UseCases\Data\StreamingOfferData;

interface StreamingOfferLookup
{
    /** @return list<StreamingOfferData> */
    public function forMovie(string $movieId, string $region): array;
}
