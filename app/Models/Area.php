<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Area extends Model
{
    public $incrementing = false; 
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'join_policy',
        'join_form',
        'member_count',
        'pending_member',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($area) 
        {            
            if (!$area->getKey()) {
                $area->{$area->getKeyName()} = (string) Str::uuid();
            } 
        });
    }
}
