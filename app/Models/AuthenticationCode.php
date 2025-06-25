<?php

namespace App\Models;

use App\Models\Applicant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AuthenticationCode extends Model
{
    use SoftDeletes, HasFactory;
    
    protected $table = 'authentication_codes';
    
    protected $fillable = [
        'application_id',
    ];

    protected $casts = [
        'expires_on' => 'datetime',
    ];

    protected $hidden = [
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
            $model->expires_on = now()->addWeek();
        
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            } 
        });

        static::deleting(function ($model) {
            if ($model->member) {
                $model->applicant->delete();
            }
        });
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'application_id', 'id');
    }

}