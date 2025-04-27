<?php

namespace App\Models;

use App\Enums\ApplicantStatusEnum;

use App\Models\Enums\ApplicantStatus;

use App\Models\AuthenticationCode;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Applicant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'applicants';
    
    protected $guarded = [
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
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }

            $model->status_id = ApplicantStatusEnum::PENDING->value;
            $model->expires_at = now()->days(2);
        });
    }

    public function status()
    {
        return $this->belongsTo(ApplicantStatus::class, 'status_id', 'id');
    }

    public function authCode()
    {
        return $this->hasOne(AuthenticationCode::class, 'application_id', 'id');
    }
}