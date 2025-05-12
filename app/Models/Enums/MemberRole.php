<?php

namespace App\Models\Enums;

use App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MemberRole extends Model
{
    use SoftDeletes;
    
    protected $table = 'member_roles';

    protected $fillable = [
        'name',
    ]; 

    protected $hidden = [
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
        return $this->hasMany(Member::class, 'role_id', 'id');
    }
}