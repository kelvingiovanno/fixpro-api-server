<?php

namespace App\Models\Enums;

use App\Models\Member;
use App\Models\TicketIssue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TicketIssueType extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_issue_types';

    protected $fillable = [
        'id',
        'name',
        'sla_duration_hour',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'sla_hours' => 'integer',
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

    public function ticket_issues()
    {
        return $this->hasMany(TicketIssue::class, 'issue_id', 'id');
    }

    public function specialities()
    {
        return $this->belongsToMany(Member::class, 'specialties', 'issue_id', 'member_id');
    }
}