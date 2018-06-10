<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class UserRolePathTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    const TRANSFORMER = \App\Transformers\UserTransformer::class;

    /* @var string */
    public static $table  = User::TABLE_NAME;

    public function role_type_data_provider()
    {
        $ret = [];
        foreach (User::ROLE_TYPES as $key => $value) {
            $ret[$key] = [$key, $value];
        }
        return $ret;
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function get_a_user_s_role_should_succeed($role_type, $class)
    {
        $user = factory(User::class)->create();
        $role = factory($class)->create();
        $user->attachRole($role);

        $this->get($this->u('user.get.role', 'user', $user->id))
            ->seeJson([
                'id' => $role->id,
                'type' => $role_type,
                'uri' => $this->u($role_type . '.get', $role_type, $role->id),
            ])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function get_a_role_should_fail_with_user_dont_have_role()
    {
        $user = factory(User::class)->create();
        $this->get($this->u('user.get.role', 'user', $user->id))->assertResponseStatus(400);
    }

    /**
     * @test
     */
    public function get_a_role_should_fail_by_given_a_non_existent_user_id()
    {
        factory(User::class, 5)->create();

        $id = User::max('id') + 1;
        $this->get($this->u('user.get.role', 'user', $id))->assertResponseStatus(404);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function attach_role_to_user_should_success($role_type, $class)
    {
        $user = factory(User::class)->create();
        /** @var \App\Models\Role $role */
        $role = factory($class)->create();

        $this->json('PUT', $this->u('user.attach.role', 'user', $user->id), ['data' => [
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]])->assertResponseStatus(202);

        $this->seeInDatabase($role::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $user->id,
        ]);

        $this->seeInDatabase(self::$table, [
            'id' => $user->id,
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function detach_role_from_user_should_success($role_type, $class)
    {
        $user = factory(User::class)->create();
        /** @var \App\Models\Role $role */
        $role = factory($class)->create();

        $user->attachRole($role);

        $this->json('DELETE', $this->u('user.detach.role', 'user', $user->id))
             ->assertResponseStatus(202);

        $this->dontSeeInDatabase($role::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $user->id,
        ]);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]);

    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function attach_role_to_non_existent_user_should_fail($role_type, $class)
    {
        factory(User::class, 3)->create();

        /** @var \App\Models\Role $role */
        $role = factory($class)->create();

        $id = User::max('id') + 1;
        $this->json('PUT', $this->u('user.attach.role', 'user', $id), ['data' => [
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]])->assertResponseStatus(404);

        $this->dontSeeInDatabase($role::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $id,
        ]);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $id,
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]);

    }

    /**
     * @test
     */
    public function detach_role_from_non_existent_user_should_fail()
    {
        factory(User::class, 3)->create();

        $id = User::max('id') + 1;
        $this->json('DELETE', $this->u('user.detach.role', 'user', $id))
             ->assertResponseStatus(404);
    }


    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function user_already_have_a_role_cannot_attach($role_type, $class)
    {
        $user = factory(User::class)->create();
        /** @var \App\Models\Role $roles */
        $roles = factory($class, 2)->create();

        $user->attachRole($roles[0]);

        $this->json('PUT', $this->u('user.attach.role', 'user', $user->id), ['data' => [
            'role_id' => $roles[1]->id,
            'role_type' => $role_type,
        ]])->assertResponseStatus(409);

        $this->dontSeeInDatabase($roles[0]::TABLE_NAME, [
            'id' => $roles[1]->id,
            'user_id' => $user->id,
        ]);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'role_id' => $roles[1]->id,
            'role_type' => $role_type,
        ]);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function attach_a_non_existent_role_should_fail($role_type, $class)
    {
        $user = factory(User::class)->create();

        $role = factory($class)->create();

        $id = $role::max('id') + 1;

        $this->json('PUT', $this->u('user.attach.role', 'user', $user->id), ['data' => [
            'role_id' => $id,
            'role_type' => $role_type,
        ]])->assertResponseStatus(400);

        $this->dontSeeInDatabase($role::TABLE_NAME, [
            'id' => $id,
            'user_id' => $user->id,
        ]);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $user->id,
            'role_id' => $id,
            'role_type' => $role_type,
        ]);
    }

    /**
     * @test
     */
    public function user_do_not_have_role_cannot_detach()
    {
        $user = factory(User::class)->create();

        $this->json('DELETE', $this->u('user.detach.role', 'user', $user->id))
            ->assertResponseStatus(400);
    }

    /**
     * @test
     * @dataProvider role_type_data_provider
     * @param $role_type
     * @param $class
     */
    public function attach_a_role_which_has_been_attached_should_fail($role_type, $class)
    {
        $users = factory(User::class, 2)->create();

        $role = factory($class)->create();

        $users[0]->attachRole($role);

        $this->json('PUT', $this->u('user.attach.role', 'user', $users[1]->id), ['data' => [
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]])->assertResponseStatus(400);

        $this->dontSeeInDatabase($role::TABLE_NAME, [
            'id' => $role->id,
            'user_id' => $users[1]->id,
        ]);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $users[1]->id,
            'role_id' => $role->id,
            'role_type' => $role_type,
        ]);

    }


//    /**
//     * @test
//     * @dataProvider role_type_data_provider
//     * @param $class
//     * @param $role_type
//     */
//    public function add_a_role_to_user_should_be_successful($role_type, $class)
//    {
//        $user = factory(App\Models\User::class)->create();
//        $this->post($this->u('user.add.role', 'user', $user->id), [
//            'type' => $role_type
//        ])->assertResponseStatus(201);
//
//        /** @var App\Models\Role $role */
//        $role = $user->fresh()->role;
//
//        $role_uri = $this->u($role_type . '.get', 'role', $role->id);
//        $this->seeHeader('Location', $role_uri);
//
//        $this->seeInDatabase($role->getTable(), [
//            'id' => $role->id,
//            'user_id' => $user->id
//        ]);
//        $this->seeInDatabase(self::$table, [
//            'id' => $user->id,
//            'role_type' => $role_type,
//            'role_id' => $role->id
//        ]);
//    }
//
//    /**
//     * @test
//     */
//    public function add_a_role_to_user_who_had_role_should_conflict()
//    {
//        $user = factory(App\Models\User::class)->create();
//        $role = factory(App\Models\Roles\Admin::class)->make();
//        $user->attachRole($role);
//
//        $this->post($this->u('user.add.role','user', $user->id), [
//            'type' => 'student'
//        ])->assertResponseStatus(409);
//
//        $this->notSeeInDatabase(self::$table, [
//            'id' => $user->id,
//            'role_type' => 'student'
//        ]);
//    }
}
