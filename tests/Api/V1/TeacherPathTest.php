<?php
/**
 * Auto generate source code header.
 * Original File Name: TeacherPathTest.php
 * Author: Wangjian
 * Date: 2017/3/1
 * Time: 14:10
 */

use App\Models\Resources\Course;
use App\Models\Roles\Teacher;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class TeacherPathTest extends TestCase
{
    use DatabaseMigrations;

    /** @var  User|null */
    protected $user;

    /** @var  Teacher|null */
    protected $teacher;

    const VERSION = 'v1';

    /* @var string */
    public static $table = Teacher::TABLE_NAME;

    /**
     * 准备Teacher
     * @param bool $attach 是否创建用户并附加
     */
    protected function prepareTeacher($attach = false)
    {
        $this->teacher = factory(Teacher::class)->create();

        if ($attach === false) return;

        $this->user = User::create([
            'username' => 'teacher_user',
            'email' => 'teacher_user@example.com',
            'password' => bcrypt('my_password'),
        ]);

        $this->user->attachRole($this->teacher);
    }

    /**
     * @test
     */
    public function get_teacher_by_id_should_success()
    {
        $this->prepareTeacher();

        $transformer = new \App\Transformers\TeacherTransformer();

        $data = $transformer->transform($this->teacher);

        $this->get($this->u('teacher.get', 'teacher', $this->teacher->id))
             ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function update_teacher_should_success()
    {
        $this->prepareTeacher();

        $this->json('PUT', $this->u('teacher.update', 'teacher', $this->teacher->id),['data' => [
            'employee_number' => '124314534234'
        ]])->assertResponseStatus(202);

        $this->seeInDatabase(self::$table, [
            'id' => $this->teacher->id,
            'employee_number' => '124314534234'
        ]);
    }

    /**
     * @test
     */
    public function delete_teacher_should_success()
    {
        $this->prepareTeacher();

        $this->delete($this->u('teacher.delete', 'teacher', $this->teacher->id))
             ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->teacher->id,
        ]);
    }

    /**
     * @test
     */
    public function delete_an_attached_teacher_should_success()
    {
        $this->prepareTeacher(true);

        $this->delete($this->u('teacher.delete', 'teacher', $this->teacher->id))
            ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->teacher->id,
        ]);

        $this->dontSeeInDatabase(User::TABLE_NAME, [
            'id' => $this->user->id,
            'role_id' => $this->teacher->id,
        ]);
    }

    /**
     * @test
     */
    public function add_a_teacher_should_success()
    {
        $this->json('POST', $this->u('teacher.add'), ['data' => [
            'employee_number' => '3143412345341'
        ]])->assertResponseStatus(201);

        $this->seeInDatabase(self::$table, [
            'employee_number' => '3143412345341'
        ]);
    }

    /**
     * @test
     */
    public function get_all_courses_should_success()
    {
        $this->prepareTeacher();

        $courses = factory(Course::class, 5)->create();

        $data = [];
        $transformer = new \App\Transformers\CourseTransformer();
        foreach ($courses as $course) {
            $course->attachToTeacher($this->teacher);
            $data[] = $transformer->transform($course);
        }

        $this->get($this->u('teacher.course.get', 'teacher', $this->teacher->id))
            ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }
}