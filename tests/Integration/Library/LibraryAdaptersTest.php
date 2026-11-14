<?php

namespace Tests\Integration\Library;

use App\Domain\Library\UseCases\Data\BookData;
use App\Domain\Library\UseCases\Data\PageData;
use App\Domain\Library\UseCases\LibraryUnavailable;
use App\Infrastructure\Library\Adapters\EloquentBookRepository;
use App\Infrastructure\Library\Adapters\SmalotPdfTextExtractor;
use App\Infrastructure\Library\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Smalot\PdfParser\Parser;
use Tests\Support\LibraryPdf;
use Tests\TestCase;

final class LibraryAdaptersTest extends TestCase
{
    use LibraryPdf, RefreshDatabase;

    public function test_page_write_failure_rolls_back_book_and_old_index(): void
    {
        $book = Book::create(['title' => 'Old', 'file_path' => 'old.pdf']);
        $book->pages()->create(['page_number' => 1, 'content' => 'Old content']);
        // SQLite rejects NULL content after the old pages have been deleted.
        $invalidPage = (object) ['number' => 1, 'content' => null];
        try {
            (new EloquentBookRepository)->save(new BookData($book->id, 'New', null, 'new.pdf', null), [$invalidPage]);
            self::fail('Expected a database failure.');
        } catch (LibraryUnavailable) {
            $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Old', 'file_path' => 'old.pdf']);
            $this->assertDatabaseHas('book_pages', ['book_id' => $book->id, 'content' => 'Old content']);
        }
    }

    public function test_real_parser_returns_plain_page_data_and_honors_limit(): void
    {
        $extractor = new SmalotPdfTextExtractor(new Parser);
        $pages = $extractor->extract($this->pdf('Plain text'), 20000);
        self::assertCount(1, $pages);
        self::assertInstanceOf(PageData::class, $pages[0]);
        self::assertSame(1, $pages[0]->number);
        self::assertStringContainsString('Plain text', $pages[0]->content);
        self::assertSame([], $extractor->extract($this->pdf(), 0));
    }
}
