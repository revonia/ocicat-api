<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:45
 */

namespace App\Models\Resources;

use App\Models\AttendancePin;
use App\Models\Resource;
use App\Models\Roles\Teacher;
use App\Transformers\CourseTransformer;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Class Course
 *
 * @package App\Models\Resources
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Lesson[] $lessons
 * @property-read \App\Models\Roles\Teacher $teacher
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Classn[] $classns
 * @mixin \Eloquent
 * @property integer $id
 * @property string $uuid
 * @property integer $teacher_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Course whereId($value)
 * @method static Builder|Course whereUuid($value)
 * @method static Builder|Course whereTeacherId($value)
 * @method static Builder|Course whereDeletedAt($value)
 * @method static Builder|Course whereCreatedAt($value)
 * @method static Builder|Course whereUpdatedAt($value)
 */
class Course extends Resource
{
    const TRANSFORMER = CourseTransformer::class;
    const TABLE_NAME = 'courses';

    protected $morphClass = 'course';
    protected $fillable = ['name'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function classns()
    {
        return $this->belongsToMany(Classn::class, 'course_classn', 'course_id', 'classn_id');
    }

    public function attachToTeacher(Teacher $teacher)
    {
        if (!$teacher->exists) return false;

        if ($this->teacher_id) return false;

        if (! $this->teacher()->associate($teacher)->save()) return false;

        return true;
    }

    public function detachFromTeacher()
    {
        if (! $this->teacher_id) return false;

        if (! $this->teacher()->dissociate()->save()) return false;

        return true;
    }

    public function associateClassn(Classn $classn)
    {
        if (!$classn->exists) return false;

        $this->classns()->attach($classn);

        return true;
    }

    public function addLesson()
    {
        $lesson = new Lesson();
        if (! $lesson->course()->associate($this)->save()) {
            return false;
        }

        $classns = $this->classns;
        $pin = DB::table('pins')->select('pin')->orderByRaw('RAND()')->first()->pin;
        $result = false;
        foreach ($classns as $classn) {
            AttendancePin::where('classn_id', '=', $classn->id)->delete();
            $result = AttendancePin::create([
                'classn_id' => $classn->id,
                'lesson_id' => $lesson->id,
                'pin' => $pin,
            ]);
            if (!$result) break;
        }
        return $result ? [$lesson, $pin] : false;
    }
}