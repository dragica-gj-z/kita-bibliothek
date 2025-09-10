<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use App\Enums\BookStatus;
use App\Enums\BookCondition;

class AddBooksController extends Controller
{
    public function addBook(Request $request)
    {
        $data = $request->validate([
            'isbn'             => ['nullable', 'string', 'max:50', 'unique:books,isbn'],
            'title'            => ['required', 'string', 'max:255'],
            'author'           => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'status'           => ['required', new Enum(BookStatus::class)],
            'condition'        => ['required', new Enum(BookCondition::class)],
            'category_per_age' => ['nullable', 'string', 'max:50'],
        ]);

        $book = Book::create([
            'isbn'             => $data['isbn'] ?? null,
            'title'            => $data['title'],
            'author'           => $data['author'] ?? null,
            'description'      => $data['description'] ?? null,
            'status'           => $data['status'],
            'condition'        => $data['condition'],
            'category_per_age' => $data['category_per_age'] ?? null,
        ]);

        return response()->json([
            'message' => 'Buch erfolgreich angelegt.',
            'book'    => $book,
        ], 201);
    }
}
