<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calender extends Model
{
    use SoftDeletes;

    protected $table = 'calenders';
    
    protected $fillable = [
        'id',
        'name',
    ];

    protected $hidden = [
        'deleted_at',
    ];
}
