<?php
/**
 * Auto generate source code header.
 * Original File Name: AdminPathTest.php
 * Author: Wangjian
 * Date: 2017/3/1
 * Time: 14:10
 */

use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use App\Models\Resources\Lesson;
use App\Models\Resources\Attendance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;

class StudentPathTest extends TestCase
{
    use DatabaseMigrations;

    /** @var  User|null */
    protected $user;

    /** @var  Student|null */
    protected $student;

    const VERSION = 'v1';

    /* @var string */
    public static $table = Student::TABLE_NAME;

    /**
     * 准备Admin
     * @param bool $attach 是否创建用户并附加
     */
    protected function prepareStudent($attach = false)
    {
        $this->student = factory(Student::class)->create();

        if ($attach === false) return;

        $this->user = User::create([
            'username' => 'student_user',
            'email' => 'student_user@example.com',
            'password' => bcrypt('my_password'),
        ]);

        $this->user->attachRole($this->student);
    }

    /**
     * @test
     */
    public function get_student_by_id_should_success()
    {
        $this->prepareStudent();

        $tf = new \App\Transformers\StudentTransformer();
        $data = $tf->transform($this->student);
        $this->get($this->u('student.get', 'student', $this->student->id))
             ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function update_student_should_success()
    {
        $this->prepareStudent();

        $this->json('PUT', $this->u('student.update', 'student', $this->student->id),['data' => [
            'student_number' => '12345143654'
        ]])->assertResponseStatus(202);

        $this->seeInDatabase(self::$table, [
            'id' => $this->student->id,
            'student_number' => '12345143654'
        ]);
    }

    /**
     * @test
     */
    public function delete_student_should_success()
    {
        $this->prepareStudent();

        $this->delete($this->u('student.delete', 'student', $this->student->id))
             ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->student->id,
        ]);
    }

    /**
     * @test
     */
    public function delete_an_attached_student_should_success()
    {
        $this->prepareStudent(true);

        $this->delete($this->u('student.delete', 'student', $this->student->id))
            ->assertResponseStatus(202);

        $this->dontSeeInDatabase(self::$table, [
            'id' => $this->student->id,
        ]);

        $this->dontSeeInDatabase(User::TABLE_NAME, [
            'id' => $this->user->id,
            'role_id' => $this->student->id,
        ]);
    }

    /**
     * @test
     */
    public function add_a_student_should_success()
    {
        $this->json('POST', $this->u('student.add'), ['data' => [
            'student_number' => '3143412345341'
        ]])->assertResponseStatus(201);

        $this->seeInDatabase(self::$table, [
            'student_number' => '3143412345341'
        ]);
    }

    /**
     * @test
     */
    public function attach_to_classn_should_success()
    {
        $this->prepareStudent();

        $classn = factory(Classn::class)->create();

        $this->json('POST',
            $this->u('student.attachToClassn', 'student', $this->student->id),
            ['data' => ['classn_id' => $classn->id]]
        )->assertResponseStatus(202);

        $this->seeInDatabase(self::$table, [
            'id' => $this->student->id,
            'classn_id' => $classn->id
        ]);
    }

    /**
     * @test
     */
    public function detach_from_classn_should_success()
    {
        $this->prepareStudent();
        $classn = factory(Classn::class)->create();
        $this->student->attachToClassn($classn);


        $this->delete(
            $this->u('student.detachFromClassn', 'student', $this->student->id)
        )->assertResponseStatus(202);

        $this->notSeeInDatabase(self::$table, [
            'id' => $this->student->id,
            'classn_id' => $classn->id
        ]);
    }

    /**
     * @test
     */
    public function get_all_attendances_should_success()
    {
        $student = factory(Student::class)->create();
        $lessons = factory(Lesson::class, 5)->create();

        $data = [];
        $transformer = new \App\Transformers\AttendanceTransformer();
        foreach ($lessons as $lesson) {
            $attendance = Attendance::createWithStudentAndLesson($student, $lesson);
            $data[] = $transformer->transform($attendance);
        }

        $this->get($this->u('student.attendance.get', 'student', $student->id))
            ->seeJson(['data' => $data])->assertResponseStatus(200);
    }
}