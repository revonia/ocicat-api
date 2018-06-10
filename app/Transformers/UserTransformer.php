<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;


class UserTransformer extends TransformerAbstract
{
    public function transform(User $user) {
        if ($user === null) {
            return null;
        }
        $profileTransformer = new UserProfileTransformer();
        $profile = $profileTransformer->transform($user->profile);
        return [
            'id' => $user['id'],
            'uuid' => $user['uuid'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'role_type' => $user['role_type'],
            'status' => $user['status'],
            'profile' => $profile
        ];

    }
}