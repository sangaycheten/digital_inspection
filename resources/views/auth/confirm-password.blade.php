<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Confirm Password</h5>
        <p class="text-muted">This is a secure area. Please confirm your password before continuing.</p>
    </div>

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="position-relative auth-pass-inputgroup">
                    <input type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                           id="password" name="password"
                           required autocomplete="current-password" placeholder="Enter password">
                    <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button">
                        <i class="ri-eye-fill align-middle"></i>
                    </button>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-success w-100" type="submit">Confirm</button>
            </div>
        </form>
    </div>
</x-guest-layout>
