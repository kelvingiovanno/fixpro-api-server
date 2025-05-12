<?php

namespace App\Models;

use App\Models\Enums\TicketIssueType;

use App\Models\Ticket;
use App\Models\WODocument;
use App\Models\Member;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketIssue extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'ticket_issues';

    protected $fillable = [
        'issue_id',
        'ticket_id',
        'wo_id',
    ];

    protected $hidden = [
        'id',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id');
    }

    public function issue()
    {
        return $this->belongsTo(TicketIssueType::class, 'issue_id', 'id');
    }

    public function maintainers()
    {
        return $this->belongsToMany(Member::class, 'maintainers', 'ticket_issue_id', 'member_id');
    }

    public function wo_document()
    {
        return $this->belongsTo(WODocument::class, 'wo_id', 'id');
    }
}
