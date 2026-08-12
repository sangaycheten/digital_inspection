<?php

namespace App\Providers;

use App\Listeners\LogAuthActivity;
use App\Models\InspectionRecord;
use App\Observers\InspectionRecordObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised();
        });

        InspectionRecord::observe(InspectionRecordObserver::class);

        $listener = new LogAuthActivity();
        Event::listen(Login::class,  [$listener, 'handleLogin']);
        Event::listen(Logout::class, [$listener, 'handleLogout']);
    }
}
