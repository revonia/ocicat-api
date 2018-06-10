<?php
use App\Models\Resources\Attendance;
use App\Models\Resources\Lesson;
use App\Models\Roles\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Auto generate source code header.
 * Original File Name: AttendancePathTest.php
 * Author: Wangjian
 * Date: 2017/3/2
 * Time: 16:14
 */
class AttendancePathTest extends TestCase
{
    use DatabaseMigrations;

    /** @var null|Attendance  */
    protected $attendance = null;

    public function prepareAttendance()
    {
        $student = factory(Student::class)->create();
        $lesson = factory(Lesson::class)->create();

        $attendance = factory(Attendance::class)->make();

        $attendance->student()->associate($student);
        $attendance->lesson()->associate($lesson);

        $attendance->save();
        $this->attendance = $attendance;
    }

    /**
     * @test
     */
    public function get_flat_attendance_should_success()
    {
        $this->prepareAttendance();

        $this->get($this->u(
            'attendance.get_flat',
            'attendance',
            $this->attendance->id
        ));

        $this->assertResponseStatus(200);

        $transformer = new \App\Transformers\AttendanceFlatTransformer();
        $data = $transformer->transform($this->attendance);
        $this->seeJsonEquals(['data' => $data]);
    }

    /**
     * @test
     */
    public function add_should_success()
    {
        $student = factory(Student::class)->create();
        $lesson = factory(Lesson::class)->create();

        $this->json('POST', $this->u('attendance.add'), [
            'data' => [
                'student_id' => $student->id,
                'lesson_id' => $lesson->id,
            ]
        ]);
        $this->assertResponseStatus(201);
        $this->seeInDatabase(Attendance::TABLE_NAME, [
            'student_id' => $student->id,
            'lesson_id' => $lesson->id,
        ]);
    }

}