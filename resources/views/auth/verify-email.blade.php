<x-guest-layout>
    <div class="text-center mt-2">
        <h5 class="text-primary">Verify Your Email</h5>
        <p class="text-muted">
            Thanks for signing up! Please verify your email address by clicking on the link we emailed to you.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mt-3" role="alert">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="p-2 mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn btn-success w-100" type="submit">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-link w-100 text-muted">Log Out</button>
        </form>
    </div>
</x-guest-layout>
