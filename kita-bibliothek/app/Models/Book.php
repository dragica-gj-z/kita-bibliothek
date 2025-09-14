<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookCondition;
use App\Enums\BookStatus;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    // Primärschlüssel
    protected $primaryKey = 'book_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // timestamps sind in deinen Migrationen vorhanden → Standard ist true
    // public $timestamps = true; // (nur zur Info)

    protected $fillable = [
        'isbn', 
        'title', 
        'author', 
        'description',
        'status', 
        'condition', 
        'category_per_age',
    ];

    // Wenn du Enums verwendest (empfohlen)
    protected $casts = [
        'condition' => BookCondition::class,
        'status'    => BookStatus::class,
    ];
    public function adults()
    {
        return $this->belongsToMany(Adult::class, 'adults_books');
    }
}
