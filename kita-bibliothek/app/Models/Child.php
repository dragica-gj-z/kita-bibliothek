<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Child extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_id',
        'group_id',
        'child_first_name',
        'child_last_name',
        'child_birthday'
    ];

    public function groups (){
        return $this->belongsToMany(KidergartenGroup::class, 'kindergarten_group');
    }
}
