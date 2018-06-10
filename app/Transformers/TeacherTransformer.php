<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Roles\Teacher;
use App\Models\UserProfile;
use League\Fractal\TransformerAbstract;


class TeacherTransformer extends TransformerAbstract
{
    public function transform(Teacher $role) {

        if ($role === null) {
            return null;
        }
        if ($user_id = $role['user_id']) {
            $profileObj = UserProfile::find($user_id);
            $profileTransformer = new UserProfileTransformer();
            $profile = $profileTransformer->transform($profileObj);
        } else {
            $profile = null;
        }

        return [
            'id' => $role['id'],
            'role' => 'teacher',
            'employee_number' => $role['employee_number'],
            'profile' => $profile
        ];
    }
}