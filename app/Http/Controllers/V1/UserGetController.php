<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/8
 * Time: 13:54
 */

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use App\Transformers\UserTransformer;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;

/**
 * 用户资源表示
 * @Resource("Users", uri="/users")
 */
class UserGetController extends Controller
{
    const VERSION = 'v1';

    /**
     * 使用选择的id来查询用户
     *
     * @Versions({"v1"})
     * @GET("/{user}")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(200, body={"data":{"id":6179,"uuid":"8758c471-f7e3-11e6-ab55-080027b55b5e","username":"tristian.aufderhar","email":"leanna19@kilback.com","role_id":null,"role_type":null,"status":"active","profile":{"nickname":null,"realname":null,"age":null,"gender":"secret","phone":null}}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param User $user
     * @return Response
     */
    public function get(User $user)
    {
        return $this->response->item($user, new UserTransformer);
    }

    /**
     * 搜索用户
     *
     * 可在url中使用username, email, uuid,status, role_type, role_id,nickname, realname, age, gender, phone
     * 可多个条件组合使用，此时返回的data中是数组
     *
     * @Versions({"v1"})
     * @GET("/")
     * @Parameters({
     *      @Parameter("username", description="用户名", type="string"),
     *      @Parameter("email", description="Email", type="string"),
     *      @Parameter("uuid", description="uuid", type="string"),
     *      @Parameter("status", description="用户状态", type="string"),
     *      @Parameter("role_type", description="用户角色类型", type="string"),
     *      @Parameter("role_id", description="用户角色id", type="integer"),
     *      @Parameter("nickname", description="用户昵称", type="string"),
     *      @Parameter("realname", description="用户真名", type="string"),
     *      @Parameter("age", description="用户年龄", type="string"),
     *      @Parameter("gender", description="用户性别", type="string"),
     *      @Parameter("phone", description="用户电话号码", type="string"),
     * })
     * @Transaction({
     *     @Request("username={username}&email={email}&uuid={uuid}&status={status}&role_type={role_type}&role_id={role_id}&nickname={nickname}&realname={realname}&age={age}&gender={gender}&phone={phone}", contentType="application/x-www-form-urlencoded"),
     *     @Response(200, body={"data":{"id":6179,"uuid":"8758c471-f7e3-11e6-ab55-080027b55b5e","username":"tristian.aufderhar","email":"leanna19@kilback.com","role_id":null,"role_type":null,"status":"active","profile":{"nickname":null,"realname":null,"age":null,"gender":"secret","phone":null}}}),
     *     @Response(204),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     */
    public function search(Request $request)
    {
        $query = array_filter_keys($request->query(), User::QUERYABLE);
        $profile_query = array_filter_keys($request->query(), UserProfile::QUERYABLE);

        if (empty($query) && empty($profile_query)) return $this->response->noContent();

        $q = User::query();

        if (!empty($profile_query)) {
            $query = array_merge($query, $profile_query);
            $q->join('user_profiles', 'users.id', '=', 'user_profiles.user_id');
        }

        foreach ($query as $key => $value) {
            $q->where($key, $value);
        }

        $users = $q->get();

        if ($users->isEmpty()) return $this->response->noContent();

        return $this->collection($users, new UserTransformer);
    }

}
