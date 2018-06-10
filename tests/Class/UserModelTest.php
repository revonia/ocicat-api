<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class UserModelTest extends TestCase
{
    use DatabaseMigrations;

    const ATTR_KEYS = ['id', 'username', 'uuid', 'email'];

    /* @var string */
    public static $table;

    public static $profile_table = 'user_profiles';

    public static function setUpBeforeClass()
    {
        $user = new User();
        self::$table = $user->getTable();
    }

    /**
     * 过滤出所需要的attributes
     *
     * @param User $user
     * @return array|bool
     */
    public static function userAttributes(User $user)
    {
        if (!$user->exists) return false;
        return array_filter_keys($user->getAttributes(), self::ATTR_KEYS);
    }

    /**
     * For testUserCreateValidation
     *
     * @return array
     */
    public function fieldValidationUserDataProvider()
    {
        $password = '$2y$10$WCwA79auH9cJkhkUHtpE3OI4zTyRoSncv97YiVT8g5QMxhDU4q5va';
        return [
            'normal user' =>
                ['hello123', 'hello123@mail.com', $password, true, ''],
            'normal user with symbols' =>
                ['is.me_mario123', 'mario@mail.com', $password, true, ''],

            // missing fields
            'missing username' =>
                ['', 'noname@163.com', $password, false, 'username'],
            'missing email' =>
                ['noemail', '', $password, false, 'email'],
            'missing password' =>
                ['nopassword', 'nopassword@mail.com', '', false, 'password'],

            //illegal username
            'illegal username too short' =>
                ['hel', 'hel@mail.com', $password, false, 'username'],
            'illegal username too long' =>
                ['a123451234512345123451234', 'a12345@mail.com', $password, false, 'username'],
            'illegal username number start' =>
                ['123hello', '123hello@mail.com', $password, false, 'username'],
            'illegal username with upper' =>
                ['isMyWay', 'ismyway@mail.com', $password, false, 'username'],

            //illegal email
            'illegal email address' =>
                ['noemail', 'whatisemail', $password, false, 'email'],
            'illegal email address 2' =>
                ['noemail2', 'whatisemail@mail', $password, false, 'email'],

            'illegal password' =>
                ['mypassword', 'mypassword@mail.com', '34qefud3heh8fh9w', false, 'password']
        ];
    }

    /**
     * 进行创建测试
     * 字段验证测试
     *
     * @test
     * @dataProvider fieldValidationUserDataProvider
     * @param $username string
     * @param $email string
     * @param $password string
     * @param $expect bool
     * @param $failedRules
     */
    public function testUserCreateValidation($username, $email, $password, $expect, $failedRules)
    {
        $user = User::create([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);

        $this->assertEquals($expect, $user->wasRecentlyCreated);
        if ($expect) {
            $this->seeInDatabase(self::$table, ['username' => $username, 'email' => $email]);
            $this->seeInDatabase(self::$profile_table, ['user_id' => $user->id]);
        } else {
            $this->notSeeInDatabase(self::$table, ['username' => $username, 'email' => $email]);
            $this->assertTrue($user->validatorFailed());
            $this->assertArrayHasKey($failedRules, $user->invalid());
        }
    }

    /**
     * 进行创建测试
     * 冲突数据测试
     *
     * @test
     * @depends testUserCreateValidation
     */
    public function testUserCreateConflicted()
    {
        $user = factory(\App\Models\User::class)->create();

        $user_test = User::create([
            'username' => $user->username,
            'email' => 'a' . $user->email,
            'password' => $user->password
        ]);

        $this->assertTrue($user_test->isConflicted());
        $this->assertFalse($user_test->wasRecentlyCreated);
        $this->notSeeInDatabase(self::$table, ['email' => 'a' . $user->email]);

        $user_test2 = User::create([
            'username' => 'a' . $user->username,
            'email' => $user->email,
            'password' => $user->password
        ]);

        $this->assertTrue($user_test2->isConflicted());
        $this->assertFalse($user_test2->wasRecentlyCreated);
        $this->notSeeInDatabase(self::$table, ['username' => 'a' . $user->username]);


    }

    /**
     * 测试用户创建后能否得到uuid
     *
     * @test
     * @depends testUserCreateValidation
     */
    public function testGetUuidAfterCreated()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->assertNotNull($user->uuid);
        $this->assertArrayNotHasKey('uuid', $user->getDirty());
    }

    /**
     * 测试更新Email
     *
     * @test
     * @depends testUserCreateValidation
     */
    public function testUpdateEmail()
    {
        $email = 'aaa@eqws.com';
        $user = factory(\App\Models\User::class)->create();

        //正常更新email
        $this->assertTrue($user->updateEmail($email));
        $this->assertEquals($email, $user->email);
        $this->seeInDatabase(self::$table, ['email' => $email]);

        //不符合格式的email
        $this->assertFalse($user->updateEmail('reqw@mail'));
        $this->assertTrue($user->validatorFailed());

        //空email
        $this->assertFalse($user->updateEmail(''));
    }

    /**
     * 测试更新Email冲突
     *
     * @test
     * @depends testUpdateEmail
     */
    public function testUpdateEmailConflicted()
    {
        $user = factory(\App\Models\User::class)->create();
        $user2 = factory(\App\Models\User::class)->create();

        $this->assertFalse($user2->updateEmail($user->email));
        $this->assertTrue($user2->validatorFailed());
        $this->assertTrue($user2->isConflicted());
        $this->notSeeInDatabase(self::$table, ['username' => $user2->username, 'email' => $user->email]);
    }

    /**
     * 测试更新password
     *
     * @test
     * @depends testUserCreateValidation
     */
    public function testUpdatePassword()
    {
        $password = bcrypt('newpassword');

        $user = factory(\App\Models\User::class)->create();

        $this->assertTrue($user->updatePassword($password));
        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'password' => $password
        ]);

        //空密码
        $this->assertFalse($user->updatePassword(''));
    }

    /**
     * @return array
     */
    public function roleClassDataProvider()
    {
        return [
            'admin' => [\App\Models\Roles\Admin::class],
            'student' => [\App\Models\Roles\Student::class],
            'teacher' => [\App\Models\Roles\Teacher::class]
        ];
    }

    /**
     * @test
     * @dataProvider roleClassDataProvider
     * @param $class
     */
    public function attach_role_should_success($class)
    {
        $user = factory(\App\Models\User::class)->create();
        $role = factory($class)->create();

        $user->attachRole($role);
        $this->seeInDatabase(self::$table, [
            'id' => $user->id,
            'role_type' => $role->getMorphClass(),
            'role_id' => $role->id
        ]);
        $this->seeInDatabase($class::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     * @depends attach_role_should_success
     * @dataProvider roleClassDataProvider
     * @param $class
     */
    public function detach_role_should_success($class)
    {
        $user = factory(\App\Models\User::class)->create();
        $role = factory($class)->create();
        $user->attachRole($role);

        $user->detachRole();
        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'role_type' => $role->getMorphClass(),
            'role_id' => $role->id
        ]);
        $this->dontSeeInDatabase($class::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $user->id
        ]);
    }

    /**
     * @test
     * @dataProvider roleClassDataProvider
     * @param $class
     */
    public function get_user_role_should_success($class)
    {
        $user = factory(\App\Models\User::class)->create();
        $role = factory($class)->create();
        $user->attachRole($role);

        $role = $user->role;

        $this->assertTrue($role->exists);
    }


    /**
     * 测试删除用户
     *
     * @dataProvider roleClassDataProvider
     * @test
     * @param $class App\Models\Role
     */
    public function testDeleteUser($class)
    {
        $user = factory(\App\Models\User::class)->create();
        $role = factory($class)->create();
        $user->attachRole($role);
        $user->delete();

        //把软删除实现推后
        //$this->seeIsSoftDeletedInDatabase(self::$table, ['id' => $user->id]);

        //User::withTrashed()->find($user->id)->forceDelete();
        $this->notSeeInDatabase(self::$table, ['id' => $user->id]);
        $this->notSeeInDatabase($role->getTable(), ['user_id' => $user->id]);
        $this->notSeeInDatabase(self::$profile_table, ['user_id' => $user->id]);
    }
}
