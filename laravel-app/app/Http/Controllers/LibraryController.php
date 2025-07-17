<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

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
            'file' => 'nullable|file|mimes:pdf|max:10240', // 10MB
        ]);

        $path = null;

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('books', 'public');
        }

        Book::create([
            'title' => $validated['title'],
            'author' => $validated['author'] ?? null,
            'file_path' => $path,
        ]);

        return redirect()->route('library.index')->with('success', 'Libro añadido correctamente.');
    }
}
