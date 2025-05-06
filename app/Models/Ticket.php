<?php

namespace App\Models;

use App\Enums\TicketStatusEnum;

use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\ResponseLevelType;

use App\Models\User;
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
        'user_id',
        'ticket_status_type_id',
        'response_level_type_id',
        'location_id',
        'executive_summary',
        'stated_issue',
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
            $model->ticket_status_type_id = TicketStatusEnum::OPEN->id();

            if(! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function maintainers()
    {
        return $this->belongsToMany(User::class, 'ticket_maintenance_staffs', 'ticket_id', 'user_id');
    }

    public function statusType()
    {
        return $this->belongsTo(TicketStatusType::class, 'ticket_status_type_id');
    }

    public function issues()
    {
        return $this->belongsToMany(TicketIssueType::class, 'issue_type_ticket', 'ticket_id', 'issue_type_id');
    }

    public function responseLevelType()
    {
        return $this->belongsTo(ResponseLevelType::class, 'response_level_type_id');
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