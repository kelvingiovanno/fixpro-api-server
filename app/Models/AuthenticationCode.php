<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuthenticationCode extends Model
{
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected $table = 'authentication_codes';
    protected $fillable = ['applicant_id', 'user_id'];

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
}
