<?php

namespace App\Models\Enums;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketStatusType extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_status_types';

    protected $fillable = [
        'id',
        'label',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $timestamps = false;

    public function tickets() 
    {
        return $this->hasMany(Ticket::class, 'ticket_status_type_id', 'id');
    }
}