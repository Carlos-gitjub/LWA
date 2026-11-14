<?php

namespace App\Http\Presenters\Movies;

final readonly class ViewModel
{
    public function __construct(public ?array $data) {}
}
