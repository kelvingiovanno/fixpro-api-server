<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\ResponseLevelType;

use Illuminate\Support\Str;

use App\Enums\TikectStatusEnum;

class Ticket extends Model
{   
    use HasFactory;

    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $table = 'tickets';

    protected $fillable = [
        'user_id',
        'ticket_issue_type_id',
        'ticket_status_type_id',
        'response_level_type_id',
        'location_id',
        'stated_issue',
        'closed_on',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            $ticket->raised_on = now();
            $ticket->ticket_status_type_id = TikectStatusEnum::OPEN;

            if(! $ticket->getKey()) {
                $ticket->{$ticket->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function issueType()
    {
        return $this->belongsTo(TicketIssueType::class, 'ticket_issue_type_id');
    }

    public function statusType()
    {
        return $this->belongsTo(TicketStatusType::class, 'ticket_status_type_id');
    }

    public function responseLevelType()
    {
        return $this->belongsTo(ResponseLevelType::class, 'response_level_type_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
