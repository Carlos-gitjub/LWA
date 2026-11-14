<?php

namespace App\Infrastructure\Library\Adapters;

use App\Domain\Library\UseCases\Data\PageData;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;
use App\Domain\Library\UseCases\InvalidPdf;
use Smalot\PdfParser\Parser;

final class SmalotPdfTextExtractor implements PdfTextExtractor
{
    public function __construct(private Parser $parser) {}

    public function extract(string $contents, int $maxPages): array
    {
        try {
            $result = [];
            foreach ($this->parser->parseContent($contents)->getPages() as $index => $page) {
                if ($index >= $maxPages) {
                    break;
                }
                $text = mb_convert_encoding($page->getText(), 'UTF-8', 'UTF-8');
                if (trim($text) !== '') {
                    $result[] = new PageData($index + 1, $text);
                }
            }

            return $result;
        } catch (\Exception $error) {
            throw new InvalidPdf('Could not extract the PDF.', 0, $error);
        }
    }
}
