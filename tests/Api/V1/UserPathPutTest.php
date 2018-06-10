<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class UserPathPutTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    /* @var string */
    public static $table = User::TABLE_NAME;

    protected static $faker;

    public static function setUpBeforeClass()
    {
        self::$faker = Faker\Factory::create();
    }

    /**
     * @test
     */
    public function put_username_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();
        $value = self::$faker->userName;
        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
            'username' => $value
        ]])->assertResponseStatus(202);

        $this->notSeeInDatabase(self::$table, ['username' => $user->username]);
        $this->seeInDatabase(self::$table, ['username' => $value]);
    }

    /**
     * @test
     */
    public function put_email_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();
        $value = self::$faker->email;
        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
            'email' => $value
        ]])->assertResponseStatus(202);

        $this->notSeeInDatabase(self::$table, ['email' => $user->email]);
        $this->seeInDatabase(self::$table, ['email' => $value]);
    }

    /**
     * @test
     */
    public function put_password_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();
        $value = str_random(10);
        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
            'password' => $value
        ]])->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'password' => $user->password
        ]);
    }

    /**
     * @test
     */
    public function put_status_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();

        foreach (User::STATUS as $value) {
            $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
                'status' => $value
            ]])->assertResponseStatus(202);

            $this->seeInDatabase(self::$table, ['id' => $user->id, 'status' => $value]);
        }
    }

    /**
     * @test
     */
    public function put_wrong_field_should_fail()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
            'foo' => 'bar'
        ]])->assertResponseStatus(400);
    }

    /**
     * @test
     */
    public function put_multi_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();

        $value = ['data' => [
            'username' => self::$faker->userName,
            'email' => self::$faker->email,
            'password' => str_random(10),
            'status' => User::STATUS[1]
        ]];
        $this->json('PUT', $this->u('user.update', 'user', $user->id), $value)->assertResponseStatus(202);

        unset($value['data']['password']);
        $this->seeInDatabase(self::$table, $value['data']);
    }

    /**
     * @test
     */
    public function put_some_special_field_should_fail()
    {
        $fields = [['id' => 1234], ['uuid' => self::$faker->uuid],
            ['role_type' => 'admin'], ['role_id' => 2323]];

        $user = factory(\App\Models\User::class)->create();

        foreach ($fields as $field) {
            $this->json('PUT', $this->u('user.update', 'user', $user->id),
                ['data' => $field])->assertResponseStatus(400);
        }
    }

    /**
     * @test
     */
    public function put_conflict_email_should_fail()
    {
        $users = factory(\App\Models\User::class, 2)->create();

        $this->json('PUT', $this->u('user.update', 'user', $users[1]->id), ['data' => [
            'email' => $users[0]->email
        ]])->assertResponseStatus(409);

        $this->notSeeInDatabase(self::$table, ['id' => $users[1], 'email' => $users[0]->email]);
    }

    /**
     * @test
     */
    public function put_conflict_username_should_fail()
    {
        $users = factory(\App\Models\User::class, 2)->create();

        $this->json('PUT', $this->u('user.update', 'user', $users[1]->id), ['data' => [
            'username' => $users[0]->username
        ]])->assertResponseStatus(409);

        $this->notSeeInDatabase(self::$table, ['id' => $users[1], 'username' => $users[0]->username]);

    }

    /**
     * @test
     */
    public function put_status_enum_field_with_wrong_value_should_fail()
    {
        $user = factory(\App\Models\User::class)->create();
        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['data' => [
            'status' => 'foo'
        ]])->assertResponseStatus(422);

        $this->notSeeInDatabase(self::$table, ['status' => 'foo']);
    }

    /**
     * @test
     */
    public function put_profile_field_should_success()
    {
        $user = factory(\App\Models\User::class)->create();
        $value = ['data' => [
            'nickname' => self::$faker->userName,
            'realname' => self::$faker->name,
            'age' => rand(1, 100),
            'gender' => 'female',
            'phone' => self::$faker->phoneNumber,

        ]];
        $this->json('PUT', $this->u('user.update.profile', 'user', $user->id), $value)
            ->assertResponseStatus(202);
        $this->seeInDatabase('user_profiles', $value['data']);
    }

    /**
     * @test
     */
    public function put_empty_response_bad_request()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->json('PUT', $this->u('user.update', 'user', $user->id), [])
            ->assertResponseStatus(400);
    }

    /**
     * @test
     */
    public function put_non_existent_user_should_fail()
    {
        $users = factory(\App\Models\User::class, 5)->create();
        $id = User::max('id') + 1;
        $this->json('PUT', $this->u('user.update', 'user', $id), [])
            ->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function put_non_json_content_should_fail()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->put($this->u('user.update', 'user', $user->id), [
            'username' => 'hello123',
            'email' => 'hello123@mail.com',
            'password' => bcrypt(str_random(10)),
        ])->assertResponseStatus(400);

        $this->notSeeInDatabase(self::$table, ['username' => 'hello123']);
    }

    /**
     * @test
     */
    public function put_json_without_data_array_should_fail()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->json('PUT', $this->u('user.update', 'user', $user->id), ['x' => [
            'username' => 'hello123',
            'email' => 'hello123@mail.com',
            'password' => bcrypt(str_random(10)),
        ]])->assertResponseStatus(400);

        $this->notSeeInDatabase(self::$table, ['username' => 'hello123']);
    }
}
