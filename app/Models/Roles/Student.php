<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:40
 */

namespace App\Models\Roles;

use App\Models\AttendancePin;
use App\Models\Resources\Classn;
use App\Models\Role;
use App\Transformers\StudentTransformer;
use App\Transformers\UserTransformer;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * App\Models\Roles\Student
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $classn_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Attendance[] $attendances
 * @property-read \App\Models\Resources\Classn $classn
 * @property-read \App\Models\User $morphUser
 * @property-read \App\Models\User $user
 * @method static Builder|Student whereId($value)
 * @method static Builder|Student whereUserId($value)
 * @method static Builder|Student whereClassnId($value)
 * @method static Builder|Student whereDeletedAt($value)
 * @method static Builder|Student whereCreatedAt($value)
 * @method static Builder|Student whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Student extends Role
{
    protected $morphClass = 'student';
    const TABLE_NAME = 'students';

    protected $fillable = ['student_number'];

    const TRANSFORMER = StudentTransformer::class;

    public function attendances()
    {
        return $this->hasMany('App\Models\Resources\Attendance', 'student_id');
    }

    public function classn()
    {
        return $this->belongsTo('App\Models\Resources\Classn', 'classn_id');
    }


    public function attachToClassn(Classn $classn)
    {
        if (!$classn->exists) return false;

        if ($this->classn_id) return false;

        if (! $this->classn()->associate($classn)->save()) return false;

        return true;
    }

    public function detachFromClassn()
    {
        if (! $this->classn_id) return false;

        if (! $this->classn()->dissociate()->save()) return false;

        return true;
    }

    public function verifyPin($pin)
    {
        if ($lesson_id = AttendancePin::verify($this->classn_id, $pin)) {
            return $lesson_id;
        }

        return false;
    }
}