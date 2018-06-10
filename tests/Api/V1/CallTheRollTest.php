<?php
use App\Models\Resources\Attendance;
use App\Models\Resources\Lesson;
use App\Models\Resources\Course;
use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Auto generate source code header.
 * Original File Name: AttendancePathTest.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 16:14
 */
class CallTheRollTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function call_the_roll_should_success()
    {
        $course = factory(Course::class)->create();
        $classns = factory(Classn::class, 3)->create();
        $users = factory(User::class, 3)->create();
        $students = factory(Student::class, 3)->create();



        for ($i = 0, $imax = count($classns); $i < $imax; $i++) {
            $users[$i]->attachRole($students[$i]);
            $students[$i]->attachToClassn($classns[$i]);
            $course->associateClassn($classns[$i]);
        }

        $result = $course->addLesson();
        $pin = $result[1];

        for ($i = 0, $imax = count($classns); $i < $imax; $i++) {
            $token = JWTAuth::fromUser($users[$i]);
            $this->refreshApplication();

            $this->json('POST', $this->u('me.verify.pin'),
                ['data' => ['pin' => $pin]],
                ['Authorization' => 'Bearer ' . $token]
            );
            $this->assertResponseStatus(201);

            $attendances = $students[$i]->attendances;

            foreach ($attendances as $attendance) {
                $this->assertEquals($result[0]->id, $attendance->lesson_id);
            }
        }
    }
}