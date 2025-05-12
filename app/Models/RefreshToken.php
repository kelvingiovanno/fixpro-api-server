<?php

namespace App\Models;

use App\Models\Member;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
USE Illuminate\Support\Str;

class RefreshToken extends Model
{
    use SoftDeletes, HasFactory;
    
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'member_id',
        'token',
    ];

    protected $casts = [
        'expires_on' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'delete_at',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {            
            $model->expires_on = now()->addMonth();

            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            
        });
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }
}