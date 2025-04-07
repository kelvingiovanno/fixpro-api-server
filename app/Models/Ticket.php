<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\TicketStatusType;
use App\Models\Enums\ResponseLevelType;

class Ticket extends Model
{   
    use HasFactory;

    protected $table = 'tickets';

    protected $fillable = [
        'user_id',
        'ticket_issue_type_id',
        'ticket_status_type_id',
        'response_level_type_id',
        'location_id',
        'description',
        'closed_on',
    ];

    public $timestamps = false;

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
