<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Resources\Attendance;
use App\Models\Roles\Student;
use League\Fractal\TransformerAbstract;


class StudentStatTransformer extends TransformerAbstract
{
    public function transform(Student $student)
    {
        $classn = $student->classn;
        if ($classn === null) {
            return $this->null();
        }
        $classnTf = new ClassnTransformer();
        $teacherTf = new TeacherTransformer();
        $courses = $classn->courses;
        $courses->load('lessons', 'teacher');
        $attendances = $student->attendances;


        $ret = [
            'id' => $student->id,
            'classn' => $classnTf->transform($classn),
            'student_number' => $student->student_number,
            'courses' => []
        ];


        foreach ($courses as $course) {
            $lessons = $course->lessons;
            $courseData = [
                'id' => $course->id,
                'name' => $course->name,
                'teacher' => ($course->teacher === null) ? null : $teacherTf->transform($course->teacher),
                'lessons' => [],
                'lessons_count' => $lessons->count(),
            ];
            $attendancesCount = 0;
            foreach ($lessons as $lesson) {
                $found = $attendances->whereLoose('lesson_id', $lesson->id)->first();
                if ($found !== null) {
                    $verifyTimestamp = $found->updated_at->timestamp;
                    $attendancesCount++;
                } else {
                    $verifyTimestamp = 0;
                }
                $courseData['lessons'][] = [
                    'start_timestamp' => $lesson->updated_at->timestamp,
                    'verify_timestamp' => $verifyTimestamp,
                ];
            }
            $courseData['attendances_count'] = $attendancesCount;
            $ret['courses'][] = $courseData;
        }

        return $ret;
    }
}