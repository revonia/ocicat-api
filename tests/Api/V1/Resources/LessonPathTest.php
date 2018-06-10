<?php
use App\Models\Resources\Attendance;
use App\Models\Resources\Lesson;
use App\Models\Resources\Course;
use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Auto generate source code header.
 * Original File Name: AttendancePathTest.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 16:14
 */
class LessonPathTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function get_all_attendances_should_success()
    {
        $students = factory(Student::class, 5)->create();
        $lesson = factory(Lesson::class)->create();

        $data = [];
        $transformer = new \App\Transformers\AttendanceTransformer();
        foreach ($students as $student) {
            $attendance = Attendance::createWithStudentAndLesson($student, $lesson);
            $data[] = $transformer->transform($attendance);
        }

        $this->get($this->u('lesson.attendance.get', 'lesson', $lesson->id))
            ->seeJson(['data' => $data])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function get_pin_should_success()
    {
        $course = factory(Course::class)->create();
        $classns = factory(Classn::class, 5)->create();

        foreach ($classns as $classn) {
            $course->associateClassn($classn);
        }

        $result = $course->addLesson();
        $pin = $result[1];
        $this->get($this->u('lesson.pin.get', 'lesson', $result[0]->id))
            ->seeJson(['data' => ['pin' => $pin]])->assertResponseStatus(200);
    }
}