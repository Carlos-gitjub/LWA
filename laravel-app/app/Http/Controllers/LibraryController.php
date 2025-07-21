<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;
use App\Models\BookPage;
use Illuminate\Support\Facades\DB;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::latest()->get();
    
        if ($request->wantsJson()) {
            return response()->json($books);
        }
    
        return inertia('Library/Index', [
            'books' => $books
        ]);
    }
    

    public function search(Request $request)
    {
        $query = $request->input('q');
        $books = Book::where('title', 'like', "%{$query}%")
                     ->orWhere('author', 'like', "%{$query}%")
                     ->get();

        return response()->json($books);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:30000',
            'cover_base64' => 'nullable|string',
        ]);
    
        $path = null;
        $coverPath = null;

        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'Archivo no válido'], 400);
        }
    
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('books', 'public');
        }
    
        if ($request->filled('cover_base64')) {
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $request->input('cover_base64'));
            $imageData = base64_decode($imageData);
            $filename = 'covers/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $coverPath = $filename;
        }
    
        $book = Book::create([
            'title' => $validated['title'],
            'author' => $validated['author'] ?? null,
            'file_path' => $path,
            'cover_path' => $coverPath,
        ]);

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile(storage_path("app/public/{$path}"));
            $pages = $pdf->getPages();
        } catch (\Exception $e) {
            Storage::disk('public')->delete($path);
            $book->delete();
            return response()->json(['error' => 'Error al procesar el PDF'], 422);
        }

        $maxPages = 20000;
        foreach ($pages as $index => $page) {
            if ($index >= $maxPages) break;
        
            $text = $page->getText();
            if (trim($text) !== '') {
                BookPage::create([
                    'book_id' => $book->id,
                    'page_number' => $index + 1,
                    'content' => $text
                ]);
            }
        }
    
        return response()->json([
            'book' => $book->fresh()
        ]);
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        $book->pages()->delete();

        if ($book->file_path && Storage::disk('public')->exists($book->file_path)) {
            Storage::disk('public')->delete($book->file_path);
        }

        if ($book->cover_path && Storage::disk('public')->exists($book->cover_path)) {
            Storage::disk('public')->delete($book->cover_path);
        }

        $book->delete();

        return response()->json(['message' => 'Libro eliminado correctamente']);
    }

    public function searchEngine(Request $request)
    {
        $query = $request->input('q');
        $authorIds = $request->input('authors', []);
        $bookIds = $request->input('books', []);

        $rawResults = BookPage::query()
            ->with('book')
            ->when($query, function ($q) use ($query) {
                $q->where('content', 'like', '%' . $query . '%');
            })
            ->when(!empty($authorIds), function ($q) use ($authorIds) {
                $q->whereHas('book', function ($q2) use ($authorIds) {
                    $q2->whereIn('author', $authorIds);
                });
            })
            ->when(!empty($bookIds), function ($q) use ($bookIds) {
                $q->whereIn('book_id', $bookIds);
            })
            ->limit(10000)
            ->get();

        $grouped = $rawResults->groupBy(fn($item) => $item->book_id . '-' . $item->page_number);

        $results = $grouped->map(function ($items, $key) use ($query) {
            $first = $items->first();
            $matches = substr_count(strtolower($first->content), strtolower($query));
            return [
                'id' => $first->id,
                'book' => $first->book->title,
                'author' => $first->book->author,
                'preview' => $this->getSnippet($first->content, $query),
                'page' => $first->page_number,
                'matches' => $matches
            ];
        });

        $totalMatches = $results->sum('matches');

        return response()->json([
            'results' => $results->values(),
            'total_matches' => $totalMatches,
            'total_pages' => $results->count()
        ]);
    }


    private function getSnippet($text, $query, $radius = 40)
    {
        $pos = stripos($text, $query);
        if ($pos === false) return substr($text, 0, $radius * 2) . '...';

        $start = max(0, $pos - $radius);
        $length = strlen($query) + $radius * 2;
        return '...' . substr($text, $start, $length) . '...';
    }
}