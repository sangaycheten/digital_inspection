<?php

namespace App\Providers;

use App\Listeners\LogAuthActivity;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        $listener = new LogAuthActivity();
        Event::listen(Login::class,  [$listener, 'handleLogin']);
        Event::listen(Logout::class, [$listener, 'handleLogout']);
    }
}
