<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    @if (session('status') === 'password-updated')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Password updated successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">Current Password</label>
        <input type="password" class="form-control @if($errors->updatePassword->has('current_password')) is-invalid @endif"
               id="update_password_current_password" name="current_password"
               autocomplete="current-password" placeholder="Enter current password">
        @if ($errors->updatePassword->has('current_password'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('current_password') }}</div>
        @endif
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">New Password</label>
        <input type="password" class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
               id="update_password_password" name="password"
               autocomplete="new-password" placeholder="Enter new password">
        @if ($errors->updatePassword->has('password'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
        @endif
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
               id="update_password_password_confirmation" name="password_confirmation"
               autocomplete="new-password" placeholder="Confirm new password">
        @if ($errors->updatePassword->has('password_confirmation'))
            <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
        @endif
    </div>

    <div class="hstack gap-2 justify-content-end">
        <button type="submit" class="btn btn-primary">Update Password</button>
    </div>
</form>
