<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.change-password');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
            'password_confirmation' => ['required'],
        ]);

        $user = $request->user();

        $user->update([
            'password'              => Hash::make($request->password),
            'force_password_change' => false,
        ]);

        return redirect()->intended(route('admin.dashboard'))
            ->with('success', 'Password updated successfully. Welcome to ' . config('app.name') . '!');
    }
}
