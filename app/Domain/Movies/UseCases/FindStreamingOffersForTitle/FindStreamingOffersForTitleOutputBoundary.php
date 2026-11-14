<?php

namespace App\Domain\Movies\UseCases\FindStreamingOffersForTitle;

interface FindStreamingOffersForTitleOutputBoundary
{
    public function present(FindStreamingOffersForTitleOutputData $output): void;
}
