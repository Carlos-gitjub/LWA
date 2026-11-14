<?php

namespace App\Domain\Library\UseCases\Data;

final readonly class PageData
{
    public function __construct(public int $number, public string $content) {}
}
