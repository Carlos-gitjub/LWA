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
}
