<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserData extends Model
{
    use HasFactory;

    protected $table = 'users_data';

    protected $guarded = ['id'];

    public $timestamps = false;
    
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}