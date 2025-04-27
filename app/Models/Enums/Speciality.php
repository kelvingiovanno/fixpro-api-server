<?php

namespace App\Models\Enums;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Speciality extends Model
{
    use SoftDeletes;

    protected $table = 'specialities';

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
        return $this->belongsToMany(User::class, 'speciality_user', 'speciality_id', 'user_id');
    }
}