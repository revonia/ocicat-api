<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:42
 */

namespace App\Models\Resources;

use App\Models\Resource;
use App\Models\Roles\Student;
use App\Transformers\ClassnTransformer;
use Illuminate\Database\Query\Builder;

/**
 * Class Classn
 *
 * @package App\Models\Resources
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Roles\Student[] $students
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Course[] $courses
 * @mixin \Eloquent
 * @property integer $id
 * @property string $uuid
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Classn whereId($value)
 * @method static Builder|Classn whereUuid($value)
 * @method static Builder|Classn whereDeletedAt($value)
 * @method static Builder|Classn whereCreatedAt($value)
 * @method static Builder|Classn whereUpdatedAt($value)
 */
class Classn extends Resource
{
    const TRANSFORMER = ClassnTransformer::class;
    const TABLE_NAME = 'classns';

    protected $morphClass = 'classn';
    protected $fillable = ['name'];

    public function students()
    {
        return $this->hasMany(Student::class, 'classn_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_classn', 'classn_id', 'course_id');
    }
}