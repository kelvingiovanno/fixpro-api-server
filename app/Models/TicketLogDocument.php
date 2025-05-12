<?php

namespace App\Models;

use App\Models\TicketLog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketLogDocument extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ticket_log_documents';

    protected $fillable = [
        'ticket_log_id',
        'resource_type',
        'resource_name',
        'resource_size',
        'previewable_on',
    ];

    protected $hidden = [
        'id',
        'ticket_log_id',
        'deleted_at',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {    
            $model->expires_at = now()->addYear();
        
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            } 
        });
    }

    public function ticket_logs()
    {
        return $this->belongsTo(TicketLog::class, 'log_id' , 'id');
    }
}
