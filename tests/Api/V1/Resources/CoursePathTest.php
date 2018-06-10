<?php
use App\Models\Resources\Course;
use App\Models\Resources\Classn;
use App\Models\Roles\Teacher;
use App\Models\Resources\Lesson;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Auto generate source code header.
 * Original File Name: CoursePathTest.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 16:14
 */
class CoursePathTest extends TestCase
{
    use DatabaseMigrations;

    const VERSION = 'v1';

    /* @var string */
    public static $table = Course::TABLE_NAME;

    /**
     * @test
     */
    public function attach_to_teacher_should_success()
    {
        $course = factory(Course::class)->create();

        $teacher = factory(Teacher::class)->create();

        $this->json('POST',
            $this->u('course.attachToTeacher', 'course', $course->id),
            ['data' => ['teacher_id' => $teacher->id]]
        )->assertResponseStatus(202);

        $this->seeInDatabase(self::$table, [
            'id' => $course->id,
            'teacher_id' => $teacher->id
        ]);
    }

    /**
     * @test
     */
    public function detach_from_teacher_should_success()
    {
        $course = factory(Course::class)->create();
        $teacher = factory(Teacher::class)->create();
        $course->attachToTeacher($teacher);


        $this->delete(
            $this->u('course.detachFromTeacher', 'course', $course->id)
        )->assertResponseStatus(202);

        $this->notSeeInDatabase(self::$table, [
            'id' => $course->id,
            'teacher_id' => $teacher->id
        ]);
    }

    /**
     * @test
     */
    public function add_lesson_should_success()
    {
        $course = factory(Course::class)->create();
        $classn = factory(Classn::class)->create();
        $course->associateClassn($classn);
        $this->json('POST', $this->u('course.lesson.add', 'course', $course->id),
            ['data' => []]
        )->assertResponseStatus(201);

        $this->seeInDatabase(Lesson::TABLE_NAME, [
            'course_id' => $course->id
        ]);
    }

    /**
     * @test
     */
    public function add_lesson_for_course_which_attached_classn_should_has_pin()
    {
        $course = factory(Course::class)->create();
        $classns = factory(Classn::class, 5)->create();

        foreach ($classns as $classn) {
            $course->associateClassn($classn);
        }

        $this->json('POST', $this->u('course.lesson.add', 'course', $course->id),
            ['data' => []]
        );
        $this->assertResponseStatus(201);

        $location = $this->response->headers->get('Location');
        $path_arr = explode('/', parse_url($location)['path']);
        $lesson_id = (int)end($path_arr);

        foreach ($classns as $classn) {
            $this->seeInDatabase('attendance_pin', [
                'classn_id' => $classn->id,
                'lesson_id' => $lesson_id
            ]);
        }
    }

    /**
     * @test
     */
    public function get_all_classns_should_success()
    {
        $classns = factory(Classn::class, 5)->create();

        $course = factory(Course::class)->create();

        $data = [];
        $transformer = new \App\Transformers\ClassnTransformer();
        foreach ($classns as $classn) {
            $course->associateClassn($classn);
            $data[] = $transformer->transform($classn);
        }

        $this->get($this->u('course.classn.get', 'course', $course->id))
            ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function get_all_lessons_should_success()
    {
        $course = factory(Course::class)->create();

        $lessons = factory(Lesson::class, 5)->create();

        $data = [];
        $transformer = new \App\Transformers\LessonTransformer();
        foreach ($lessons as $lesson) {
            $lesson->course()->associate($course)->save();
            $data[] = $transformer->transform($lesson);
        }

        $this->get($this->u('course.lesson.get', 'course', $course->id))
            ->seeJsonEquals(['data' => $data])->assertResponseStatus(200);
    }

    /**
     * @test
     */
    public function associate_classn_should_success()
    {
        $course = factory(Course::class)->create();

        $classns = factory(Classn::class, 5)->create();

        foreach ($classns as $classn) {
            $this->json('PUT', $this->u('course.classn.associate', 'course', $course->id),
                ['data' => ['classn_id' => $classn->id]]
            )->assertResponseStatus(202);
            $this->seeInDatabase('course_classn', ['course_id' => $course->id, 'classn_id' => $classn->id ]);
        }
    }

    /**
     * @test
     */
    public function get_flat_should_success()
    {
        $course = factory(Course::class)->create();
        $teacher = factory(Teacher::class)->create();
        $lessons = factory(Lesson::class, 5)->create();
        $course->lessons()->saveMany($lessons);
        $course->attachToTeacher($teacher);

        $tf = new \App\Transformers\CourseFlatTransformer();
        $data = $tf->transform($course);
        $this->get($this->u('course.get_flat', '$res_name', $course->id))
            ->seeJsonEquals([ 'data' => $data ]);
    }


}