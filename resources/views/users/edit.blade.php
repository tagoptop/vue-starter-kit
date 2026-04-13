@extends('layouts.app')

@section('content')
<h4 class="mb-3">Edit User</h4>
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                        <option value="staff" @selected(old('role', $user->role) === 'staff')>Staff</option>
                        <option value="customer" @selected(old('role', $user->role) === 'customer')>Customer</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $user->address) }}">
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
