<?php

namespace App\Models;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'resource_path',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $timestamps = false;

    public function tickets()
    {
        return $this->belongsTo(Ticket::class, 'ticket_log_id' , 'id');
    }
}
