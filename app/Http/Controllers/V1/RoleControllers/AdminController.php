<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 15:22
 */

namespace App\Http\Controllers\V1\RoleControllers;

use App\Http\Controllers\V1\RoleController;
use App\Models\Roles\Admin;
use App\Transformers\AdminTransformer;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Api\Http\Request;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Response;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * 管理员角色资源表示
 * @Resource("Admin", uri="/admins")
 */
class AdminController extends RoleController
{
    const ROLE_TYPE = 'admin';
    const VERSION = 'v1';

    /**
     * 使用选择的id来查询管理员
     *
     * @Versions({"v1"})
     * @GET("/{admin}")
     * @Parameters({
     *      @Parameter("admin", description="管理员id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"role":"admin","group":"my_group"}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Admin $admin
     * @return \Dingo\Api\Http\Response
     */
    public function get(Admin $admin)
    {
        return $this->response->item($admin, new AdminTransformer());
    }

    /**
     * 添加管理员
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Parameters({
     *      @Parameter("group", description="管理员组", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"group":"my_group"}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/admins/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not add admin.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function add(Request $request)
    {
        $data = $this->getData($request);
        $admin = new Admin();
        if (! $admin->fill($data)->save()) {
            throw new DeleteResourceFailedException('Could not add admin.');
        } else {
            return $this->response->created(route_api('admin.get', self::VERSION, ['id' => $admin->id]));
        }
    }

    /**
     * 更新指定管理员
     *
     * @Versions({"v1"})
     * @PUT("/{admin}")
     * @Parameters({
     *      @Parameter("admin", description="管理员id", type="integer"),
     *      @Parameter("group", description="管理员组", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"group":"my_group"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/admins/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(422, body={"message":"Could not update admin.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param Admin $admin
     * @return \Dingo\Api\Http\Response
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     */
    public function update(Request $request, Admin $admin)
    {
        $data = $this->getData($request);

        if (!$admin->fill($data)->save()) {
            throw new DeleteResourceFailedException('Could not update admin.');
        } else {
            return $this->response->accepted(route_api('admin.get', self::VERSION, ['id' => $admin->id]));
        }
    }

    /**
     * 删除指定id的管理员
     *
     * @Versions({"v1"})
     * @DELETE("/{admin}")
     * @Parameters({
     *      @Parameter("admin", description="管理员id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/admins/2589"}),
     *     @Response(422, body={"message":"Could not delete an admin.","status_code":422}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Admin $admin
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     * @throws HttpException
     */
    public function delete(Admin $admin)
    {
        if (!$admin->delete()) {
            throw new DeleteResourceFailedException('Could not delete an admin.');
        } else {
            return $this->response->accepted(route_api('admin.get', self::VERSION, ['id' => $admin->id]));
        }
    }
}