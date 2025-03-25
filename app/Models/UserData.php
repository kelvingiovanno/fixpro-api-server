<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UserData extends Model
{
    protected $table = 'users_data';

    protected $guarded = ['id', 'created_at', 'updated_at'];
    
    public static function getColumnNames()
    {
        $columns = Schema::getColumnListing((new self)->getTable());
        return array_values(array_diff($columns, ['id', 'created_at', 'updated_at']));
    }
}
