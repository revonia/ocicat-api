<?php

namespace App\Http\Requests;

use Dingo\Api\Http\Request;

class UpdateUserRequest extends Request
{
    protected $postRules = [
        'username' => 'required|username',
        'email' => 'required|email',
        'password' => 'required|bcrypt',
    ];

    /**
     * PUT前的验证条件
     * @var array
     */
    protected $putEmailRules = [
        'email' => 'required|email',
    ];

    protected $putPasswordRules = [
        'password' => 'required|bcrypt',
    ];
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            //
        ];
    }
}
