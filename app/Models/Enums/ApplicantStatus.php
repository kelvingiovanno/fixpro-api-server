<?php

namespace App\Models\Enums;

use App\Models\Applicant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicantStatus extends Model
{
    use SoftDeletes;

    protected $table = 'applicant_statuses';

    protected $fillable = [
        'id',
        'label',
    ];

    protected $hidden = [
        'id',
        'deleted_at',
    ];

    public $timestamps = false;

    public function applicants()
    {
        return $this->hasMany(Applicant::class, 'status_id', 'id');
    }
}