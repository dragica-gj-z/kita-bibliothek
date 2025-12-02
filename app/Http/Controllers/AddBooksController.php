<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use App\Enums\BookStatus;
use App\Enums\BookCondition;
use App\Enums\KiConfidence;


class AddBooksController extends Controller
{
    public function addBook(Request $request)
    {
        $data = $request->validate([
            'isbn'             => ['nullable', 'string', 'max:50', 'unique:books,isbn'],
            'title'            => ['required', 'string', 'max:255'],
            'author'           => ['nullable', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'confidence'       => ['nullable', new Enum(KiConfidence::class)],
            'status'           => ['required', new Enum(BookStatus::class)],
            'condition'        => ['required', new Enum(BookCondition::class)],
            'category_per_age' => ['nullable', 'string', 'max:50'],
            'publisher'   => ['nullable', 'string', 'max:255'],
            'publishedAt' => ['nullable', 'string', 'max:20'],
            'pageCount'   => ['nullable', 'integer', 'min:1'],
            'categories'  => ['nullable', 'string', 'max:255'], 
            'cover'       => ['nullable', 'url', 'max:2048'],
        ]);

        $book = Book::create([
            'isbn'             => $data['isbn'] ?? null,
            'title'            => $data['title'],
            'author'           => $data['author'] ?? null,
            'description'      => $data['description'] ?? null,
            'confidence' => $data['confidence'] ?? KiConfidence::LOW->value,
            'status'           => $data['status'],
            'condition'        => $data['condition'],
            'category_per_age' => $data['category_per_age'] ?? null,
            'publisher'        => $data['publisher'] ?? null,
            'published_at'     => $data['publishedAt'] ?? null, 
            'page_count'       => $data['pageCount'] ?? null,
            'categories'       => $data['categories'] ?? null,
            'cover'            => $data['cover'] ?? null,
        ]);

        return response()->json([
            'message' => 'Buch erfolgreich angelegt.',
            'book'    => $book,
        ], 201);
    }
}
