<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    private const ALLOWED_ROUTES = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->force_password_change && ! in_array($request->route()?->getName(), self::ALLOWED_ROUTES)) {
            return redirect()->route('password.change')
                ->with('warning', 'You must set a new password before continuing.');
        }

        return $next($request);
    }
}
