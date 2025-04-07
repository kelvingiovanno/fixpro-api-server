<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Model;

class ResponseLevelType extends Model
{
    protected $table = 'response_level_types';

    protected $fillable = [
        'label',
    ];
}
