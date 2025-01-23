@extends('layouts.admin')

@section('title', 'My Profile')

@push('styles')
    <style>

    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">profile</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="row">
            <!-- Profile Card -->
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="/path/to/profile-picture.jpg" class="rounded-circle mb-3" alt="Profile Picture"
                            style="width: 150px; height: 150px;">
                        <h4>{{ $admin->fullname }}</h4>
                        <p class="text-muted mb-1">{{ $admin->email }}</p>
                        <p class="text-muted">Role: {{ ucfirst($admin->role) }}</p>
                        <a href="" class="btn btn-primary btn-sm mt-3">Edit Profile</a>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Profile Details</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th scope="row">Full Name</th>
                                    <td>{{ $admin->fullname }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Email Address</th>
                                    <td>{{ $admin->email }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Phone Number</th>
                                    <td>{{ $admin->phone_number }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Joined On</th>
                                    <td>{{ $admin->created_at->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th scope="row">Last Login</th>
                                    {{-- <td>{{ $admin->last_login ? $admin->last_login->format('d M Y, h:i A') : 'N/A' }}</td> --}}
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="/admin/profile/change-password">
                            @csrf
                            <div class="mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" name="current_password" id="currentPassword" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" name="new_password" id="newPassword" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" id="confirmPassword" class="form-control"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
