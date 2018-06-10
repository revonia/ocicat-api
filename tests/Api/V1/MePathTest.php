<?php
/**
 * Created by PhpStorm.
 * User: wangjian
 * Date: 2016/12/8
 * Time: 17:51
 */

use App\Models\UserProfile;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Facades\JWTFactory;

class MePathTest extends TestCase
{
    use DatabaseMigrations;

    /** @var User|null  */
    protected $user = null;

    protected $username = 'test_user';
    protected $email = 'my_mail@mail.com';
    protected $password = 'my_password';

    protected function prepareUser($role = null)
    {
        $this->user = User::create([
            'username' => $this->username,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        if ($role) $this->user->attachRole($role);
    }

    /**
     * @param User $user
     * @return array
     */
    public function getJsonResponseFormat(User $user)
    {
        $profile = $user->profile;
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'role_type' => $user->role_type,
            'status' => $user->status,
            'profile' => [
                'nickname' => $profile->nickname,
                'realname' => $profile->realname,
                'age' => $profile->age,
                'gender' => $profile->gender,
                'phone' => $profile->phone,
            ]
        ];
    }

    public function role_type_data_provider()
    {
        $ret = [];
        foreach (User::ROLE_TYPES as $key => $value) {
            $ret[$key] = [$key, $value];
        }
        return $ret;
    }

    /**
     * 获取用户的token
     */

    protected function getToken()
    {
        $this->json('POST', $this->u('me.auth'), ['data' => [
            'username' => $this->username,
            'password' => $this->password,
        ]]);
        $this->refreshApplication();  //特别重要，初始化请求
        return 'Bearer ' . json_decode($this->response->content(), true)['token'];
    }

    /**
     * @test
     */
    public function get_without_token_should_fail()
    {
        $this->prepareUser();
        $this->get($this->u('me.get'))->assertResponseStatus(401);
    }

    /**
     * @test
     */

    public function get_with_wrong_token_should_fail()
    {
        $this->prepareUser();
        $this->get($this->u('me.get'), ['Authorization' => 'Bearer asddasfsdfewerwdsf'])
             ->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function login_get_token_should_success()
    {
        $this->prepareUser();

        $this->json('POST', $this->u('me.auth'), ['data' => [
            'username' => $this->username,
            'password' => $this->password,
        ]])->assertResponseStatus(200);

        $token = json_decode($this->response->content(), true)['token'];

        $this->assertNotEmpty($token);
    }

    /**
     * @test
     */
    public function refresh_token_should_success()
    {
        $this->prepareUser();

        $token = $this->getToken();
        sleep(1);
        $this->get($this->u('me.refresh.token'), ['Authorization' => $token])
             ->assertResponseStatus(200);
        $new_token = $this->response->headers->get('Authorization');

        $this->assertNotEquals($token, $new_token);

        sleep(1);
        $this->get($this->u('me.refresh.token'), ['Authorization' => $new_token])
             ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function refresh_token_without_token_should_failed()
    {
        $this->prepareUser();

        $token = 'no_token';

        $this->get($this->u('me.refresh.token'), ['Authorization' => $token])
            ->assertResponseStatus(400);
    }

        /**
     * @test
     */
    public function get_with_token_should_success()
    {
        $this->prepareUser();

        $data = $this->getJsonResponseFormat($this->user);

        $this->get($this->u('me.get'), ['Authorization' => $this->getToken()])
             ->seeJsonEquals(['data' => $data])
             ->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function login_with_wrong_email_or_username_should_failed()
    {
        $this->prepareUser();

        $this->json('POST', $this->u('me.auth'), ['data' => [
            'username' => $this->username,
            'password' => $this->password . 'a',
        ]])->assertResponseStatus(401);
    }

    /**
     * @test
     */
    public function put_email_should_success()
    {
        $this->prepareUser();

        $email = 'my_new_email@mail.com';
        $this->json('PUT', $this->u('me.update.email'), ['data' => [
            'email' => $email
        ]], ['Authorization' => $this->getToken()])->assertResponseStatus(202);

        $this->seeInDatabase(User::TABLE_NAME, [
            'id' => $this->user->id,
            'email' => $email,
        ]);
    }

    /**
     * @test
     */
    public function put_password_should_success()
    {
        $this->prepareUser();
        $password = 'new_password';

        $this->json('PUT', $this->u('me.update.password'), ['data' => [
            'old_password' => $this->password,
            'password' => $password,
        ]], ['Authorization' => $this->getToken()])->assertResponseStatus(202);

        $this->dontSeeInDatabase(User::TABLE_NAME, [
            'id' => $this->user->id,
            'password' => $this->user->password,
        ]);
    }

    /**
     * @test
     */
    public function put_profile_should_success()
    {
        $this->prepareUser();

        $profile = array(
            'nickname' => 'nickname',
            'realname' => 'realname',
            'age' => 20,
            'gender' => 'male',
            'phone' => '1234567890',
        );

        $this->json('PUT', $this->u('me.update.profile'), [
            'data' => $profile
        ], ['Authorization' => $this->getToken()])->assertResponseStatus(202);

        $profile['user_id'] = $this->user->id;
        $this->seeInDatabase(UserProfile::TABLE_NAME, $profile);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function get_role_should_success($role_type, $class)
    {
        $role = factory($class)->create();
        $this->prepareUser($role);

        $this->get($this->u('me.get.role'), ['Authorization' => $this->getToken()])
             ->seeJson([
                 'id' => $role->id,
                 'role' => $role->getMorphClass(),
             ])->assertResponseStatus(200);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function add_user_role_should_success($role_type, $class)
    {
        $this->prepareUser();

        $this->json('POST', $this->u('me.add.role'), [ 'data' => [
            'role_type' => $role_type,
        ]], ['Authorization' => $this->getToken()]);

        if ($role_type != 'admin') {
            $this->assertResponseStatus(201);
            $this->seeInDatabase($class::TABLE_NAME, [
                'user_id' => $this->user->id,
            ]);
        } else {
            $this->assertResponseStatus(400);
        }
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function update_user_role_should_success($role_type, $class)
    {
        $role = factory($class)->create();
        $this->prepareUser($role);

        if ($role_type == 'student') {
            $data = ['student_number' => "12345426435"];
        } else if ($role_type == 'teacher') {
            $data = ['employee_number' => "12345426435"];
        } else {
            $data = [];
        }


        $this->json('PUT', $this->u('me.update.role'), ['data' => $data],
            ['Authorization' => $this->getToken()]);

        if ($role_type != 'admin') {
            $this->assertResponseStatus(202);
            $this->seeInDatabase($class::TABLE_NAME, $data);
        } else {
            $this->assertResponseStatus(400);
        }
    }


}
