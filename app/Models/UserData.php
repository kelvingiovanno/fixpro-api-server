<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class UserData extends Model
{
    use HasFactory;

    protected $table = 'users_data';

    protected $guarded = ['id'];

    public $timestamps = false;
    
    public static function getColumnNames()
    {
        $columns = Schema::getColumnListing((new self)->getTable());
        return array_values(array_diff($columns, ['id', 'user_id']));
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}