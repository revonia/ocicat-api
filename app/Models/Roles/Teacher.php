<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:42
 */

namespace App\Models\Roles;

use App\Models\Role;
use App\Transformers\TeacherTransformer;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\Roles\Teacher
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Course[] $courses
 * @property-read \App\Models\User $morphUser
 * @property-read \App\Models\User $user
 * @method static Builder|Teacher whereId($value)
 * @method static Builder|Teacher whereUserId($value)
 * @method static Builder|Teacher whereDeletedAt($value)
 * @method static Builder|Teacher whereCreatedAt($value)
 * @method static Builder|Teacher whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Teacher extends Role
{
    const TABLE_NAME = 'teachers';

    const TRANSFORMER = TeacherTransformer::class;

    protected $fillable = ['employee_number'];

    protected $morphClass = 'teacher';

    public function courses()
    {
        return $this->hasMany('App\Models\Resources\Course', 'teacher_id');
    }
}