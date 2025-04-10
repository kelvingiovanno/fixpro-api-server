<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportiveTicketDocument extends Model
{
    protected $table = 'supportive_ticket_documents';

    protected $fillable = [
        'ticket_id',
        'resource_type',
        'resource_name',
        'resource_size',
        'resource_content'
    ];
}
