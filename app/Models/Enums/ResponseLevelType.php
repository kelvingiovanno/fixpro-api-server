<?php

namespace App\Models\Enums;

use App\Models\Ticket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class ResponseLevelType extends Model
{
    use SoftDeletes;

    protected $table = 'response_level_types';

    protected $fillable = [
        'id',
        'label',
        'sla_modifier',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    protected $casts = [
        'sla_modifier' => 'double',
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

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'response_level_type_id', 'id');   
    }
}