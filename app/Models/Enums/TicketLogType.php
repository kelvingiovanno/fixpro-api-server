<?php

namespace App\Models\Enums;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketLogType extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_log_types';
    
    protected $fillable = [
        'id',
        'label',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $incrementing = false; 
    public $timestamps = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_log_type_id', 'id');
    }
}