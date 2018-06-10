<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Validators\FieldValidator;

class ValidationServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->app->validator->resolver(function ($translator, $data, $rules, $messages) {
            return new FieldValidator($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register any validation services.
     *
     * @return void
     */
    public function register() {}

}
