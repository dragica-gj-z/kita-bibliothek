<?php

namespace App\Http\Controllers;

use App\Models\Book;
use BackedEnum; // PHP 8.1

class ShowBooksController extends Controller
{
    public function showBooks()
    {
        $books = Book::query()
            ->orderByDesc('created_at') // timestamps vorhanden → sortieren ok
            ->get([
                'book_id',
                'isbn',
                'title',
                'author',
                'description',
                'status',
                'condition',
                'category_per_age',
                'created_at',
            ])
            ->map(function ($book) {
                // Enum-Objekte sicher in String-Codes wandeln
                $condCode   = $book->condition instanceof BackedEnum ? $book->condition->value : $book->condition;
                $statusCode = $book->status    instanceof BackedEnum ? $book->status->value    : $book->status;

                return [
                    // vereinheitlichte ID fürs FE
                    'id'          => $book->book_id,
                    'isbn'        => $book->isbn,
                    'title'       => $book->title,
                    'author'      => $book->author,
                    'description' => $book->description,

                    // Codes (falls FE sie braucht)
                    'age_group'   => $book->category_per_age,
                    'condition'   => $condCode,
                    'status'      => $statusCode,

                    // Labels aus config/books.php
                    'condition_label' => config("books.conditions.$condCode", $condCode),
                    'status_label'    => config("books.statuses.$statusCode", $statusCode),

                    // ISO-8601 für FE
                    'created_at'  => $book->created_at?->toISOString(),
                ];
            });

        return response()->json($books);
    }
}
