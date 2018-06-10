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


class CourseFlatTransformer extends TransformerAbstract
{
    public function transform(Course $course) {

        if ($course === null) {
            return null;
        }
        $teacher = null;
        $teacherModel = $course->teacher;
        if ($teacherModel !== $this->null()) {
            $teacherTf = new TeacherTransformer();
            $teacher = $teacherTf->transform($teacherModel);
        }

        $lessonsModel = $course->lessons;

        $lessons = [];
        if (!$lessonsModel->isEmpty()) {
            $lessonTf = new LessonTransformer();
            foreach ($lessonsModel as $lesson) {
                $lessons[] = $lessonTf->transform($lesson);
            }
        }

        //$classn_uri = $role['calssn_id'] ? '/classn/' . $role['calssn_id'] : null;
        return [
            'id' => $course['id'],
            'teacher' => $teacher,
            'name' => $course['name'],
            'lessons' => $lessons
        ];
    }
}