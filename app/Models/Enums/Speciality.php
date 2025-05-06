<?php

namespace App\Models\Enums;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


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

    public $incrementing = false; 
    public $timestamps = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if(! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'speciality_user', 'speciality_id', 'user_id');
    }
}