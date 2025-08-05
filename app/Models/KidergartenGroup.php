<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KidergartenGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'group_name'
    ];

    public function adults () {
        return $this->belongsToMany(Adult::class, 'adults_groups');
    }
}
