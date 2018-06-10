<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/5
 * Time: 21:19
 */

namespace app\Validators;

use App\Models\User;
use Illuminate\Validation\Validator;

class FieldValidator extends Validator
{
    /**
     * 验证器规则:username
     * 小写字母开头，以小写字母、数字、下划线_、点. 组成，长度 4-24
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateUsername($attribute, $value, $parameters)
    {
        return is_string($value) && preg_match('/^[a-z][a-z0-9_.]{3,23}$/u', $value);
    }

    /**
     * 验证器规则:uuid
     * 例：6b8ad49d-b058-3c4d-a271-f80e1e28dbbf
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateUuid($attribute, $value, $parameters)
    {
        $regex = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/u';
        return is_string($value) && preg_match($regex, $value);
    }

    /**
     * 验证器规则:password bcrypt
     * 例：$2y$10$nObdmv8v2GzIOsdR5RYc3eG3ML6/VOgBVcKqtaX0m3qX8fg9FSW0y
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateBcrypt($attribute, $value, $parameters)
    {
        $regex = '/^\$2[ayb]\$.{56}$/u';
        return is_string($value) && preg_match($regex, $value);
    }

    /**
     * 验证器规则:status
     * 例:active、suspend （常量）
     *
     * @param $attribute
     * @param $value
     * @param $parameters
     * @return bool
     */
    public function validateStatus($attribute, $value, $parameters)
    {
        return is_string($value) && in_array($value, User::STATUS);
    }

}