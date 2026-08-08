<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class LogAuthActivity
{
    public function handleLogin(Login $event): void
    {
        activity()->useLog('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('login')
            ->withProperties([
                'attributes' => [
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            ])
            ->log("User logged in: {$event->user->name}");
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user) {
            return;
        }

        activity()->useLog('auth')
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->event('logout')
            ->withProperties([
                'attributes' => ['ip' => request()->ip()],
            ])
            ->log("User logged out: {$event->user->name}");
    }
}
