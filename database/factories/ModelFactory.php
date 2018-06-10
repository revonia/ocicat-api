<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/* @var $factory  */
// attendance 工厂
$factory->define(App\Models\Resources\Attendance::class, function (Faker\Generator $faker) {
    return [
    ];
});

// classn 工厂
$factory->define(App\Models\Resources\Classn::class, function (Faker\Generator $faker) {
    return [
    ];
});

// course 工厂
$factory->define(App\Models\Resources\Course::class, function (Faker\Generator $faker) {
    return [
    ];
});

// lesson 工厂
$factory->define(App\Models\Resources\Lesson::class, function (Faker\Generator $faker) {
    return [
    ];
});

// student 工厂
$factory->define(App\Models\Roles\Student::class, function (Faker\Generator $faker) {
    return [
        'student_number' => $faker->postcode
    ];
});

// teacher 工厂
$factory->define(App\Models\Roles\Teacher::class, function (Faker\Generator $faker) {
    return [
        'employee_number' => $faker->postcode
    ];
});

// admin 工厂
$factory->define(App\Models\Roles\Admin::class, function (Faker\Generator $faker) {
    return [
    ];
});

// user 工厂
$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'username' => $faker->userName,
        'email' => $faker->email,
        'password' => bcrypt(str_random(10)),
    ];
});