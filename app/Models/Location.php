<?php

namespace App\Models;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'locations';
    
    protected $fillable = [
        'stated_location',
        'latitude',
        'longitude',
        'deleted_at',
    ];

    protected $hidden = [
        'id',
    ];

    public $timestamps = false;

    public function ticket()
    {
        return $this->hasOne(Ticket::class,  'location_id', 'id');
    }
}