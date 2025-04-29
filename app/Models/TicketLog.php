<?php

namespace App\Models;

use App\Models\Enums\TicketLogType;

use App\Models\Ticket;
use App\Models\User;
use App\Models\TicketLogDocument;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketLog extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'ticket_logs';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'ticket_log_type_id',
        'news',
    ];

    protected $casts = [
        'recorded_on' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string';
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
            
            $model->recorded_on = now();
        });
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function logType()
    {
        return $this->belongsTo(TicketLogType::class, 'ticket_log_type_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(TicketLogDocument::class, 'ticket_log_id', 'id');
    }
}