<?php

namespace App\Models;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Location extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'locations';
    
    protected $fillable = [
        'stated_location',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {   
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            } 
        });
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class,  'location_id', 'id');
    }
}