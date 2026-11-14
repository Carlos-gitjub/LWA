<?php

namespace App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms;

interface AnalyzeSubscriptionPlatformsOutputBoundary
{
    public function present(AnalyzeSubscriptionPlatformsOutputData $output): void;
}
