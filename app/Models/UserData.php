<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserData extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'users_data';

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        'id', 
        'user_id'
    ];

    public $timestamps = false;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}