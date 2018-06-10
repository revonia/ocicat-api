<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use League\Fractal\TransformerAbstract;


class ClassnTransformer extends TransformerAbstract
{
    public function transform(Classn $classn) {
        $studentCount = Student::where('classn_id', $classn->id)->count();
        if ($classn === null) {
            return null;
        }
        //$classn_uri = $role['calssn_id'] ? '/classn/' . $role['calssn_id'] : null;
        return [
            'id' => $classn['id'],
            'name' => $classn['name'],
            'student_count' => $studentCount
        ];
    }
}