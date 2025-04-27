<?php

namespace App\Models\Enums;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRole extends Model
{
    use SoftDeletes;
    
    protected $table = 'users_role';

    protected $fillable = [
        'id',
        'label',
    ]; 

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $timestamps = false;

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}