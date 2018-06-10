<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    const QUERYABLE = ['nickname', 'realname', 'age', 'gender', 'phone'];
    const GENDERS = ['secret', 'male', 'female', 'others'];
    const UPDATABLE = ['nickname', 'realname', 'age', 'gender', 'phone'];
    const TABLE_NAME = 'user_profiles';

    protected $fillable = ['nickname', 'realname', 'age', 'gender', 'phone'];
    protected $primaryKey = 'user_id';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
