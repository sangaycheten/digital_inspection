<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Welcome Back!</h5>
        <p class="text-muted">Sign in to continue to {{ config('app.name') }}.</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mt-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       required autofocus autocomplete="username" placeholder="Enter email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <div class="float-end">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-muted">Forgot password?</a>
                    @endif
                </div>
                <label class="form-label" for="password">Password</label>
                <div class="position-relative auth-pass-inputgroup mb-3">
                    <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                           id="password" name="password"
                           required autocomplete="current-password" placeholder="Enter password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button" id="password-addon">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Remember Me -->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label" for="remember_me">Remember me</label>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Sign In</button>
            </div>
        </form>
    </div>

</x-guest-layout>
