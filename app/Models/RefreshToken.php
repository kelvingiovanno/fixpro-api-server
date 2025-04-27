<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RefreshToken extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'deleted_at',
    ];

    protected $hidden = [
        'id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}