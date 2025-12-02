<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\BookCondition;
use App\Enums\BookStatus;
use App\Enums\KiConfidence;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $primaryKey = 'book_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'isbn',
        'title',
        'author',
        'description',
        'status',
        'condition',
        'category_per_age',
        'publisher',
        'published_at',
        'page_count',
        'categories',
        'cover',
        'confidence',
    ];

    protected $casts = [
        'condition' => BookCondition::class,
        'status'    => BookStatus::class,
        'confidence' => KiConfidence::class,
    ];
    public function adults()
    {
        return $this->belongsToMany(Adult::class, 'adults_books');
    }

    public function getConfidenceLabelAttribute(): string
    {
        return match ($this->confidence) {
            KiConfidence::HIGH   => 'hoch',
            KiConfidence::MEDIUM => 'mittel',
            KiConfidence::LOW    => 'niedrig',
            default            => '-',
        };
    }
}
