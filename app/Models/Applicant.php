<?php

namespace App\Models;

use App\Enums\ApplicantStatusEnum;

use App\Models\Enums\ApplicantStatus;

use App\Models\Member;
use App\Models\AuthenticationCode;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Applicant extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'applicants';

    protected $fillable = [
        'member_id',
        'status_id',
    ];

    protected $casts = [
        'expires_on' => 'datetime',
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
    
            $model->expires_on = now()->addWeek();

            if(! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('c');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'id');
    }

    public function status()
    {
        return $this->belongsTo(ApplicantStatus::class, 'status_id', 'id');
    }

    public function authentication_code()
    {
        return $this->hasOne(AuthenticationCode::class, 'application_id', 'id');
    }
}
