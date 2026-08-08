<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Forgot Password?</h5>
        <p class="text-muted">Enter your email and we'll send you a reset link.</p>
    </div>

    @if (session('status'))
        <div class="alert alert-success mt-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control @error('email') is-invalid @enderror"
                       id="email" name="email" value="{{ old('email') }}"
                       required autofocus placeholder="Enter your email">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Send Reset Link</button>
            </div>
        </form>
    </div>

    <div class="mt-4 text-center">
        <p class="mb-0">Remember your password?
            <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline">Sign In</a>
        </p>
    </div>
</x-guest-layout>
