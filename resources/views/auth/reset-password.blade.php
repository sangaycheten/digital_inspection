<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Reset Password</h5>
        <p class="text-muted">Enter your new password below.</p>
    </div>

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email', $request->email) }}"
                       required autofocus autocomplete="username" placeholder="Enter email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="password">New Password</label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                           id="password" name="password"
                           required autocomplete="new-password" placeholder="Enter new password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input @error('password_confirmation') is-invalid @enderror"
                           id="password_confirmation" name="password_confirmation"
                           required autocomplete="new-password" placeholder="Confirm new password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Reset Password</button>
            </div>
        </form>
    </div>
</x-guest-layout>
