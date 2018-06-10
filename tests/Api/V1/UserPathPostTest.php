<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class UserPathPostTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    /* @var string */
    public static $table = User::TABLE_NAME;

    public function legal_user_data_provider()
    {
        return [
            'legal user' =>
                ['hello123', 'hello123@mail.com', 'my_password'],
            'legal user with symbols' =>
                ['is.me_mario123', 'mario@mail.com', 'my_password'],
        ];
    }

    /**
     * @test
     * @dataProvider legal_user_data_provider
     * @param $username
     * @param $email
     * @param $password
     */
    public function post_a_legal_user_should_be_successful($username, $email, $password)
    {
        $this->json('POST', $this->u('user.add'), ['data' => [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]])->assertResponseStatus(201);

        $location = $this->response->headers->get('Location');
        $this->get($location)->seeJson([
            'username' => $username,
            'email' => $email
        ]);
        $this->seeInDatabase(self::$table, ['username' => $username]);
        $this->seeInDatabase(self::$table, ['email' => $email]);
    }

    public function missing_field_user_data_provider()
    {
        return [
            // missing fields
            'missing username' =>
                ['', 'noname@163.com', 'my_password'],
            'missing email' =>
                ['noemail', '', 'my_password'],
            'missing password' =>
                ['nopassword', 'nopassword@mail.com', ''],
        ];
    }

    /**
     * @test
     * @dataProvider missing_field_user_data_provider
     * @param $username
     * @param $email
     * @param $password
     */
    public function post_a_missing_field_user_should_fail($username, $email, $password)
    {
        $this->json('POST', $this->u('user.add'), ['data' => [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]])->assertResponseStatus(422);

        $this->notSeeInDatabase(self::$table, ['username' => $username]);
        $this->notSeeInDatabase(self::$table, ['email' => $email]);
    }


    public function illegal_field_user_data_provider()
    {
        return [
            'illegal username too short' =>
                ['hel', 'hel@mail.com', 'my_password'],
            'illegal username too long' =>
                ['a123451234512345123451234', 'a12345@mail.com', 'my_password'],
            'illegal username number start' =>
                ['123hello', '123hello@mail.com', 'my_password'],
            'illegal username with upper' =>
                ['isMyWay', 'ismyway@mail.com', 'my_password'],

            'illegal email address' =>
                ['noemail', 'whatisemail', 'my_password'],
            'illegal email address 2' =>
                ['noemail2', 'whatisemail@mail', 'my_password'],
        ];
    }

    /**
     * @test
     * @dataProvider illegal_field_user_data_provider
     * @param $username
     * @param $email
     * @param $password
     */
    public function post_a_illegal_field_user_should_fail($username, $email, $password)
    {
        $this->json('POST', $this->u('user.add'), ['data' => [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]])->assertResponseStatus(422);

        $this->notSeeInDatabase(self::$table, ['username' => $username]);
        $this->notSeeInDatabase(self::$table, ['email' => $email]);
    }

    /**
     * @test
     */
    public function post_a_user_conflicted_should_fail()
    {
        $user = factory(\App\Models\User::class)->create();

        $this->json('POST', $this->u('user.add'), ['data' => [
            'username' => 'a' . $user->username,
            'email' => $user->email,
            'password' => bcrypt('password'),
        ]])->assertResponseStatus(409);

        $this->json('POST', $this->u('user.add'), ['data' => [
            'username' => $user->username,
            'email' => 'a' . $user->email,
            'password' => bcrypt('password'),
        ]])->assertResponseStatus(409);

        $this->notSeeInDatabase(self::$table, ['username' => 'a' . $user->username]);
        $this->notSeeInDatabase(self::$table, ['email' => 'a' . $user->email]);
    }

    /**
     * @test
     */
    public function post_non_json_content_should_fail()
    {
        $this->post($this->u('user.add'), [
            'username' => 'hello123',
            'email' => 'hello123@mail.com',
            'password' => 'my_password',
        ])->assertResponseStatus(400);

        $this->notSeeInDatabase(self::$table, ['username' => 'hello123']);
    }

    /**
     * @test
     */
    public function post_json_without_data_array_should_fail()
    {
        $this->json('POST', $this->u('user.add'), ['x' => [
            'username' => 'hello123',
            'email' => 'hello123@mail.com',
            'password' => 'my_password',
        ]])->assertResponseStatus(400);

        $this->notSeeInDatabase(self::$table, ['username' => 'hello123']);
    }
}
