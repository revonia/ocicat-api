<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/4
 * Time: 15:22
 */

namespace App\Http\Controllers\V1;

use App\Exceptions\ResourceConflictedException;
use App\Http\Controllers\Controller;
use App\Models\Resources\Attendance;
use App\Models\Roles\Student;
use App\Models\User;
use App\Models\UserProfile;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use App\Transformers\UserTransformer;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Blueprint\Annotation\Parameter;
use Dingo\Blueprint\Annotation\Parameters;
use Dingo\Blueprint\Annotation\Resource;
use Dingo\Blueprint\Annotation\Transaction;
use Dingo\Blueprint\Annotation\Versions;
use Illuminate\Auth\Access\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JWTAuth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Dingo\Api\Auth\Auth;

/**
 * 当前用户资源表示
 * @Resource("Me", uri="/me")
 */
class MeController extends Controller
{
    const VERSION = 'v1';

    protected function getAuthenticatedUser()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            throw new NotFoundHttpException();
        }

        return $user;
    }

    /**
     * 获取token
     *
     * 用用户名或邮箱，加上密码获取token
     *
     * @Versions({"v1"})
     * @POST("/")
     * @Parameters({
     *      @Parameter("username", description="用户名，^[a-z][a-z0-9_.]{3,23}", type="string", required=false),
     *      @Parameter("email", description="用户邮箱，邮箱格式", type="string", required=false),
     *      @Parameter("password", description="用户密码", type="string", required=true),
     * })
     * @Transaction({
     *     @Request({"data":{"username":"example","email": "example@mail.com","password":"my_password"}}),
     *     @Response(200, body={"token":"..."}),
     *     @Response(404, body={"message":"","status_code":"404"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function authenticate(Request $request)
    {
        $credentials = array_filter_keys($this->data($request), ['username','email', 'password']);

        if(isset($credentials['username'])) unset($credentials['email']);

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                throw new UnauthorizedHttpException('');
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            throw new HttpException(500);
        }

        // all good so return the token
        return $this->response->array(compact('token'));
    }

    /**
     * 更新token
     *
     * token将在头部返回
     *
     * @Versions({"v1"})
     * @GET("/refresh_token")
     * @Transaction({
     *     @Request(headers={"Authorization": " Bearer [user's token here]"}),
     *     @Response(200, headers={"Authorization": " Bearer [your token here]"}),
     *     @Response(404, body={"message":"","status_code":"404"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     */
    public function refresh() {}

    /**
     * 获取当前用户的信息
     *
     * @Versions({"v1"})
     * @GET("/")
     * @Transaction({
     *     @Request(headers={"Authorization": " Bearer [user's token here]"}),
     *     @Response(200, body={"data":{"id":6179,"uuid":"8758c471-f7e3-11e6-ab55-080027b55b5e","username":"tristian.aufderhar","email":"leanna19@kilback.com","role_id":null,"role_type":null,"status":"active","profile":{"nickname":null,"realname":null,"age":null,"gender":"secret","phone":null}}}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @return Response
     */
    public function get()
    {
        $user = $this->getAuthenticatedUser();
        return $this->response->item($user, new UserTransformer);
    }

    /**
     * 更新当前用户邮箱
     *
     * @Versions({"v1"})
     * @PUT("/email")
     * @Parameters({
     *      @Parameter("email", description="用户邮箱，邮箱格式", type="string", required=true),
     * })
     * @Transaction({
     *     @Request({"data": {"email":"new_mail@mail.com"}},headers={"Authorization": " Bearer [user's token here]"}),
     *     @Response(202,headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Exception
     * @throws \App\Exceptions\ResourceConflictedException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     */
    public function updateEmail(Request $request)
    {
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

        $data = array_filter_keys($this->data($request), ['email']);

        if (empty($data)) throw new BadRequestHttpException();

        if ($user->updateEmail($data['email']))
            return $this->response->accepted(route_api('user.get', self::VERSION, ['id' => $user->id]));

        if ($user->isConflicted()) throw new ResourceConflictedException('Conflicted.', $user->errors());

        if ($user->validatorFailed()) throw new StoreResourceFailedException(
            'Could not update user\'s email.', $user->errors());

        //unknown error
        throw new HttpException(500);
    }

    /**
     * 更新当前用户的密码
     *
     * @Versions({"v1"})
     * @PUT("/password")
     * @Parameters({
     *      @Parameter("old_password", description="用户密码，原密码", type="string", required=true),
     *      @Parameter("password", description="用户新密码", type="string", required=true),
     * })
     * @Transaction({
     *     @Request({"data": {"old_password":"password","password":"my_password"}},headers={"Authorization": "Bearer [user's token here]"}),
     *     @Response(202,headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(404, body={"message":"404 Not Found","status_code":"404"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     *
     * @param Request $request
     * @return Response
     * @throws \Exception
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Dingo\Api\Exception\StoreResourceFailedException
     * @throws \Exception
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws UnauthorizedException
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

        $data = array_filter_keys($this->data($request), ['old_password', 'password']);

        if (count($data) != 2) throw new BadRequestHttpException();

        if (!\Hash::check($data['old_password'], $user->password)) throw new UnauthorizedException();

        if($user->updatePassword($data['password'])) {
            return $this->response->accepted(
                route_api('user.get', self::VERSION, ['id' => $user->id]));
        }

        if ($user->validatorFailed()) throw new StoreResourceFailedException(
            'Could not update user\'s password.', $user->errors());

        //unknown error
        throw new HttpException(500);
    }

    /**
     * 更新指定用户信息
     *
     * @Versions({"v1"})
     * @PUT("/profile")
     * @Transaction({
     *     @Request({"data":{"nickname":"example","realname": "my name","age":18,"gender":"male","phone":"12345678"}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/users/2589"}),
     *     @Response(400, body={"message":"","status_code":"400"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     *
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function updateProfile(Request $request)
    {
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

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

    /**
     * 获取用户角色
     *
     * @Versions({"v1"})
     * @GET("/role")
     * @Transaction({
     *     @Response(200, body={"id":"12","user_id": "34","...":"..."}),
     *     @Response(404, body={"message":"User's role not exists.","status_code":"404"}),
     *     @Response(404, body={"message":"","status_code":"400"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function getRole()
    {
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

        //can't find user's role
        if (! $role = $user->role) throw new NotFoundHttpException('User\'s role not exists.');

        if (!empty($role::TRANSFORMER)) {
            return $this->response->item($role, $role::TRANSFORMER);
        }
        throw new HttpException(500);
    }

    /**
     * 为当前用户添加角色
     *
     * 仅在新用户绑定角色时适用
     *
     * @Versions({"v1"})
     * @POST("/role")
     * @Transaction({
     *     @Request({"data":{"role_type":"teacher","...":"..."}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/teacher/2589"}),
     *     @Response(409, body={"message":"User's role exists.","status_code":"409"}),
     *     @Response(404, body={"message":"","status_code":"400"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\ConflictHttpException
     */
    public function addRole(Request $request)
    {
        $user = app(Auth::class)->user();
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

        //User's role exists
        if ($user->role_id) throw new ConflictHttpException('User\'s role exists.');

        $data = $this->data($request);

        $role_type = getvar($data['role_type'], '');

        if (!in_array($role_type, ['student', 'teacher'], true)) {
            throw new BadRequestHttpException('Wrong role type.');
        }

        $class = User::ROLE_TYPES[$role_type];
        /** @noinspection PhpUndefinedMethodInspection */
        $role = $class::create($data);

        if (!$role) throw new HttpException(500, 'Can not create role.');

        if (!$user->attachRole($role)) {
            throw new HttpException(500, 'Can not attach role');
        }

        return $this->response->created(route_api($role_type . '.get', self::VERSION, ['id' => $role->id]));
    }

    /**
     * 为当前用户更新角色
     *
     * @Versions({"v1"})
     * @PUT("/role")
     * @Transaction({
     *     @Request({"data":{"...":"..."}}),
     *     @Response(202, headers={"location": "http://api.ocicat.dev/.../2589"}),
     *     @Response(409, body={"message":"User's role exists.","status_code":"409"}),
     *     @Response(404, body={"message":"","status_code":"400"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function updateRole(Request $request)
    {
        /** @var User $user */
        $user = $this->getAuthenticatedUser();

        //can't find user's role
        if (! $role = $user->role) throw new NotFoundHttpException('User\'s role not exists.');

        $role_type = $role->getMorphClass();
        if (!in_array($role_type, ['student', 'teacher'], true)) {
            throw new BadRequestHttpException('Wrong role type.');
        }

        $data = $this->data($request);

        if (!$role->fill($data)->save())
            throw new HttpException(500, 'Can not update role.');

        return $this->response->accepted(route_api($role_type . '.get', self::VERSION, ['id' => $role->id]));
    }

    /**
     * 学生提交pin签到验证码
     *
     * @Versions({"v1"})
     * @POST("/pin")
     * @Transaction({
     *     @Request({"data":{"pin":123123}}),
     *     @Response(201, headers={"location": "http://api.ocicat.dev/attendances/2589"}),
     *     @Response(403, body={"message":"User's role exists.","status_code":"409"}),
     *     @Response(404, body={"message":"","status_code":"400"}),
     *     @Response(401, body={"message":"","status_code":"401"}),
     *     @Response(500, body={"message":"","status_code":"500"})
     * })
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function verifyPin(Request $request)
    {
        $data = $this->getData($request);
        if (!isset($data['pin']) || !is_int($data['pin'])) {
            throw new BadRequestHttpException('Missing key: pin.');
        }

        /** @var User $user */
        $user = $this->getAuthenticatedUser();
        if ($user->role_type !== 'student') {
            throw new HttpException(404, 'Only Student allow.');
        }

        /** @var Student $student */
        $student = $user->role;

        if (!$lesson_id = $student->verifyPin($data['pin'])) {
            throw new HttpException(403, 'Wrong pin');
        }
        Attendance::unguard();


        $attendance = Attendance::firstOrCreate([
            'student_id' => $student->id,
            'lesson_id' => $lesson_id
        ]);
        Attendance::reguard();

        if ($attendance) {
            return $this->response->created(route_api('attendance.get', self::VERSION, ['id' => $attendance->id]));
        }

        throw new HttpException(500);
    }

}