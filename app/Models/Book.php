<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookCondition;
use App\Enums\BookStatus;

class Book extends Model
{
    use HasFactory;

    protected $primaryKey = 'book_id';
    public $incrementing = true;
   
    protected $fillable = [
        'isbn',
        'title',
        'author',
        'description',
        'status',
        'condition',
        'category_per_age',
    ];

    protected $casts = [
        'status'           => BookStatus::class,
        'condition'        => BookCondition::class,
    ];

    public function adults()
    {
        return $this->belongsToMany(Adult::class, 'adults_books');
    }
}
