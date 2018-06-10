<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:43
 */

namespace App\Models\Resources;

use App\Models\AttendancePin;
use App\Models\Resource;
use App\Transformers\LessonTransformer;
use Illuminate\Database\Query\Builder;

/**
 * Class Lesson
 *
 * @package App\Models\Resources
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Resources\Attendance[] $attendances
 * @property-read \App\Models\Resources\Course $course
 * @mixin \Eloquent
 * @property integer $id
 * @property string $uuid
 * @property integer $course_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Lesson whereId($value)
 * @method static Builder|Lesson whereUuid($value)
 * @method static Builder|Lesson whereCourseId($value)
 * @method static Builder|Lesson whereDeletedAt($value)
 * @method static Builder|Lesson whereCreatedAt($value)
 * @method static Builder|Lesson whereUpdatedAt($value)
 */
class Lesson extends Resource
{
    const TRANSFORMER = LessonTransformer::class;
    const TABLE_NAME = 'lessons';

    protected $morphClass = 'lesson';
    protected $fillable = ['mmm'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'lesson_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function getPin()
    {
        if (!$this->exists) return false;

        if (!$attendancePin = AttendancePin::where('lesson_id', $this->id)->first()) {
            return false;
        }
        return $attendancePin->pin;
    }
}