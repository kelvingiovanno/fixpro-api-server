<?php

namespace App\Models;

use App\Enums\TicketStatusEnum;

use App\Models\Enums\TicketStatusType;
use App\Models\Enums\TicketResponseType;

use App\Models\Member;
use App\Models\TicketDocument;
use App\Models\TicketLog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ticket extends Model
{   
    use HasFactory;
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'member_id',
        'assessed_by',
        'evaluated_by',
        'status_id',
        'response_id',
        'location_id',
        'stated_issue',
        'executive_summary',
        'closed_at',
    ];

    protected $casts = [
        'raised_on' => 'datetime',
        'closed_on' => 'datetime',
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

            $model->raised_on = now();

            if(! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function issuer()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function assessed()
    {
        return $this->belongsTo(Member::class, 'assessed_by', 'id');
    }

    public function evaluated()
    {
        return $this->belongsTo(Member::class, 'evaluated_by', 'id');
    }

    public function ticket_issues()
    {
        return $this->hasMany(TicketIssue::class, 'ticket_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(TicketStatusType::class, 'status_id', 'id');
    }

    public function response()
    {
        return $this->belongsTo(TicketResponseType::class, 'response_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
    
    public function documents() 
    {
        return $this->hasMany(TicketDocument::class, 'ticket_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class, 'ticket_id', 'id');
    }
}