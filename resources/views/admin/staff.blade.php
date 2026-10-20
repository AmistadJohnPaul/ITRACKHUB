@extends('layouts.main')
@section('title', 'Manage Staff')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <h3>Manage Staff &amp; Admin Accounts</h3>

    <div class="row">
        <div class="col-md-5">
            <form action="{{ route('admin.staff.store') }}" method="POST" class="bg-white p-3 rounded shadow-sm">
                @csrf
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Role</label>
                    <select name="role" class="form-control">
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button class="btn btn-primary">Create Account</button>
            </form>
            @if ($errors->any())
                <div class="alert alert-danger mt-2">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
        </div>

        <div class="col-md-7">
            <table class="table table-bordered bg-white">
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
                <tbody>
                    @foreach ($staff as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                @if ($user->id !== auth()->id())
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmRemove({{ $user->id }}, '{{ addslashes($user->name) }}')">Remove</button>
                                    <form id="staff-delete-{{ $user->id }}" action="{{ route('admin.staff.destroy', $user) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                @else
                                    <span class="text-muted">(you)</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function confirmRemove(id, name) {
            Swal.fire({
                title: 'Remove ' + name + '?',
                text: 'They will lose access immediately.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Yes, remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('staff-delete-' + id).submit();
                }
            });
        }
    </script>
@endsection