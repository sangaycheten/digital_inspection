<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Create Account</h5>
        <p class="text-muted">Register to get access to {{ config('app.name') }}.</p>
    </div>

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       id="name" name="name" value="{{ old('name') }}"
                       required autofocus autocomplete="name" placeholder="Enter full name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       required autocomplete="username" placeholder="Enter email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                           id="password" name="password"
                           required autocomplete="new-password" placeholder="Enter password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-3">
                <label class="form-label" for="password_confirmation">Confirm Password</label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input @error('password_confirmation') is-invalid @enderror"
                           id="password_confirmation" name="password_confirmation"
                           required autocomplete="new-password" placeholder="Confirm password">
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
                <button class="btn btn-success w-100" type="submit">Create Account</button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p class="mb-0">Already have an account?
            <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Sign In</a>
        </p>
    </div>
</x-guest-layout>
