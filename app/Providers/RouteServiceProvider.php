<?php

namespace App\Providers;

use App\Models\Resources\Attendance;
use App\Models\Resources\Classn;
use App\Models\Resources\Course;
use App\Models\Resources\Lesson;
use App\Models\User;
use App\Models\Roles\Admin;
use App\Models\Roles\Student;
use App\Models\Roles\Teacher;
use Dingo\Api\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);
        $this->bindModel($router);
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }

    protected function bindModel(Router $router)
    {
        $router->model('user', User::class);
        $router->model('admin', Admin::class);
        $router->model('teacher', Teacher::class);
        $router->model('student', Student::class);
        $router->model('attendance', Attendance::class);
        $router->model('classn', Classn::class);
        $router->model('course', Course::class);
        $router->model('lesson', Lesson::class);
    }

}
