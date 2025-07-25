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
        try {
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
    
            $results = collect();
            $totalMatches = 0;
    
            foreach ($grouped as $group) {
                $first = $group->first();
                $book = $first->book()->first();
    
                // Sanitizar contenido para asegurar codificación válida
                $content = mb_convert_encoding($first->content, 'UTF-8', 'UTF-8');
    
                $matches = substr_count(mb_strtolower($content), mb_strtolower($query));
                $totalMatches += $matches;
    
                $results->push([
                    'id' => $first->id,
                    'book' => mb_convert_encoding($book->title ?? 'Libro desconocido', 'UTF-8', 'UTF-8'),
                    'author' => mb_convert_encoding($book->author ?? 'Autor desconocido', 'UTF-8', 'UTF-8'),
                    'preview' => mb_convert_encoding($this->getSnippet($content, $query), 'UTF-8', 'UTF-8'),
                    'page' => $first->page_number,
                    'count' => $matches,
                ]);
            }
    
            return response()->json([
                'results' => $results,
                'total_matches' => $totalMatches,
                'total_pages' => $grouped->count()
            ], 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE);
    
        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
    
    private function getSnippet($text, $query, $radius = 40)
    {
        // Convertir a UTF-8 seguro
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $query = mb_convert_encoding($query, 'UTF-8', 'UTF-8');
    
        $pos = mb_stripos($text, $query);
    
        if ($pos === false) {
            return mb_substr($text, 0, $radius * 2) . '...';
        }
    
        $start = max(0, $pos - $radius);
        $length = mb_strlen($query) + $radius * 2;
    
        return '...' . mb_substr($text, $start, $length) . '...';
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'nullable|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:30000',
            'cover_base64' => 'nullable|string',
        ]);

        // Si se sube un nuevo archivo PDF
        if ($request->hasFile('file')) {
            // Eliminar el anterior
            if ($book->file_path && Storage::disk('public')->exists($book->file_path)) {
                Storage::disk('public')->delete($book->file_path);
            }
            // Guardar el nuevo
            $book->file_path = $request->file('file')->store('books', 'public');

            // Reprocesar el contenido PDF
            $book->pages()->delete();
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile(storage_path("app/public/{$book->file_path}"));
                $pages = $pdf->getPages();

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
            } catch (\Exception $e) {
                Storage::disk('public')->delete($book->file_path);
                return response()->json(['error' => 'Error al procesar el nuevo PDF'], 422);
            }
        }

        // Si se sube una nueva portada
        if ($request->filled('cover_base64')) {
            if ($book->cover_path && Storage::disk('public')->exists($book->cover_path)) {
                Storage::disk('public')->delete($book->cover_path);
            }
            $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->input('cover_base64')));
            $filename = 'covers/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $book->cover_path = $filename;
        }

        $book->title = $validated['title'];
        $book->author = $validated['author'] ?? null;
        $book->save();

        return response()->json(['book' => $book->fresh()]);
    }


    
    
}