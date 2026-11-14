<?php

namespace App\Domain\Library\UseCases\UpdateBook;

use App\Domain\Library\Entities\Book;
use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;

final class UpdateBookInteractor implements UpdateBookInputBoundary
{
    public const MAX_PAGES = 20000;

    public const MAX_PDF_BYTES = 30000 * 1024;

    public function __construct(
        private BookRepository $books,
        private BookFileStorage $files,
        private PdfTextExtractor $extractor,
        private UpdateBookOutputBoundary $output,
    ) {}

    public function execute(UpdateBookInputData $input): void
    {
        $book = new Book($input->title, $input->author);
        $old = $this->books->find($input->id);
        if ($input->pdfContents !== null && strlen($input->pdfContents) > self::MAX_PDF_BYTES) {
            throw new \InvalidArgumentException('The PDF exceeds the size limit.');
        }
        // Prepare replacement pages before changing any saved data or files.
        $pages = $input->pdfContents === null ? null : $this->extractor->extract($input->pdfContents, self::MAX_PAGES);
        $newPdf = $newCover = null;
        try {
            $newPdf = $input->pdfContents === null ? null : $this->files->storePdf($input->pdfContents);
            $newCover = $input->coverContents === null ? null : $this->files->storeCover($input->coverContents);
            $saved = $this->books->save(new BookData(
                $old->id, $book->title, $book->author,
                $newPdf ?? $old->filePath, $newCover ?? $old->coverPath,
            ), $pages);
        } catch (\Throwable $error) {
            $this->files->delete($newPdf);
            $this->files->delete($newCover);
            throw $error;
        }
        if ($newPdf !== null) {
            $this->files->delete($old->filePath);
        }
        if ($newCover !== null) {
            $this->files->delete($old->coverPath);
        }
        $this->output->present(new UpdateBookOutputData($saved));
    }
}
