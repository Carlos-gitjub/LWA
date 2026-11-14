<?php

namespace App\Domain\Movies\UseCases\FindStreamingOffersForTitle;

interface FindStreamingOffersForTitleInputBoundary
{
    public function execute(FindStreamingOffersForTitleInputData $input): void;
}
