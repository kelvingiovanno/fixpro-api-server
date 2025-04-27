<?php

namespace App\Models;

use App\Models\Enums\UserRole;
use App\Models\Enums\Speciality;

use App\Models\RefreshToken;
use App\Models\UserData;
use App\Models\TicketLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'role_id',
        'name',
        'title',
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

        static::creating(function ($user) 
        {            
            $user->member_since = now();
            
            $user->member_until = now()->addYear();
        
            if (!$user->getKey()) {
                $user->{$user->getKeyName()} = (string) Str::uuid();
            }
            
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id', 'id');
    }

    public function maintainedTickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_maintenance_staffs', 'user_id', 'ticket_id');
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
        return $this->belongsToMany(Speciality::class, 'speciality_user', 'user_id', 'speciality_id');
    }

    public function refreshToken()
    {
        return $this->hasOne(RefreshToken::class, 'user_id', 'id');
    }

    public function ticketLogs()
    {
        return $this->hasMany(TicketLog::class, 'user_id', 'id');
    }
}
