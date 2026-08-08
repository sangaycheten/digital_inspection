<x-app-layout>
    <x-slot name="title">Profile</x-slot>

    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Profile</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                            @php
                                $home = Auth::user()->hasRole('system-administrator') ? route('admin.dashboard')
                                    : (Auth::user()->hasRole('manager') ? route('reviewer.dashboard')
                                    : (Auth::user()->hasRole('field-technician') ? route('technician.dashboard')
                                    : route('client.dashboard')));
                            @endphp
                            <a href="{{ $home }}">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xxl-8">

            <!-- Update Profile Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Update Password -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Update Password</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Delete Account -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delete Account</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
