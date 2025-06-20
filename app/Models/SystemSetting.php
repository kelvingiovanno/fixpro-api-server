<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = [
        'key', 
        'value',
    ];

    protected $hidden = [
        'id',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {    
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            } 
        });
    }

    public static function get(string $key, $default = null)
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function put(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
    }
}