<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Resources\Lesson;
use League\Fractal\TransformerAbstract;


class LessonStatTransformer extends TransformerAbstract
{
    public function transform(Lesson $lesson) {

        $course = $lesson->course;
        if ($course === null) return $this->null();
        $courseTf = new CourseTransformer();
        $studentTf = new StudentTransformer();
        $classns = $course->classns;
        $classns->load('students');
        $attendances = $lesson->attendances;

        $ret = [
            'id' => $lesson->id,
            'course' => $courseTf->transform($course),
            'timestamp' => $lesson->updated_at->timestamp,
            'classns' => []
        ];

        foreach ($classns as $classn) {
            $students = $classn->students;
            $students->load('profile');
            $classnData = [
                'id' => $classn['id'],
                'name' => $classn['name'],
                'students' => [],
                'students_count' => $students->count()
            ];

            $attendancesCount = 0;
            foreach ($students as $student) {
                $found = $attendances->whereLoose('student_id', $student->id)->first();
                if ($found !== null) {
                    $verifyTimestamp = $found->updated_at->timestamp;
                    $attendancesCount++;
                } else {
                    $verifyTimestamp = 0;
                }
                $classnData['students'][] = array_merge(
                    $studentTf->transform($student),
                    ['verify_timestamp' => $verifyTimestamp]
                );
            }
            $classnData['attendances_count'] = $attendancesCount;
            $ret['classns'][] = $classnData;
        }

        return $ret;
    }
}