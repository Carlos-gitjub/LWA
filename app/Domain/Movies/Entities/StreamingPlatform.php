<?php

namespace App\Domain\Movies\Entities;

final readonly class StreamingPlatform
{
    public function __construct(public string $id, public string $name) {}
}
