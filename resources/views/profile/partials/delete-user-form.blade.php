<p class="text-muted mb-4">
    Once your account is deleted, all of its resources and data will be permanently deleted.
    Before deleting your account, please download any data or information that you wish to retain.
</p>

<button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
    <i class="ri-delete-bin-line align-middle me-1"></i> Delete Account
</button>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-body">
                    <p class="text-muted">
                        Are you sure you want to delete your account? This action cannot be undone.
                        Please enter your password to confirm.
                    </p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label">Password</label>
                        <input type="password"
                               class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif"
                               id="delete_password" name="password" placeholder="Enter your password">
                        @if ($errors->userDeletion->has('password'))
                            <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
        modal.show();
    });
</script>
@endif
