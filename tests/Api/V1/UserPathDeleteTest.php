<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class UserPathDeleteTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    /* @var string */
    public static $table;

    public static function setUpBeforeClass()
    {
        $user = new User();
        self::$table = $user->getTable();
    }

    /**
     * @test
     */
    public function deleting_a_user_should_succeed()
    {
        $user = factory(\App\Models\User::class)->create();
        $this->delete($this->u('user.delete', 'user', $user->id))->assertResponseStatus(202);
        $this->notSeeInDatabase(self::$table, ['id' => $user->id]);
    }

    /**
     * @test
     */
    public function deleting_a_non_existent_user_should_fail()
    {
        factory(\App\Models\User::class, 2)->create();
        $id = User::max('id') + 1;
        $this->delete($this->u('user.delete', 'user', $id))->assertResponseStatus(404);
    }
}
