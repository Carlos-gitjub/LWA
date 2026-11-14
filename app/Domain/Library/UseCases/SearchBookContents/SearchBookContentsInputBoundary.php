<?php

namespace App\Domain\Library\UseCases\SearchBookContents;

interface SearchBookContentsInputBoundary
{
    public function execute(SearchBookContentsInputData $input): void;
}
