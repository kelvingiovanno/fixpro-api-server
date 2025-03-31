<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\UserRoleEnum;

class User extends Model
{
    protected $fillable = [
        'application_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->role_id = UserRoleEnum::MEMBER;
        });
    }

    public function userData()
    {
        return $this->hasOne(UserData::class);
    }

    public function role()
    {
        return $this->belongsTo(UserRole::class, 'role_id');
    }
}
