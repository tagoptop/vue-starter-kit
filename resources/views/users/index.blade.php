@extends('layouts.app')

@section('content')
<h4 class="mb-3">User Management</h4>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th width="100">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-dark">{{ ucfirst($user->role) }}</span></td>
                        <td>{{ $user->phone }}</td>
                        <td><a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $users->links() }}</div>
@endsection
