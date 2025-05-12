<?php

namespace App\Models\Enums;

use App\Models\TicketLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketLogType extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_log_types';
    
    protected $fillable = [
        'id',
        'name',
    ];

    protected $hidden = [
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

    public function ticket_logs()
    {
        return $this->hasMany(TicketLog::class, 'type_id', 'id');
    }
}