<x-guest-layout>
    <div class="text-center mt-2">
        <div class="avatar-sm mx-auto mb-3">
            <span class="avatar-title rounded-circle bg-warning-subtle text-warning fs-22">
                <i class="ri-lock-password-line"></i>
            </span>
        </div>
        <h5 class="text-primary">Set Your New Password</h5>
        <p class="text-muted fs-13">
            Your account was set up with a temporary password.<br>
            Please choose a new password to continue.
        </p>
    </div>

    @if(session('warning'))
    <div class="alert alert-warning alert-border-left fade show" role="alert">
        <i class="ri-alert-line me-2 align-middle"></i>{{ session('warning') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-border-left fade show" role="alert">
        <i class="ri-error-warning-line me-2 align-middle"></i>{{ $errors->first() }}
    </div>
    @endif

    <div class="p-2 mt-3">
        <form method="POST" action="{{ route('password.change.update') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password"
                           class="form-control pe-5 password-input"
                           id="password" name="password"
                           required autocomplete="new-password"
                           placeholder="Enter new password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label" for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password"
                           class="form-control pe-5 password-input @error('password_confirmation') is-invalid @enderror"
                           id="password_confirmation" name="password_confirmation"
                           required autocomplete="new-password"
                           placeholder="Confirm new password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                </div>
            </div>

            <div class="mt-2">
                <button class="btn btn-success w-100" type="submit">
                    <i class="ri-shield-check-line me-1"></i> Set New Password & Continue
                </button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p class="mb-0 text-muted fs-12">
            <i class="ri-information-line me-1"></i>
            You will be redirected to the dashboard after setting your password.
        </p>
    </div>

    <div class="mt-3 text-center">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted p-0 fs-12">
                <i class="ri-logout-box-line me-1"></i> Sign out instead
            </button>
        </form>
    </div>
</x-guest-layout>
