<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Model;

class TicketIssueType extends Model
{
    protected $table = 'ticket_issue_types';

    protected $fillable = [
        'label'
    ];
}
