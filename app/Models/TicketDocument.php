<?php

namespace App\Models;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketDocument extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'ticket_documents';

    protected $fillable = [
        'ticket_id',
        'resource_type',
        'resource_name',
        'resource_size',
        'previewable_on',
    ];

    protected $hidden = [
        'id',
        'ticket_id',
        'deleted_at',
    ];

    public $timestamps = false;
    
    public function tickets()
    {
        return $this->belongsTo(Ticket::class);
    }
}