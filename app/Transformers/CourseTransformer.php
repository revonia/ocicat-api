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


class CourseTransformer extends TransformerAbstract
{
    public function transform(Course $course) {

        if ($course === null) {
            return null;
        }

        //$classn_uri = $role['calssn_id'] ? '/classn/' . $role['calssn_id'] : null;
        return [
            'id' => $course['id'],
            'teacher_id' => $course->teacher_id,
            'name' => $course['name']
        ];
    }
}