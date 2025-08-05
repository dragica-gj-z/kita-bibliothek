<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'title',
        'description',
        'status',
        'condition',
        'category_per_age'
    ];

    public function adults () {
        return $this->belongsToMany(Adult::class, 'adults_books');
    }
}
