<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/9
 * Time: 1:15
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class Role extends Model
{
    //this is not exists
    const TABLE_NAME = 'roles';

    public function morphUser()
    {
        return $this->morphOne(User::class, 'role');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function profile()
    {
        return $this->belongsTo(UserProfile::class, 'user_id');
    }
}