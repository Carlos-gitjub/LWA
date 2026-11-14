<?php

namespace App\Domain\Library\UseCases\AddBook;

use App\Domain\Library\Entities\Book;
use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;

final class AddBookInteractor implements AddBookInputBoundary
{
    public const MAX_PAGES = 20000;

    public const MAX_PDF_BYTES = 30000 * 1024;

    public function __construct(
        private BookRepository $books,
        private BookFileStorage $files,
        private PdfTextExtractor $extractor,
        private AddBookOutputBoundary $output,
    ) {}

    public function execute(AddBookInputData $input): void
    {
        $book = new Book($input->title, $input->author);
        if (strlen($input->pdfContents) > self::MAX_PDF_BYTES) {
            throw new \InvalidArgumentException('The PDF exceeds the size limit.');
        }
        $pages = $this->extractor->extract($input->pdfContents, self::MAX_PAGES);
        $pdf = $cover = null;
        try {
            $pdf = $this->files->storePdf($input->pdfContents);
            $cover = $input->coverContents === null ? null : $this->files->storeCover($input->coverContents);
            $saved = $this->books->save(new BookData(null, $book->title, $book->author, $pdf, $cover), $pages);
        } catch (\Throwable $error) {
            $this->files->delete($pdf);
            $this->files->delete($cover);
            throw $error;
        }
        $this->output->present(new AddBookOutputData($saved));
    }
}
