@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="container-fluid px-4 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">User Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search users...">
                    <button class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Role</th>
                            <th scope="col">Status</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i = 1;
                        @endphp
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $user->fullname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    <span
                                        class="status-badge {{ $user->is_active ? 'status-approved' : 'status-pending' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <form method="POST"
                                        action="{{ $user->is_active ? route('admin.users.deactivate', $user->id) : route('admin.users.activate', $user->id) }}"
                                        class="d-inline">
                                        @csrf
                                        <button class="btn {{ $user->is_active ? 'btn-warning' : 'btn-success' }} btn-sm">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }} me-1"></i>
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <button class="btn btn-info btn-sm view-user" data-user="{{ json_encode($user) }}"
                                        data-joined="{{ $user->created_at->format('d M Y') }}">
                                        <i class="fas fa-eye me-1"></i> View
                                    </button>
                                    <button class="btn btn-primary btn-sm edit-user" data-user="{{ json_encode($user) }}">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-secondary btn-sm reset-password"
                                        data-user-id="{{ $user->id }}">
                                        <i class="fas fa-key me-1"></i> Reset Password
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-4">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Name:</strong> <span id="view-fullname"></span></p>
                    <p><strong>Email:</strong> <span id="view-email"></span></p>
                    <p><strong>Role:</strong> <span id="view-role"></span></p>
                    <p><strong>Status:</strong> <span id="view-status"></span></p>
                    <p><strong>Joined:</strong> <span id="view-joined"></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="phone" class="form-control" id="edit-phone" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="edit-role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="support">Support</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="new_password_confirmation" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View User Modal
            const viewModal = new bootstrap.Modal(document.getElementById('viewUserModal'));
            document.querySelectorAll('.view-user').forEach(button => {
                button.addEventListener('click', function() {
                    const user = JSON.parse(this.dataset.user);
                    const joined = this.dataset.joined;

                    document.getElementById('view-fullname').textContent = user.fullname;
                    document.getElementById('view-email').textContent = user.email;
                    document.getElementById('view-role').textContent = user.role.charAt(0)
                        .toUpperCase() + user.role.slice(1);
                    document.getElementById('view-status').textContent = user.is_active ? 'Active' :
                        'Inactive';
                    document.getElementById('view-joined').textContent = joined;

                    viewModal.show();
                });
            });

            // Edit User Modal
            const editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            document.querySelectorAll('.edit-user').forEach(button => {
                button.addEventListener('click', function() {
                    const user = JSON.parse(this.dataset.user);
                    const form = document.getElementById('editUserForm');

                    form.action = `/admin/users/${user.id}`;
                    document.getElementById('edit-phone').value = user.phone_number;
                    document.getElementById('edit-email').value = user.email;
                    document.getElementById('edit-role').value = user.role;

                    editModal.show();
                });
            });

            // Reset Password Modal
            const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
            document.querySelectorAll('.reset-password').forEach(button => {
                button.addEventListener('click', function() {
                    const userId = this.dataset.userId;
                    const form = document.getElementById('resetPasswordForm');

                    form.action = `/admin/users/${userId}/reset-password`;
                    form.reset(); // Clear previous input

                    resetModal.show();
                });
            });
        });
    </script>
@endpush
