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


class UserProfileTransformer extends TransformerAbstract
{
    public function transform(UserProfile $profile) {

        return [
            'nickname' => $profile['nickname'],
            'realname' => $profile['realname'],
            'age' => $profile['age'],
            'gender' => $profile['gender'],
            'phone' => $profile['phone'],
        ];
    }
}