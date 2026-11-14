<?php

namespace App\Domain\Movies\UseCases\AnalyzeSubscriptionPlatforms;

interface AnalyzeSubscriptionPlatformsInputBoundary
{
    public function execute(AnalyzeSubscriptionPlatformsInputData $input): void;
}
