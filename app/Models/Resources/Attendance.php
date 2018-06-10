<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/9
 * Time: 16:45
 */

namespace App\Models\Resources;

use App\Models\Resource;
use App\Models\Roles\Student;
use App\Transformers\AttendanceTransformer;
use App\Transformers\StudentTransformer;
use Illuminate\Database\Query\Builder;

/**
 * Class Attendance
 *
 * @package App\Models\Resources
 * @property-read Lesson $lesson
 * @mixin \Eloquent
 * @property integer $id
 * @property string $uuid
 * @property integer $student_id
 * @property integer $lesson_id
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static Builder|Attendance whereId($value)
 * @method static Builder|Attendance whereUuid($value)
 * @method static Builder|Attendance whereStudentId($value)
 * @method static Builder|Attendance whereLessonId($value)
 * @method static Builder|Attendance whereDeletedAt($value)
 * @method static Builder|Attendance whereCreatedAt($value)
 * @method static Builder|Attendance whereUpdatedAt($value)
 */
class Attendance extends Resource
{
    const TRANSFORMER = AttendanceTransformer::class;
    const TABLE_NAME = 'attendances';

    protected $morphClass = 'attendance';
    protected $fillable = ['mmm'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    public static function createWithStudentAndLesson(Student $student, Lesson $lesson)
    {
        if ($student->exists && $lesson->exists) {
            $attendance = new static();
            $attendance->student()->associate($student);
            $attendance->lesson()->associate($lesson);
            if ($attendance->save()) {
                return $attendance;
            }
        }
        return false;
    }
}