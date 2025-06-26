<?php

namespace App\Models;

use App\Models\TicketIssue;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WODocument extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'work_order_documents';

    protected $fillable = [
        'id',
        'ticket_issue_id',
        'resource_type',
        'resource_name',
        'resource_size',
        'previewable_on',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    public function ticket_issue()
    {
        return $this->belongsTo(TicketIssue::class, 'ticket_issue_id', 'id');
    }
}
