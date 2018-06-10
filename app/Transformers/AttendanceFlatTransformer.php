<?php
/**
 * Auto generate source code header.
 * Original File Name: AttendanceDetailTransformer.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 15:12
 */

namespace App\Transformers;


use App\Models\Resources\Attendance;
use League\Fractal\TransformerAbstract;

class AttendanceFlatTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance)
    {
        if ($attendance === null) {
            return null;
        }
        $student = null;
        if ($studentObj = $attendance->student) {
            $studentTf = new StudentTransformer();
            $student = $studentTf->transform($studentObj);
        }

        $lesson = null;
        if ($lessonObj = $attendance->lesson) {
            $lessonTf = new LessonTransformer();
            $lesson = $lessonTf->transform($lessonObj);
        }

        return [
            'id' => $attendance['id'],
            'student' => $student,
            'lesson'  => $lesson,
            'timestamp' => $attendance->updated_at->timestamp
        ];
    }
}