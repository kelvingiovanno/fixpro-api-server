<?php

namespace App\Models\Enums;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketResponseType extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_response_types';

    protected $fillable = [
        'id',
        'name',
        'sla_modifier',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'sla_modifier' => 'double',
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
        return $this->hasMany(Ticket::class, 'response_id', 'id');   
    }
}