<?php

namespace App\Models\Enums;

use App\Models\Member;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MemberCapability extends Model
{
    protected $table = 'member_capabilities';

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

    public function members()
    {
        return $this->belongsToMany(Member::class, 'capabilities', 'capability_id', 'member_id');
    }
}
