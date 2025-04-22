<?php

namespace App\Models\Enums;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    protected $table = 'specialities';

    protected $fillable = [
        'id',
        'label',
    ];

    public $timestamps = false;

    public function users()
    {
        return $this->belongsToMany(User::class, 'speciality_user', 'speciality_id', 'user_id');
    }
}
