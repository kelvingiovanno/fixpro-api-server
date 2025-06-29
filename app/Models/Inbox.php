<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Inbox extends Model
{
    use HasFactory;

    protected $table = 'inbox';

    protected $fillable = [
        'member_id',
        'ticket_id',
        'title',
        'body',
        'sent_on',
    ];

    protected $casts = [
        'sent_on' => 'datetime',
    ];

    protected $hidden = [
        'member_id',
        'ticket_id',
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

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }
}
