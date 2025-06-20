<?php

namespace App\Models\Enums;

use App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApplicantStatus extends Model
{
    use SoftDeletes;

    protected $table = 'applicant_statuses';

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

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'applicant_id', 'id');
    }
}
