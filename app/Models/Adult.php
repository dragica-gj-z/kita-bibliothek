<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Adult extends Model
{
    use HasFactory;

    protected $fillable = [
        'adult_id',
        'adult_first_name',
        'adult_last_name',
        'adult_street',
        'adult_house_nr',
        'adult_city',
        'adult_email',
        'adult_tel_nr'
    ];

    public function books () {
        return $this->belongsToMany(Book::class, 'adults_books');
    }
    public function groups () {
        return $this->belongsToMany(KidergartenGroup::class, 'adults_books');
    }


}
