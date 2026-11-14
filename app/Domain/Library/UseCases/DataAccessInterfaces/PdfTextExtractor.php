<?php

namespace App\Domain\Library\UseCases\DataAccessInterfaces;

use App\Domain\Library\UseCases\Data\PageData;

interface PdfTextExtractor
{
    /** @return list<PageData> Nonblank pages, preserving original page numbers. */
    public function extract(string $contents, int $maxPages): array;
}
