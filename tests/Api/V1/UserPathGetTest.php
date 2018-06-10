<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use App\Models\Roles\Admin;

class UserPathGetTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    /* @var string */
    public static $table = User::TABLE_NAME;

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

    /**
     * @test
     */
    public function get_a_user_by_id_should_be_successful()
    {
        $users = factory(User::class, 10)->create();
        foreach ($users as $user) {
            $json['data'] = $this->getJsonResponseFormat($user);
            $this->get($this->u('user.get', 'user', $user->id))
                ->seeJsonEquals($json);
        }
    }

    /**
     * @test
     */
    public function it_should_fail_by_given_a_non_numeric_id()
    {
        factory(User::class, 10)->create();
        $this->get($this->u('user.get', 'user', 'a\'~!@#$%^&*(){}<>'))->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function it_should_fail_by_given_a_non_existent_id()
    {
        factory(User::class, 10)->create();
        $id = User::max('id') + 1;
        $this->get($this->u('user.get', 'user', $id))->assertResponseStatus(404);
    }

    /**
     * @test
     */
    public function searching_without_query_should_be_empty()
    {
        factory(User::class, 10)->create();
        $this->get($this->u('user.search'))->assertResponseStatus(204);
    }

    /**
     * @test
     */
    public function searching_a_user_by_user_queryable_should_be_successful()
    {
        $user = factory(User::class)->create();
        $role = factory(\App\Models\Roles\Admin::class)->create();
        $user->attachRole($role);

        foreach (User::QUERYABLE as $q)
        {
            $json['data'] = [$this->getJsonResponseFormat($user)];
            $this->get($this->ux('user.search', [$q => $user->$q]))
                ->seeJsonEquals($json)->assertResponseStatus(200);
        }
    }

    /**
     * @test
     */
    public function searching_users_by_user_queryable_should_be_successful()
    {
        $users = factory(User::class, 10)->create();

        $json = [];
        foreach ($users as $user)
        {
            $json['data'][] = $this->getJsonResponseFormat($user);
        }
        $this->get($this->ux('user.search', ['status' => 'active']))
            ->seeJsonEquals($json)->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function searching_a_user_by_user_profile_queryable_should_be_successful()
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function searching_user_by_non_existent_query_value_should_be_empty()
    {
        $user = factory(User::class)->create();
        $user->attachRole(factory(Admin::class)->create());

        foreach (User::QUERYABLE as $q)
        {
            $json['data'] = [$this->getJsonResponseFormat($user)];
            $this->get($this->ux('user.search', [$q => $user->$q . '1']))
                 ->assertResponseStatus(204);
        }
    }

    /**
     * @test
     */
    public function searching_a_user_by_wrong_query_key_should_be_empty()
    {
        $user = factory(User::class)->create();
        $params = ['foo' => $user->username];
        //$params = ['foo' => $user->username, 'username' => $user->username];
        $this->get($this->ux('user.search', $params))->assertResponseStatus(204);
        $this->markTestIncomplete();
    }


}
