<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function index()
    {
        return inertia('Library/Index', [
            'books' => Book::latest()->get()
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
            'file' => 'nullable|file|mimes:pdf|max:30000', // 30MB
            'cover_base64' => 'nullable|string',
        ]);
    
        $path = null;
        $coverPath = null;
    
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('books', 'public');
        }
    
        // Guardar imagen base64 si existe
        if ($request->filled('cover_base64')) {
            $imageData = $request->input('cover_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
    
            $filename = 'covers/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $coverPath = $filename;
        }
    
        Book::create([
            'title' => $validated['title'],
            'author' => $validated['author'] ?? null,
            'file_path' => $path,
            'cover_path' => $coverPath,
        ]);
    
        return response()->json([
            'book' => Book::create([
                'title' => $validated['title'],
                'author' => $validated['author'] ?? null,
                'file_path' => $path,
                'cover_path' => $coverPath,
            ])->fresh()
        ]);
        
    }
    
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Elimina también el archivo PDF si existe
        if ($book->pdf_path && Storage::exists($book->pdf_path)) {
            Storage::delete($book->pdf_path);
        }

        // Elimina la portada si es imagen
        if ($book->cover_path && Storage::exists($book->cover_path)) {
            Storage::delete($book->cover_path);
        }

        $book->delete();

        return response()->json(['message' => 'Libro eliminado correctamente']);
    }

}
