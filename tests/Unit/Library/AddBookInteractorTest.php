<?php

namespace Tests\Unit\Library;

use App\Domain\Library\UseCases\AddBook\AddBookInputData;
use App\Domain\Library\UseCases\AddBook\AddBookInteractor;
use App\Domain\Library\UseCases\AddBook\AddBookOutputBoundary;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookFileStorage;
use App\Domain\Library\UseCases\DataAccessInterfaces\BookRepository;
use App\Domain\Library\UseCases\DataAccessInterfaces\PdfTextExtractor;
use App\Domain\Library\UseCases\LibraryUnavailable;
use PHPUnit\Framework\TestCase;

final class AddBookInteractorTest extends TestCase
{
    public function test_cover_write_failure_cleans_new_pdf_without_saving_book(): void
    {
        $books = $this->createMock(BookRepository::class);
        $books->expects(self::never())->method('save');
        $files = $this->createMock(BookFileStorage::class);
        $files->method('storePdf')->willReturn('new.pdf');
        $files->method('storeCover')->willThrowException(new LibraryUnavailable);
        $deleted = [];
        $files->method('delete')->willReturnCallback(function ($path) use (&$deleted) {
            $deleted[] = $path;
        });
        $extractor = $this->createMock(PdfTextExtractor::class);
        $extractor->method('extract')->willReturn([]);
        try {
            (new AddBookInteractor($books, $files, $extractor, $this->unpublishedOutput()))->execute(new AddBookInputData('New', null, 'pdf', 'cover'));
            self::fail('Expected storage failure.');
        } catch (LibraryUnavailable) {
            self::assertSame(['new.pdf', null], $deleted);
        }
    }

    public function test_invalid_title_is_rejected_without_infrastructure(): void
    {
        $books = $this->createMock(BookRepository::class);
        $books->expects(self::never())->method('save');
        $extractor = $this->createMock(PdfTextExtractor::class);
        $extractor->expects(self::never())->method('extract');
        $this->expectException(\InvalidArgumentException::class);
        (new AddBookInteractor($books, $this->createMock(BookFileStorage::class), $extractor, $this->unpublishedOutput()))->execute(new AddBookInputData(' ', null, 'pdf', null));
    }

    private function unpublishedOutput(): AddBookOutputBoundary
    {
        $output = $this->createMock(AddBookOutputBoundary::class);
        $output->expects(self::never())->method('present');

        return $output;
    }
}
