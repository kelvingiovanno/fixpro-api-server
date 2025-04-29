<?php

namespace App\Models;

use App\Models\Applicant;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AuthenticationCode extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'authentication_codes';
    
    protected $fillable = [
        'applicant_id', 
        'user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
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
            $model->expires_at = now()->addYear();
        
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            } 
        });
    }

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'application_id', 'id');
    }

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}