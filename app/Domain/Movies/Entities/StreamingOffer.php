<?php

namespace App\Domain\Movies\Entities;

final readonly class StreamingOffer
{
    public function __construct(
        public string $movieId,
        public StreamingPlatform $platform,
        public string $region,
        public string $type,
        public string $format,
        public ?string $price,
        public ?string $currency,
        public string $url,
    ) {}

    public function isSubscription(): bool
    {
        return $this->type === 'sub';
    }
}
