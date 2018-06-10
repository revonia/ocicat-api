<?php

use App\Models\Resources\Course;
use App\Models\Resources\Attendance;
use Illuminate\Database\Seeder;

class CallTheRollDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = Course::all();
        foreach ($courses as $course) {
            $randLesson = mt_rand(1, 3);
            for ($i = 0; $i < $randLesson; $i++) {
                $result = $course->addLesson();
                $classns = $course->classns;
                foreach ($classns as $classn) {
                    $rand = mt_rand(20, 30);
                    $students = $classn->students->random($rand);
                    foreach ($students as $student) {
                        Attendance::firstOrCreate([
                            'student_id' => $student->id,
                            'lesson_id' => $result[0]->id,
                        ]);
                    }
                }
            }
        }
    }
}
