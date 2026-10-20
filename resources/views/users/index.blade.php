@extends('layouts.itrack')
@section('title', 'Users')

@section('content')
    <div class="page-head">
        <div><h1>Settings</h1><p>Store information, users, categories and barcode options.</p></div>
    </div>
    @include('partials.settings-tabs')

    <div class="two-col" style="grid-template-columns:1fr 1.7fr">
        <div class="card">
            <div class="card-title">Add User</div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <label class="form-label" style="margin-top:0">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror

                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                @error('email') <div class="error-text">{{ $message }}</div> @enderror

                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password') <div class="error-text">{{ $message }}</div> @enderror

                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="staff">Staff (Scan / POS only)</option>
                    <option value="admin">Admin (everything)</option>
                </select>

                <div class="form-actions"><button class="btn btn-primary"><i class="bi bi-person-plus"></i> Create Account</button></div>
            </form>
        </div>

        <div class="card">
            <div class="card-title">All Users</div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong>@if ($user->id === auth()->id()) <span class="hint">(you)</span>@endif</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge badge-role">{{ ucfirst($user->role) }}</span></td>
                                <td>
                                    <button type="button" class="icon-btn" title="Edit"
                                            data-id="{{ $user->id }}" data-name="{{ $user->name }}" data-role="{{ $user->role }}"
                                            data-self="{{ $user->id === auth()->id() ? 1 : 0 }}" onclick="openUser(this)">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    @if ($user->id !== auth()->id())
                                        <button type="button" class="icon-btn del" title="Remove"
                                                data-name="{{ $user->name }}"
                                                onclick="confirmDelete('user-del-{{ $user->id }}', this.dataset.name, 'They will lose access immediately.')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="user-del-{{ $user->id }}" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="userModal">
        <form class="modal" method="POST" id="userForm">
            @csrf @method('PUT')
            <h3>Edit User</h3>
            <label class="form-label">Name</label>
            <input type="text" name="name" id="uName" class="form-control" required>
            <label class="form-label">Role</label>
            <select name="role" id="uRole" class="form-control">
                <option value="admin">Admin</option>
                <option value="staff">Staff</option>
                <option value="customer">Customer</option>
            </select>
            <label class="form-label">New password <span class="hint">(leave blank to keep the current one)</span></label>
            <input type="password" name="password" class="form-control" autocomplete="new-password">
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('userModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function openUser(btn) {
        document.getElementById('userForm').action = @json(url('/users')) + '/' + btn.dataset.id;
        document.getElementById('uName').value = btn.dataset.name;
        const role = document.getElementById('uRole');
        role.value = btn.dataset.role;
        role.disabled = btn.dataset.self === '1'; // you cannot change your own role
        document.getElementById('userModal').classList.add('open');
    }
</script>
@endpush