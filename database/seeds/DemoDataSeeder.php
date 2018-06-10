<?php

use App\Models\Resources\Classn;
use App\Models\Roles\Teacher;
use App\Models\Resources\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{

    public static $courses = [
        '《鬼谷子》',
        '《本经阴符七术》',
        '天体力学',
        '线形代数',
        '高等数学',
        '工程图学',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $teacherUser = User::create([
            'username' => 'guiguzi',
            'email' => 'guiguzi@123.com',
            'password' => bcrypt('123123')
        ]);

        $data = [
            'nickname' => 'demo_teacher',
            'realname' => '鬼谷子',
            'age' => 2407,
            'gender' => 'male',
            'phone' => '13579',
        ];

        $teacherUser->profile->update($data);

        $teacher = factory(Teacher::class)->create();

        $teacherUser->attachRole($teacher);

        $classn = Classn::create([
            'id' => 10,
            'name' => '纵横家天字班'
        ]);

        foreach(self::$courses as $course_name) {
            $course = new Course([
                'name' => $course_name
            ]);
            $course->save();
            $course->attachToTeacher($teacher);
            $classn->courses()->save($course);
        }
    }
}
