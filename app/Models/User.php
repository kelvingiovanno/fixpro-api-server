<?php

namespace App\Models;

use App\Enums\UserRoleEnum;

use App\Models\Enums\UserRole;
use App\Models\Enums\Speciality;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class User extends Model
{
    use HasFactory;

    public $incrementing = false; 
    protected $keyType = 'string'; 

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) 
        {

            // Set default role for the user
            $user->role_id = UserRoleEnum::MEMBER;
            
            // Set the 'member_since' field to the current date and time
            $user->member_since = now();
            
            // Set 'member_until' field to a year from now
            $user->member_until = now()->addYear();
        
            // Generate and assign a UUID if it's not already set
            if (!$user->getKey()) {
                $user->{$user->getKeyName()} = (string) Str::uuid();
            }
            
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

    public function specialities()
    {
        return $this->belongsToMany(Speciality::class);
    }
}
