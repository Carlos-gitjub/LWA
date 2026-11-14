<?php

namespace Tests\Feature\Library;

use App\Infrastructure\Library\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Support\LibraryPdf;
use Tests\TestCase;

final class LibraryOperationsTest extends TestCase
{
    use LibraryPdf, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_lists_books_and_searches_titles_and_authors(): void
    {
        $book = Book::create(['title' => 'Architecture', 'author' => 'Alice']);
        Book::create(['title' => 'Cooking', 'author' => 'Bob']);
        $this->getJson('/library')->assertOk()->assertJsonCount(2);
        $this->getJson('/library/search?q=Alice')->assertOk()->assertJsonCount(1)->assertJsonPath('0.id', $book->id);
        $this->getJson('/library/search?q=Architecture')->assertJsonCount(1);
        $this->get('/library')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Library/Index')->has('books', 2));
    }

    public function test_adds_indexes_updates_and_deletes_a_book(): void
    {
        $response = $this->postJson('/library/store', [
            'title' => 'Example', 'author' => ' null ',
            'file' => UploadedFile::fake()->createWithContent('book.pdf', $this->pdf('Hello library')),
            'cover_base64' => 'data:image/jpeg;base64,'.base64_encode('cover'),
        ])->assertOk()->assertJsonPath('book.author', null);
        $id = $response->json('book.id');
        $oldPdf = $response->json('book.file_path');
        $oldCover = $response->json('book.cover_path');
        Storage::disk('public')->assertExists([$oldPdf, $oldCover]);
        $this->assertDatabaseHas('book_pages', ['book_id' => $id, 'page_number' => 1]);
        $this->postJson('/library/search-engine', ['q' => 'hello'])->assertOk()->assertJsonPath('total_matches', 1)
            ->assertJsonPath('results.0.book', 'Example');

        $updated = $this->postJson('/library/update/'.$id, [
            'title' => 'Changed', 'author' => 'Alice',
            'file' => UploadedFile::fake()->createWithContent('replacement.pdf', $this->pdf('New content')),
            'cover_base64' => base64_encode('new cover'),
        ])->assertOk();
        Storage::disk('public')->assertMissing([$oldPdf, $oldCover]);
        Storage::disk('public')->assertExists([$updated->json('book.file_path'), $updated->json('book.cover_path')]);
        $this->postJson('/library/search-engine', ['q' => 'hello'])->assertJsonPath('total_matches', 0);
        $this->postJson('/library/search-engine', ['q' => 'new'])->assertJsonPath('total_matches', 1);

        $this->deleteJson('/library/delete/'.$id)->assertOk()->assertJsonStructure(['message']);
        $this->assertDatabaseMissing('books', ['id' => $id]);
        $this->assertDatabaseMissing('book_pages', ['book_id' => $id]);
        self::assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_pdf_replacement_preserves_everything(): void
    {
        Storage::disk('public')->put('books/old.pdf', 'original');
        Storage::disk('public')->put('covers/old.jpg', 'original cover');
        $book = Book::create(['title' => 'Original', 'file_path' => 'books/old.pdf', 'cover_path' => 'covers/old.jpg']);
        $book->pages()->create(['page_number' => 1, 'content' => 'Original text']);
        $this->postJson('/library/update/'.$book->id, [
            'title' => 'Replacement',
            'file' => UploadedFile::fake()->createWithContent('invalid.pdf', '%PDF-1.4
invalid'),
            'cover_base64' => base64_encode('new cover'),
        ])->assertStatus(422);
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Original', 'file_path' => 'books/old.pdf']);
        $this->assertDatabaseHas('book_pages', ['book_id' => $book->id, 'content' => 'Original text']);
        self::assertSame('original', Storage::disk('public')->get('books/old.pdf'));
        self::assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_failed_add_does_not_leave_files_or_records(): void
    {
        $this->postJson('/library/store', [
            'title' => 'Invalid', 'file' => UploadedFile::fake()->createWithContent('invalid.pdf', '%PDF-1.4
invalid'),
            'cover_base64' => base64_encode('cover'),
        ])->assertStatus(422);
        $this->assertDatabaseCount('books', 0);
        self::assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_metadata_update_keeps_pdf_and_index(): void
    {
        $book = Book::create(['title' => 'Old', 'file_path' => 'books/old.pdf']);
        $book->pages()->create(['page_number' => 3, 'content' => 'Saved text']);
        $this->postJson('/library/update/'.$book->id, ['title' => 'New'])->assertOk()->assertJsonPath('book.file_path', 'books/old.pdf');
        $this->assertDatabaseHas('book_pages', ['book_id' => $book->id, 'page_number' => 3]);
    }

    public function test_search_filters_counts_and_empty_query(): void
    {
        $book = Book::create(['title' => 'A', 'author' => 'Alice']);
        $book->pages()->create(['page_number' => 2, 'content' => 'Hello HELLO']);
        $other = Book::create(['title' => 'B', 'author' => 'Bob']);
        $other->pages()->create(['page_number' => 1, 'content' => 'Hello']);
        $this->postJson('/library/search-engine', ['q' => 'hello', 'authors' => ['Alice'], 'books' => [$book->id]])
            ->assertOk()->assertJsonPath('total_matches', 2)->assertJsonPath('total_pages', 1)
            ->assertJsonPath('results.0.page', 2)->assertJsonPath('results.0.preview', '...Hello HELLO...');
        $this->postJson('/library/search-engine', ['q' => '', 'books' => [$book->id]])
            ->assertOk()->assertJsonPath('total_matches', 0)->assertJsonPath('total_pages', 1);
        $this->postJson('/library/search-engine', ['q' => 'hello', 'authors' => ['Nobody']])
            ->assertOk()->assertJsonPath('results', []);
    }

    public function test_validation_and_missing_book_responses(): void
    {
        $this->postJson('/library/store', ['title' => 'Missing PDF'])->assertStatus(400);
        $this->postJson('/library/store', ['title' => ''])->assertStatus(422);
        $this->postJson('/library/search-engine', ['books' => ['invalid']])->assertStatus(422);
        $this->deleteJson('/library/delete/999')->assertNotFound();
        $this->postJson('/library/update/999', ['title' => 'Missing'])->assertNotFound();
    }

    public function test_guests_cannot_access_library(): void
    {
        auth()->logout();
        $this->getJson('/library')->assertUnauthorized();
        $this->postJson('/library/store', [])->assertUnauthorized();
        $this->deleteJson('/library/delete/1')->assertUnauthorized();
    }
}
