<?php

namespace App\Domain\Movies\UseCases\Data;

final readonly class StreamingOfferData
{
    public function __construct(public string $platformId, public string $platform, public string $region,
        public string $type, public string $format, public ?string $price,
        public ?string $currency, public string $url) {}
}
