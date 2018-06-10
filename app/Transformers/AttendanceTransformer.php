<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Resources\Attendance;
use League\Fractal\TransformerAbstract;


class AttendanceTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance)
    {

        if ($attendance === null) {
            return null;
        }
//        $student_uri = $attendance['student_id'] ? '/students/' . $attendance['student_id'] : null;
//        $lesson_uri = $attendance['lesson_id'] ? '/lessons/' . $attendance['lesson_id'] : null;

        $lesson = null;
        if ($attendance->lesson) {
            $lessonTf = new LessonTransformer();
            $lesson = $lessonTf->transform($attendance->lesson);
        }

        return [
            'id' => $attendance['id'],
            'student_id' => $attendance['student_id'],
            'lesson' => $lesson,
//            'student_uri' => $student_uri,
//            'lesson_uri'  => $lesson_uri,
            'timestamp' => $attendance->updated_at->timestamp
        ];
    }
}