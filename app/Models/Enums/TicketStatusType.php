<?php

namespace App\Models\Enums;

use Illuminate\Database\Eloquent\Model;

class TicketStatusType extends Model
{
    protected $table = 'ticket_status_types';

    protected $fillable = [
        'label',
    ];
}
