<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Str;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'events';
    
    protected $fillable = [
        'ticket_id',
        'calender_id',
        'summary',
        'description',
        'start',
        'end',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    public $incrementing = false; 
    public $timestamps = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {            
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            
        });
    }

    public function calender()
    {
        return $this->belongsTo(Calender::class, 'calender_id', 'id');
    }

    public function tikcet()
    {
        return $this->belongsTo(Member::class, 'ticket_id', 'id');
    }
}