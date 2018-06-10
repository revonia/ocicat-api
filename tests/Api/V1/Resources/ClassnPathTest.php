<?php
use App\Models\Resources\Attendance;
use App\Models\Resources\Classn;
use App\Models\Resources\Course;
use App\Models\Roles\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Auto generate source code header.
 * Original File Name: AttendancePathTest.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 16:14
 */
class ClassnPathTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @test
     */
    public function get_all_courses_should_success()
    {
        $classn = factory(Classn::class)->create();

        $courses = factory(Course::class, 5)->create();

        $transform = new \App\Transformers\CourseTransformer();
        $data = [];
        foreach ($courses as $course) {
            $course->associateClassn($classn);
            $data[] = $transform->transform($course);
        }

        $this->get($this->u('classn.course.get', 'classn', $classn->id))
            ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }


    /**
     * @test
     */
    public function get_all_students_should_success()
    {
        $classn = factory(Classn::class)->create();

        $students = factory(Student::class, 5)->create();

        $data = [];
        $transformer = new \App\Transformers\StudentTransformer;
        foreach ($students as $student) {
            $student->attachToClassn($classn);
            $data[] = $transformer->transform($student);
        }

        $this->get($this->u('classn.student.get', 'classn', $classn->id))
            ->seeJson(['data' => $data])->assertResponseStatus(200);
    }
}