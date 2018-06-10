<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/8
 * Time: 13:54
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Illuminate\Foundation\Testing\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * 用户-角色资源表示
 * @Resource("Users 角色", uri="/users/{user}/role")
 */
class UserRoleController extends Controller
{
    const VERSION = 'v1';

    /**
     * 获取一个用户角色的uri
     *
     * @Versions({"v1"})
     * @GET("/")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"id":3439,"type":"admin","uri":"http:\/\/api.ocicat.dev\/admins\/3439"}),
     *     @Response(400, body={"message":"User's role not exists.","status_code":"400"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     * })
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function get(User $user)
    {
        //can't find user's role
        if (! $role = $user->role) throw new BadRequestHttpException('User\'s role not exists.');

        $role_uri = route_api($role->getMorphClass() . '.get', self::VERSION, ['id' => $user->role_id]);

        /** @noinspection PhpUndefinedMethodInspection */
        return $this->response->array([
            'id' => $user->role_id,
            'type' => $role->getMorphClass(),
            'uri' => $role_uri
        ]);
    }

    /**
     * 为用户附加一个角色
     *
     * @Versions({"v1"})
     * @PUT("/")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer", required=true),
     *      @Parameter("role_id", description="角色id", type="integer", required=true),
     *      @Parameter("role_type", description="角色类型", type="string", required=true)
     * })
     * @Transaction({
     *     @Request({"data": {"role_id": 123, "role_type": "admin"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(400, body={"message":"Missing field.","status_code":"400"}),
     *     @Response(400, body={"message":"Wrong role type.","status_code":"400"}),
     *     @Response(400, body={"message":"Role not found.","status_code":"400"}),
     *     @Response(400, body={"message":"This role has already been attached.","status_code":"400"}),
     *     @Response(409, body={"message":"User already attached an role.","status_code":"409"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Illuminate\Foundation\Testing\HttpException
     */
    public function attach(Request $request, User $user)
    {
        $data = $this->data($request);
        list($id, $type) = [$data['role_id'], $data['role_type']];

        if (!$id || !$type) throw new BadRequestHttpException('Missing field.');

        if (!array_key_exists($type, User::ROLE_TYPES))
            throw new BadRequestHttpException('Wrong role type.');

        if ($user->role_id) throw new ConflictHttpException('User already attached an role.');

        $role_class = User::ROLE_TYPES[$type];

        /** @noinspection PhpUndefinedMethodInspection */
        if (! $role = $role_class::find($id))
            throw new BadRequestHttpException('Role not found.');

        if ($role->user_id)
            throw new BadRequestHttpException('This role has already been attached.');

        if ($user->attachRole($role))
            return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));

        throw new HttpException(500);
    }

    /**
     * 为用户解除角色附加
     *
     * @Versions({"v1"})
     * @DELETE("/")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer", required=true)
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"})
     * })
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function detach(User $user)
    {
        if ($user->detachRole()) {
            return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));
        }

        throw new BadRequestHttpException();
    }
}
