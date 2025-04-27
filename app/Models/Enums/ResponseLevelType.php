<?php

namespace App\Models\Enums;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResponseLevelType extends Model
{
    use SoftDeletes;

    protected $table = 'response_level_types';

    protected $fillable = [
        'id',
        'label',
    ];

    protected $hidden = [
        'id',
    ];

    public $timestamps = false;

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'response_level_type_id', 'id');   
    }
}