<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['namespace' => 'App\Http\Controllers\V1'], function ($api) {

        /**
         * /users path
         */
        $api->group(['prefix' => 'users'], function ($api) {

            $api->get('/', [
                'as' => 'user.search',
                'uses' => 'UserGetController@search'
            ]);

            $api->get('/{user}', [
                'as' => 'user.get',
                'uses' => 'UserGetController@get'
            ])->where('user', '[0-9]+');

            $api->post('/', [
                'as' => 'user.add',
                'uses' => 'UserModifyController@add'
            ]);

            $api->delete('/{user}', [
                'as' => 'user.delete',
                'uses' => 'UserModifyController@delete'
            ])->where('user', '[0-9]+');

            $api->put('/{user}', [
                'as' => 'user.update',
                'uses' => 'UserModifyController@update'
            ])->where('user', '[0-9]+');

            $api->put('/{user}/profile', [
                'as' => 'user.update.profile',
                'uses' => 'UserModifyController@updateProfile'
            ])->where('user', '[0-9]+');

            $api->get('/{user}/role', [
                'as' => 'user.get.role',
                'uses' => 'UserRoleController@get'
            ])->where('user', '[0-9]+');

            $api->put('/{user}/role', [
                'as' => 'user.attach.role',
                'uses' => 'UserRoleController@attach'
            ])->where('user', '[0-9]+');

            $api->delete('/{user}/role', [
                'as' => 'user.detach.role',
                'uses' => 'UserRoleController@detach'
            ])->where('user', '[0-9]+');
        });

        /**
         * /me path
         */
        $api->group(['prefix' => 'me'], function ($api) {

            $api->post('/', [
                'as' => 'me.auth',
                'uses' => 'MeController@authenticate'
            ]);

            $api->get('/refresh_token', [
                'as' => 'me.refresh.token',
                'middleware' => 'jwt.refresh',
                'uses' => 'MeController@refresh'
            ]);

            $api->group(['middleware' => 'api.auth'], function ($api) {
                $api->get('/', [
                    'as' => 'me.get',
                    'uses' => 'MeController@get'
                ]);

                $api->put('/email', [
                    'as' => 'me.update.email',
                    'uses' => 'MeController@updateEmail'
                ]);

                $api->put('/password', [
                    'as' => 'me.update.password',
                    'uses' => 'MeController@updatePassword'
                ]);

                $api->put('/profile', [
                    'as' => 'me.update.profile',
                    'uses' => 'MeController@updateProfile'
                ]);

                $api->get('/role', [
                    'as' => 'me.get.role',
                    'uses' => 'MeController@getRole'
                ]);

                $api->post('/role', [
                    'as' => 'me.add.role',
                    'uses' => 'MeController@addRole'
                ]);

                $api->put('/role', [
                    'as' => 'me.update.role',
                    'uses' => 'MeController@updateRole'
                ]);

                $api->post('/pin', [
                    'as' => 'me.verify.pin',
                    'uses' => 'MeController@verifyPin'
                ]);
            });


        });


        $api->group(['namespace' => 'RoleControllers'], function ($api) {

            /**
             * /admin path
             */
            $api->group(['prefix' => 'admins'], function ($api) {

                $api->get('/{admin}', [
                    'as' => 'admin.get',
                    'uses' => 'AdminController@get'
                ])->where('admin', '[0-9]+');

                $api->post('/', [
                    'as' => 'admin.add',
                    'uses' => 'AdminController@add'
                ]);

                $api->put('/{admin}', [
                    'as' => 'admin.update',
                    'uses' => 'AdminController@update'
                ])->where('admin', '[0-9]+');

                $api->delete('/{admin}', [
                    'as' => 'admin.delete',
                    'uses' => 'AdminController@delete'
                ])->where('admin', '[0-9]+');

            });

            /**
             * /student path
             */
            $api->group(['prefix' => 'students'], function ($api) {

                $api->get('/{student}', [
                    'as' => 'student.get',
                    'uses' => 'StudentController@get'
                ])->where('student', '[0-9]+');

                $api->post('/', [
                    'as' => 'student.add',
                    'uses' => 'StudentController@add'
                ]);

                $api->put('/{student}', [
                    'as' => 'student.update',
                    'uses' => 'StudentController@update'
                ])->where('student', '[0-9]+');

                $api->delete('/{student}', [
                    'as' => 'student.delete',
                    'uses' => 'StudentController@delete'
                ])->where('student', '[0-9]+');

                $api->post('/{student}/classn', [
                    'as' => 'student.attachToClassn',
                    'uses' => 'StudentController@attachToClassn'
                ])->where('student', '[0-9]+');

                $api->delete('/{student}/classn', [
                    'as' => 'student.detachFromClassn',
                    'uses' => 'StudentController@detachFromClassn'
                ])->where('student', '[0-9]+');

                $api->get('/{student}/attendances', [
                    'as' => 'student.attendance.get',
                    'uses' => 'StudentController@getAttendances'
                ])->where('student', '[0-9]+');

                $api->get('/{student}/stat', [
                    'as' => 'student.stat.get',
                    'uses' => 'StudentController@getStat'
                ])->where('student', '[0-9]+');
            });

            /**
             * /teacher path
             */
            $api->group(['prefix' => 'teachers'], function ($api) {

                $api->get('/{teacher}', [
                    'as' => 'teacher.get',
                    'uses' => 'TeacherController@get'
                ])->where('teacher', '[0-9]+');

                $api->post('/', [
                    'as' => 'teacher.add',
                    'uses' => 'TeacherController@add'
                ]);

                $api->put('/{teacher}', [
                    'as' => 'teacher.update',
                    'uses' => 'TeacherController@update'
                ])->where('teacher', '[0-9]+');

                $api->delete('/{teacher}', [
                    'as' => 'teacher.delete',
                    'uses' => 'TeacherController@delete'
                ])->where('teacher', '[0-9]+');

                $api->get('/{teacher}/courses', [
                    'as' => 'teacher.course.get',
                    'uses' => 'TeacherController@getCourses'
                ])->where('teacher', '[0-9]+');

            });

        });

        $api->group(['namespace' => 'ResourceControllers'], function ($api) {

            /**
             * /attendance path
             */
            $api->group(['prefix' => 'attendances'], function ($api) {

                $api->get('/{attendance}', [
                    'as' => 'attendance.get',
                    'uses' => 'AttendanceController@get'
                ])->where('attendance', '[0-9]+');

                $api->get('/{id}.flat', [
                    'as' => 'attendance.get_flat',
                    'uses' => 'AttendanceController@getFlat'
                ])->where('id', '[0-9]+');

                $api->post('/', [
                    'as' => 'attendance.add',
                    'uses' => 'AttendanceController@add'
                ]);

                $api->put('/{attendance}', [
                    'as' => 'attendance.update',
                    'uses' => 'AttendanceController@update'
                ])->where('attendance', '[0-9]+');

                $api->delete('/{attendance}', [
                    'as' => 'attendance.delete',
                    'uses' => 'AttendanceController@delete'
                ])->where('attendance', '[0-9]+');

                $api->post('/pin/{pin}', [
                    'as' => 'attendance.add_by_pin',
                    'uses' => 'AttendanceController@addByPin'
                ])->where('pin', '[0-9]+');

            });

            /**
             * /classn path
             */
            $api->group(['prefix' => 'classns'], function ($api) {

                $api->get('/{classn}', [
                    'as' => 'classn.get',
                    'uses' => 'ClassnController@get'
                ])->where('classn', '[0-9]+');

                $api->post('/', [
                    'as' => 'classn.add',
                    'uses' => 'ClassnController@add'
                ]);

                $api->put('/{classn}', [
                    'as' => 'classn.update',
                    'uses' => 'ClassnController@update'
                ])->where('classn', '[0-9]+');

                $api->delete('/{classn}', [
                    'as' => 'classn.delete',
                    'uses' => 'ClassnController@delete'
                ])->where('classn', '[0-9]+');

                $api->get('/{classn}/courses', [
                    'as' => 'classn.course.get',
                    'uses' => 'ClassnController@getCourses'
                ])->where('classn', '[0-9]+');

                $api->get('/{classn}/students', [
                    'as' => 'classn.student.get',
                    'uses' => 'ClassnController@getStudents'
                ])->where('classn', '[0-9]+');

            });

            /**
             * /course path
             */
            $api->group(['prefix' => 'courses'], function ($api) {

                $api->get('/{course}', [
                    'as' => 'course.get',
                    'uses' => 'CourseController@get'
                ])->where('course', '[0-9]+');

                $api->post('/', [
                    'as' => 'course.add',
                    'uses' => 'CourseController@add'
                ]);

                $api->put('/{course}', [
                    'as' => 'course.update',
                    'uses' => 'CourseController@update'
                ])->where('course', '[0-9]+');

                $api->delete('/{course}', [
                    'as' => 'course.delete',
                    'uses' => 'CourseController@delete'
                ])->where('course', '[0-9]+');

                $api->post('/{course}/teacher', [
                    'as' => 'course.attachToTeacher',
                    'uses' => 'CourseController@attachToTeacher'
                ])->where('course', '[0-9]+');

                $api->delete('/{course}/teacher', [
                    'as' => 'course.detachFromTeacher',
                    'uses' => 'CourseController@detachFromTeacher'
                ])->where('student', '[0-9]+');

                $api->post('/{course}/lessons', [
                    'as' => 'course.lesson.add',
                    'uses' => 'CourseController@addLessonAndAttachToCourse'
                ]);

                $api->get('/{course}/lessons', [
                    'as' => 'course.lesson.get',
                    'uses' => 'CourseController@getLessons'
                ])->where('course', '[0-9]+');


                $api->get('/{course}/classns', [
                    'as' => 'course.classn.get',
                    'uses' => 'CourseController@getClassns'
                ])->where('course', '[0-9]+');

                $api->put('/{course}/classns', [
                    'as' => 'course.classn.associate',
                    'uses' => 'CourseController@associateClassn'
                ])->where('course', '[0-9]+');

                $api->get('/{id}.flat', [
                    'as' => 'course.get_flat',
                    'uses' => 'CourseController@getFlat'
                ])->where('id', '[0-9]+');

                $api->get('/{course}/stat', [
                    'as' => 'course.stat.get',
                    'uses' => 'CourseController@getStat'
                ])->where('course', '[0-9]+');
            });

            /**
             * /lesson path
             */
            $api->group(['prefix' => 'lessons'], function ($api) {

                $api->get('/{lesson}', [
                    'as' => 'lesson.get',
                    'uses' => 'LessonController@get'
                ])->where('lesson', '[0-9]+');

//                $api->post('/', [
//                    'as' => 'lesson.add',
//                    'uses' => 'LessonController@add'
//                ]);

//                $api->put('/{lesson}', [
//                    'as' => 'lesson.update',
//                    'uses' => 'LessonController@update'
//                ])->where('lesson', '[0-9]+');

                $api->delete('/{lesson}', [
                    'as' => 'lesson.delete',
                    'uses' => 'LessonController@delete'
                ])->where('lesson', '[0-9]+');

                $api->get('/{lesson}/attendances', [
                    'as' => 'lesson.attendance.get',
                    'uses' => 'LessonController@getAttendances'
                ])->where('lesson', '[0-9]+');

                $api->get('/{lesson}/pin', [
                    'as' => 'lesson.pin.get',
                    'uses' => 'LessonController@getPin'
                ])->where('lesson', '[0-9]+');

                $api->get('/{lesson}/stat', [
                    'as' => 'lesson.stat.get',
                    'uses' => 'LessonController@getStat'
                ])->where('lesson', '[0-9]+');

            });

        });

    });



    $api->get('/', [
        'as' => 'index',
        'uses' => 'App\Http\Controllers\IndexController@index'
    ]);

});
