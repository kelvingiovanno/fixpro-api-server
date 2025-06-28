<?php

namespace App\Models;

use App\Models\Enums\MemberRole;
use App\Models\Enums\TicketIssueType;
use App\Models\Enums\MemberCapability;

use App\Models\Applicant;
use App\Models\RefreshToken;
use App\Models\TicketLog;
use App\Models\TicketIssue;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Member extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'members';

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'member_since' => 'datetime',
        'member_until' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public $timestamps = false;
    public $incrementing = false; 
    protected $keyType = 'string'; 

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) 
        {            
            $model->member_since = now();
            $model->member_until = now()->addYear();

            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }

            static::deleting(function ($member) {
                $member->refresh_token()->delete();
            });
            
        });
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'member_id', 'id');
    }

    public function assessed_tickets() 
    {
        return $this->hasMany(Ticket::class, 'assessed_by', 'id');
    }

    public function evaluated_tickets()
    {
        return $this->hasMany(Ticket::class, 'evaluated_by', 'id');
    }
    
    public function maintained_tickets()
    {
        return $this->belongsToMany(TicketIssue::class, 'maintainers', 'member_id', 'ticket_issue_id');
    }
    
    public function ticket_logs()
    {
        return $this->hasMany(TicketLog::class, 'member_id', 'id');
    }

    public function applicant()
    {
        return $this->hasOne(Applicant::class, 'member_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(MemberRole::class, 'role_id', 'id');
    }

    public function specialities()
    {
        return $this->belongsToMany(TicketIssueType::class, 'specialties', 'member_id', 'issue_id');
    }

    public function capabilities()
    {
        return $this->belongsToMany(MemberCapability::class, 'capabilities', 'member_id' ,'capability_id');
    }

    public function refresh_token()
    {
        return $this->hasOne(RefreshToken::class, 'member_id', 'id');
    }
}
