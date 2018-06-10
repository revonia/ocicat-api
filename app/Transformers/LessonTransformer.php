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


class LessonTransformer extends TransformerAbstract
{
    public function transform(Lesson $lesson) {

        if ($lesson === null) {
            return null;
        }

        //$classn_uri = $res['calssn_id'] ? '/classn/' . $res['calssn_id'] : null;
        return [
            'id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'timestamp' => $lesson->updated_at->timestamp
        ];
    }
}