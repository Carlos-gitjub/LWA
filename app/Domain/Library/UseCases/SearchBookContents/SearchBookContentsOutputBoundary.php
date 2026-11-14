<?php

namespace App\Domain\Library\UseCases\SearchBookContents;

interface SearchBookContentsOutputBoundary
{
    public function present(SearchBookContentsOutputData $output): void;
}
