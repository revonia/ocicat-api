<?php

use App\Models\Resources\Classn;
use App\Models\Roles\Student;
use App\Models\Roles\Teacher;
use App\Models\Resources\Course;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class AllDataSeeder extends Seeder
{
    public static $userPassword = '123123';
    public static $usernames = [
        'wang',
        'asdf',
        'qwer',
        'zxcv',
        'aaaa',
        'zzzz',
        'qqqq',
        'wwww',
        'ssss',
        'xxxx'
    ];

    public static $majors = [
        '物理',
        '电气',
//        '音乐',
        '计算机',
//        '建筑',
//        '医学',
//        '美术',
//        '法律',
//        '化学',
//        '生物'
    ];

    public static $courses = [
        '高等数学1',
        '高等数学2',
        '大学物理',
        '线形代数',
        'C语言',
        '工程图学',
        '马克思主义概论',
        '大学语文',
        '大学英语',
        '大学计算机基础',
        'Matlab基础',
        '电路',
        '物理实验',
        '数字电子技术',
        '模拟电子技术',
        '信号与系统',
        '通信原理',
        '流体力学',
        'Java程序设计',
        '计算机网络',
        '单片机基础'
    ];


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $classns = $this->createClassn();
        $teachers = $this->createTeacher();
        $courses = $this->createCourseWithTeacher($teachers);
        $students = $this->createStudentWithClassn($classns);
        $this->linkCourseClassn($courses, $classns);

        $users = $this->createUserWithProfile();

        for ($i = 0; $i < 180; $i++) {
            $users[0][$i]->attachRole($students[$i]);
        }

        for ($i = 0; $i < 20; $i++) {
            $users[1][$i]->attachRole($teachers[$i]);
        }

    }

    public function createUserWithProfile()
    {
        $collTeacher = collect();
        $coll = collect();
        $faker = Faker\Factory::create('zh_CN');

        for ($i = 0; $i < 20; $i++) {
            foreach (self::$usernames as $username) {

                $name = $username;
                if ($i !== 0) {
                    $name .= $i;
                }

                $user = User::create([
                    'username' => $name,
                    'email' => $name . '@123.com',
                    'password' => bcrypt(self::$userPassword)
                ]);

                $data = [
                    'nickname' => $faker->userName,
                    'realname' => $faker->name,
                    'age' => mt_rand(10, 100),
                    'gender' => UserProfile::GENDERS[mt_rand(0, 2)],
                    'phone' => $faker->phoneNumber,
                ];

                $profile = $user->profile;
                $profile->update($data);
                if ($username === 'wang') {
                    $collTeacher->push($user);
                } else {
                    $coll->push($user);
                }

            }
        }
        return [$coll, $collTeacher];
    }

    public function createClassn()
    {
        $coll = collect();
        for ($i = 1; $i < 3; $i++) {
            foreach (self::$majors as $major) {
                $classn = Classn::create([
                    'name' => $major . $i . '班'
                ]);
                $coll->push($classn);
            }
        }

        return $coll;
    }

    public function createTeacher()
    {
        return factory(Teacher::class, 20)->create();
    }

    public function createCourseWithTeacher($teachers)
    {
        $courses = collect();
        for ($i = 0; $i < 20; $i++) {
            $course = new Course([
                'name' => self::$courses[$i]
            ]);
            $course->save();
            $course->attachToTeacher($teachers[$i]);
            $courses->push($course);
        }
        return $courses;
    }

    public function createStudentWithClassn($classns)
    {
        $students = factory(Student::class, 180)->create();
        $chunks = $students->chunk(30);
        for ($i = 0; $i < 6; $i++) {
            $classns[$i]->students()->saveMany($chunks[$i]);
        }
        return $students;
    }

    public function linkCourseClassn($courses, $classns)
    {
        foreach ($classns as $classn) {
            $tmp = $courses->random(8);
            $classn->courses()->saveMany($tmp);
        }
    }
}
