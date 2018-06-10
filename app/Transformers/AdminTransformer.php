<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 18:16
 */

namespace App\Transformers;

use App\Models\Roles\Admin;
use League\Fractal\TransformerAbstract;


class AdminTransformer extends TransformerAbstract
{
    public function transform(Admin $role) {

        if ($role === null) {
            return null;
        }

        return [
            'id' => $role['id'],
            'role' => 'admin',
            'group' => $role['group'],
        ];
    }
}