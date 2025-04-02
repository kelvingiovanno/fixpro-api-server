<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthenticationCode extends Model
{
    protected $table = 'authentication_codes';
    protected $fillable = ['code', 'expires_at'];
}
