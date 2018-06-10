<?php

namespace App\Http;

use App\Http\Middleware\Cors;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Tymon\JWTAuth\Middleware\GetUserFromToken;
use Tymon\JWTAuth\Middleware\RefreshToken;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        //\Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        //\App\Http\Middleware\EncryptCookies::class,
        //\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        //\Illuminate\Session\Middleware\StartSession::class,
        //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
        //\App\Http\Middleware\VerifyCsrfToken::class,
        Cors::class,
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        //'auth' => \App\Http\Middleware\Authenticate::class,
        //'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        //'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        //'input.filter' => \App\Http\Middleware\InputFilterMiddleware::class,
        'jwt.auth' => GetUserFromToken::class,
        'jwt.refresh' => RefreshToken::class,
    ];
}
