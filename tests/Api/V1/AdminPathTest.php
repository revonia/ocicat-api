<?php
/**
 * Auto generate source code header.
 * Original File Name: AdminPathTest.php
 * Author: Wangjian
 * Date: 2017/3/1
 * Time: 14:10
 */

use App\Models\Roles\Admin;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class AdminPathTest extends TestCase
{
    use DatabaseMigrations;

    /** @var  User|null */
    protected $user;

    /** @var  Admin|null */
    protected $admin;

    const VERSION = 'v1';

    /* @var string */
    public static $table = Admin::TABLE_NAME;

    /**
     * 准备Admin
     * @param bool $attach 是否创建用户并附加
     */
    protected function prepareAdmin($attach = false)
    {
        $this->admin = factory(Admin::class)->create();

        if ($attach === false) return;

        $this->user = User::create([
            'username' => 'admin_user',
            'email' => 'admin_user@example.com',
            'password' => bcrypt('my_password'),
        ]);

        $this->user->attachRole($this->admin);
    }

    /**
     * @test
     */
    public function get_admin_by_id_should_success()
    {
        $this->prepareAdmin();

        $this->get($this->u('admin.get', 'admin', $this->admin->id))
             ->seeJsonEquals(['data' => [
                 'id' => $this->admin->id,
                 'role' => 'admin',
                 'group' => null,
             ]])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function update_admin_should_success()
    {
        $this->prepareAdmin();

        $this->json('PUT', $this->u('admin.update', 'admin', $this->admin->id),['data' => [
            'group' => 'my_group'
        ]])->assertResponseStatus(202);

        $this->seeInDatabase(self::$table, [
            'id' => $this->admin->id,
            'group' => 'my_group'
        ]);
    }

    /**
     * @test
     */
    public function delete_admin_should_success()
    {
        $this->prepareAdmin();

        $this->delete($this->u('admin.delete', 'admin', $this->admin->id))
             ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->admin->id,
        ]);
    }

    /**
     * @test
     */
    public function delete_an_attached_admin_should_success()
    {
        $this->prepareAdmin(true);

        $this->delete($this->u('admin.delete', 'admin', $this->admin->id))
            ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->admin->id,
        ]);

        $this->dontSeeInDatabase(User::TABLE_NAME, [
            'id' => $this->user->id,
            'role_id' => $this->admin->id,
        ]);
    }

    /**
     * @test
     */
    public function add_an_admin_should_success()
    {
        $this->json('POST', $this->u('admin.add'), ['data' => [
            'group' => 'my_group'
        ]])->assertResponseStatus(201);

        $this->seeInDatabase(self::$table, [
            'group' => 'my_group'
        ]);
    }
}