<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Resources\Course;
use League\Fractal\TransformerAbstract;


class CourseStatTransformer extends TransformerAbstract
{
    public function transform(Course $course) {

        $lessons = $course->lessons;
        if ($lessons->isEmpty()) return $this->null();
        $lessons->load('attendances');
        $classns = $course->classns;
        $classns->load('students');
        $studentTf = new StudentTransformer();
        $teacherTf = new TeacherTransformer();


        $ret = [
            'id' => $course->id,
            'name' => $course->name,
            'teacher' => $teacherTf->transform($course->teacher),
            'lessons_count' => $lessons->count(),
            'classns' => []
        ];

        foreach ($classns as $classn) {
            $classnData = [
                'id' => $classn['id'],
                'name' => $classn['name'],
                'students' => []
            ];

            $students = $classn->students;
            $students->load('profile');
            foreach ($students as $student) {
                $studentData = $studentTf->transform($student);
                $studentData['lessons'] = [];
                $attendancesCount = 0;
                foreach ($lessons as $lesson) {
                    $found = $student->attendances()->where('lesson_id', $lesson->id)->first();
                    if ($found !== null) {
                        $verifyTimestamp = $found->updated_at->timestamp;
                        $attendancesCount++;
                    } else {
                        $verifyTimestamp = 0;
                    }
                    $studentData['lessons'][] = [
                        'id' => $lesson->id,
                        'start_timestamp' => $lesson->updated_at->timestamp,
                        'verify_timestamp' => $verifyTimestamp
                    ];
                }
                $studentData['attendances_count'] = $attendancesCount;
                $classnData['students'][] = $studentData;
            }

            $ret['classns'][] = $classnData;
        }

        return $ret;
    }
}