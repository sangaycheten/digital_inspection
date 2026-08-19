@use('Illuminate\Support\Facades\Storage')

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('patch')

    @if (session('status') === 'profile-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Profile updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Avatar --}}
    <div class="mb-4">
        <label class="form-label d-block">Profile Picture</label>
        <div class="d-flex align-items-center gap-3">
            <img id="avatar-preview"
                 src="{{ $user->avatar ? Storage::disk('public')->url($user->avatar) : asset('assets/images/users/avatar-1.jpg') }}"
                 alt="Avatar"
                 class="rounded-circle"
                 style="width:80px;height:80px;object-fit:cover;">
            <div>
                <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                       id="avatar" name="avatar" accept="image/*"
                       onchange="previewAvatar(this)">
                <div class="form-text">JPG, PNG or WebP · max 2 MB</div>
                @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $user->name) }}"
               required autofocus autocomplete="name">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email Address</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror"
               id="email" name="email" value="{{ old('email', $user->email) }}"
               required autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-muted fs-13">
                    Your email address is unverified.
                    <button form="send-verification" class="btn btn-link p-0 fs-13 text-primary">
                        Click here to re-send the verification email.
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success mt-2">
                        A new verification link has been sent to your email address.
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="hstack gap-2 justify-content-end">
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
</form>

@once
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endonce
