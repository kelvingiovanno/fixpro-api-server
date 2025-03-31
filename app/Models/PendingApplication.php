<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingApplication extends Model
{
    protected $table = 'pending_applications';
    protected $guarded = ['id', 'created_at', 'updated_at'];
}
