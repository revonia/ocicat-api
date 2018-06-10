<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/11/8
 * Time: 13:54
 */

namespace App\Http\Controllers\V1;

use App\Exceptions\ResourceConflictedException;
use App\Http\Controllers\Controller;
use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use App\Models\User;
use App\Models\UserProfile;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Faker\Factory;

/**
 * 用户资源表示
 * @Resource("Users", uri="/users")
 */
class UserModifyController extends Controller
{
    const VERSION = 'v1';

    /**
     * 添加一个用户
     *
     * 字段验证将在User模型内完成
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Parameters({
     *      @Parameter("username", description="用户名，^[a-z][a-z0-9_.]{3,23}", type="string", required=true),
     *      @Parameter("email", description="用户邮箱，邮箱格式", type="string", required=true),
     *      @Parameter("password", description="用户密码", type="string", required=true),
     * })
     * @Transaction({
     *     @Request({"data":{"username":"example","email": "example@mail.com","password":"my_password"}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(409, body={"message":"Conflicted.","status_code":"409","errors":{"username":"..."}}),
     *     @Response(422, body={"message":"Could not create new user.","status_code":422,"errors":{"username":"The username field is required."}}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function add(Request $request)
    {
        $data = array_filter_keys($this->data($request), ['username', 'email', 'password', 'is_demo']);

        if (isset($data['password']) && $data['password']) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);

        //successful create user
        if ($user->wasRecentlyCreated)
        {
            if (isset($data['is_demo']) && $data['is_demo'] === true ) {
                $this->createDemoUser($user);
            }
            return $this->response->created(
                route_api('user.get', self::VERSION, ['id' => $user->id]));
        }

        //username or email conflicted.
        if ($user->isConflicted()) throw new ResourceConflictedException('Conflicted.',$user->errors());

        //不能通过字段验证
        if ($user->validatorFailed()) throw new StoreResourceFailedException(
            'Could not create new user.', $user->errors());

        //unknown error
        throw new HttpException(500);
    }

    /**
     * 删除指定id的用户
     *
     * @Versions({"v1"})
     * @DELETE("/{user}")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer", required=true),
     * })
     * @Transaction({
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(422, body={"message":"Could not delete a user.","status_code":422,"errors":{"...":"..."}}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param User $user
     * @return Response
     * @throws \Dingo\Api\Exception\DeleteResourceFailedException
     * @throws \Exception
     */
    public function delete(User $user)
    {
        //can't delete user
        if (! $user->delete())
            throw new DeleteResourceFailedException('Could not delete a user.');

        //successful delete
        return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));
    }


    /**
     * 更新指定用户
     *
     * @Versions({"v1"})
     * @PUT("/{user}")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer"),
     *      @Parameter("username", description="用户名，^[a-z][a-z0-9_.]{3,23}", type="string", required=true),
     *      @Parameter("email", description="用户邮箱，邮箱格式", type="string"),
     *      @Parameter("password", description="用户密码", type="string"),
     *      @Parameter("status", description="用户状态，枚举[active,suspend]", type="string"),
     * })
     * @Transaction({
     *     @Request({"data":{"username":"example","email": "example@mail.com","password":"my_password", "status":"active"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(409, body={"message":"Conflicted.","status_code":"409","errors":{"username":"..."}}),
     *     @Response(422, body={"message":"Could not update user.","status_code":422,"errors":{"...":"..."}}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     * @throws \App\Exceptions\ResourceConflictedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function update(Request $request, User $user)
    {
        $data = array_filter_keys($this->data($request), User::UPDATABLE);

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if (empty($data)) throw new BadRequestHttpException();

        if ($user->update($data))
            return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));

        //username or email conflicted.
        if ($user->isConflicted()) throw new ResourceConflictedException('Conflicted.', $user->errors());

        //不能通过字段验证
        if ($user->validatorFailed()) throw new StoreResourceFailedException(
            'Could not update user.', $user->errors());

        //unknown error
        throw new HttpException(500);
    }

    /**
     * 更新指定用户信息
     *
     * @Versions({"v1"})
     * @PUT("/{user}/profile")
     * @Parameters({
     *      @Parameter("user", description="用户id", type="integer"),
     * })
     * @Transaction({
     *     @Request({"data":{"nickname":"example","realname": "my name","age":18,"gender":"male","phone":"12345678"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @param User $user
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function updateProfile(Request $request, User $user)
    {
        $data = array_filter_keys($this->data($request), UserProfile::UPDATABLE);

        if (empty($data)) throw new BadRequestHttpException();

        $profile = $user->profile;

        if ($profile->update($data))
            return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));

//        //不能通过字段验证
//        if ($profile->validatorFailed()) throw new StoreResourceFailedException(
//            'Could not update user's profile.', $user->errors());

        //unknown error
        throw new HttpException(500);
    }

    protected function createDemoUser(User $user)
    {
        $faker = Factory::create('zh_CN');

        $data = [
            'nickname' => $faker->userName,
            'realname' => $faker->name,
            'age' => mt_rand(18, 25),
            'gender' => UserProfile::GENDERS[mt_rand(0, 2)],
            'phone' => $faker->phoneNumber,
        ];

        $user->profile->update($data);
        $classn = Classn::find(10);

        $student = new Student([
            'student_number' => $faker->postcode
        ]);

        $student->classn()->associate($classn);

        $student->save();

        $user->attachRole($student);
    }
}
